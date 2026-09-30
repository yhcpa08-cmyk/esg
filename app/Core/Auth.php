<?php
namespace App\Core;

/**
 * 身份驗證與 Session 管理服務
 * 規格書 3.1 (M01-03, M01-04) 實作
 */
class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
            session_name($appConfig['session_name']);
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_secure', SecurityHeaders::isHttps() ? '1' : '0');
            session_start();
        }

        // 30分鐘閒置自動逾時登出
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SystemSettings::get('session_lifetime_minutes') * 60)) {
            self::logout();
            header('Location: ' . self::baseUrl() . '/auth/login?msg=timeout');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    public static function login(string $username, string $password): array
    {
        self::startSession();

        $user = Database::fetch(
            "SELECT u.*, r.role_code, r.role_name, r.data_scope, s.site_name, d.dept_name
             FROM sys_users u
             JOIN sys_roles r ON u.role_id = r.id
             JOIN org_sites s ON u.site_id = s.id
             JOIN org_departments d ON u.dept_id = d.id
             WHERE u.username = ?",
            [$username]
        );

        if (!$user) {
            return ['success' => false, 'message' => '帳號或密碼錯誤'];
        }

        if ($user['status'] === 'LOCKED') {
            if (!empty($user['locked_until']) && strtotime($user['locked_until']) <= time()) {
                Database::execute("UPDATE sys_users SET status = 'ACTIVE', failed_login_count = 0, locked_until = NULL WHERE id = ?", [$user['id']]);
                $user['status'] = 'ACTIVE';
            } else {
                return ['success' => false, 'message' => '帳號暫時鎖定，請稍候重試或聯絡管理員'];
            }
        }

        if ($user['status'] === 'DISABLED') {
            return ['success' => false, 'message' => '該帳號已停用，無法登入'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            $newFails = (int)$user['failed_login_count'] + 1;
            $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
            $maxFails = (int)($appConfig['max_login_fails'] ?? 5);
            if ($newFails >= $maxFails) {
                Database::execute(
                    "UPDATE sys_users SET failed_login_count = ?, status = 'LOCKED', locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?",
                    [$newFails, (int)($appConfig['lockout_minutes'] ?? 15), $user['id']]
                );
                AuditLogger::log('AUTH', 'LOGIN', $user['id'], null, ['status' => 'LOCKED_BY_MAX_FAILS', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'], $user['id']);
                return ['success' => false, 'message' => '連續密碼錯誤達 5 次，帳號已被鎖定 15 分鐘'];
            } else {
                Database::execute(
                    "UPDATE sys_users SET failed_login_count = ? WHERE id = ?",
                    [$newFails, $user['id']]
                );
                AuditLogger::log('AUTH', 'LOGIN', $user['id'], null, ['status' => 'FAILED_PASSWORD', 'attempts' => $newFails], $user['id']);
                return ['success' => false, 'message' => "密碼錯誤，剩餘 " . (5 - $newFails) . " 次嘗試機會"];
            }
        }

        // 登入成功
        session_regenerate_id(true);
        Database::execute(
            "UPDATE sys_users SET failed_login_count = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?",
            [$user['id']]
        );

        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $_SESSION['last_activity'] = time();

        AuditLogger::log('AUTH', 'LOGIN', $user['id'], null, ['status' => 'SUCCESS'], $user['id']);

        return ['success' => true, 'user' => $user];
    }

    public static function logout(): void
    {
        // 登出時只確保 Session 可用，避免閒置逾時流程再次進入 startSession() 而遞迴。
        if (session_status() === PHP_SESSION_NONE) {
            $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
            session_name($appConfig['session_name']);
            session_start();
        }
        if (isset($_SESSION['user']['id'])) {
            AuditLogger::log('AUTH', 'LOGIN', (int)$_SESSION['user']['id'], null, ['action' => 'LOGOUT']);
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function check(): bool
    {
        self::startSession();
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $u = self::user();
        if (!$u) return false;
        if ($u['role_code'] === 'ROLE_ADMIN') return true; // SysAdmin 最高全權
        return in_array($u['role_code'], $roles, true);
    }

    public static function isSuperAdmin(): bool
    {
        $u = self::user();
        return $u !== null && ($u['role_code'] ?? '') === 'ROLE_ADMIN';
    }

    public static function requireRoles(string ...$roles): void
    {
        if (!self::hasRole(...$roles)) {
            http_response_code(403);
            die('403 Forbidden: 您沒有執行此操作的權限。');
        }
    }

    public static function canAccessScope(int $siteId, ?int $deptId = null): bool
    {
        $user = self::user();
        if (!$user || ($user['role_code'] ?? '') === 'ROLE_ADMIN' || ($user['data_scope'] ?? '') === 'ALL') return $user !== null;
        if (($user['data_scope'] ?? '') === 'SITE') return (int)$user['site_id'] === $siteId;
        if (($user['data_scope'] ?? '') === 'DEPT') return (int)$user['site_id'] === $siteId && $deptId !== null && (int)$user['dept_id'] === $deptId;
        return false;
    }

    public static function requireScope(int $siteId, ?int $deptId = null): void
    {
        if (!self::canAccessScope($siteId, $deptId)) {
            http_response_code(403);
            die('403 Forbidden: 您沒有此廠區或部門資料的操作權限。');
        }
    }

    public static function baseUrl(): string
    {
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        return $config['base_url'];
    }
}
