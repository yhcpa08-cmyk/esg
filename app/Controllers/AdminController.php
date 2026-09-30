<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Core\AuditLogger;
use App\Core\SystemSettings;

class AdminController
{
    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }
        // 限系統管理員角色訪問管理中心
        if (!Auth::isSuperAdmin()) {
            http_response_code(403);
            die('403 Forbidden: 權限不足，僅系統管理員具備此模組存取權限。');
        }
    }

    /**
     * 廠區與邊界管理 (M01-01)
     */
    public function sites(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteCode = trim($_POST['site_code'] ?? '');
            $siteName = trim($_POST['site_name'] ?? '');
            $country = trim($_POST['country'] ?? 'Taiwan');
            $inBoundary = isset($_POST['is_in_boundary']) ? 1 : 0;

            $id = Database::insert(
                "INSERT INTO org_sites (site_code, site_name, country, is_in_boundary)
                 VALUES (?, ?, ?, ?)",
                [$siteCode, $siteName, $country, $inBoundary]
            );

            AuditLogger::log('SITE', 'INSERT', $id, null, ['code' => $siteCode, 'name' => $siteName]);

            header('Location: ' . Auth::baseUrl() . '/admin/sites?msg=created');
            exit;
        }

        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");
        View::render('admin/sites', ['sites' => $sites]);
    }

    /**
     * 部門階層管理
     */
    public function departments(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteId = (int)($_POST['site_id'] ?? 0);
            $deptName = trim($_POST['dept_name'] ?? '');
            $parentDeptId = !empty($_POST['parent_dept_id']) ? (int)$_POST['parent_dept_id'] : null;

            $id = Database::insert(
                "INSERT INTO org_departments (site_id, dept_name, parent_dept_id)
                 VALUES (?, ?, ?)",
                [$siteId, $deptName, $parentDeptId]
            );

            AuditLogger::log('DEPT', 'INSERT', $id, null, ['name' => $deptName]);

            header('Location: ' . Auth::baseUrl() . '/admin/departments?msg=created');
            exit;
        }

        $departments = Database::query(
            "SELECT d.*, s.site_name, p.dept_name as parent_name
             FROM org_departments d
             JOIN org_sites s ON d.site_id = s.id
             LEFT JOIN org_departments p ON d.parent_dept_id = p.id
             ORDER BY d.site_id, d.id ASC"
        );
        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");

        View::render('admin/departments', [
            'departments' => $departments,
            'sites'       => $sites
        ]);
    }

    /**
     * 使用者管理 (M01-03)
     */
    public function users(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $realName = trim($_POST['real_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? 'admin123');
            $roleId = (int)($_POST['role_id'] ?? 5);
            $siteId = (int)($_POST['site_id'] ?? 1);
            $deptId = (int)($_POST['dept_id'] ?? 1);

            $pwdHash = password_hash($password, PASSWORD_BCRYPT);

            $id = Database::insert(
                "INSERT INTO sys_users (username, password_hash, real_name, email, role_id, site_id, dept_id, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVE')",
                [$username, $pwdHash, $realName, $email, $roleId, $siteId, $deptId]
            );

            AuditLogger::log('USER', 'INSERT', $id, null, ['username' => $username, 'real_name' => $realName]);

            header('Location: ' . Auth::baseUrl() . '/admin/users?msg=created');
            exit;
        }

        $users = Database::query(
            "SELECT u.*, r.role_name, s.site_name, d.dept_name
             FROM sys_users u
             JOIN sys_roles r ON u.role_id = r.id
             JOIN org_sites s ON u.site_id = s.id
             JOIN org_departments d ON u.dept_id = d.id
             ORDER BY u.id ASC"
        );
        $roles = Database::query("SELECT * FROM sys_roles ORDER BY id ASC");
        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");
        $departments = Database::query("SELECT * FROM org_departments ORDER BY site_id, id ASC");

        View::render('admin/users', [
            'users'       => $users,
            'roles'       => $roles,
            'sites'       => $sites,
            'departments' => $departments
        ]);
    }

    /**
     * 使用者解鎖 / 狀態切換
     */
    public function toggleUserStatus(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newStatus = $_POST['status'] ?? 'ACTIVE';

        Database::execute(
            "UPDATE sys_users SET status = ?, failed_login_count = 0, locked_until = NULL WHERE id = ?",
            [$newStatus, $userId]
        );

        AuditLogger::log('USER', 'UPDATE', $userId, null, ['status' => $newStatus]);

        header('Location: ' . Auth::baseUrl() . '/admin/users?msg=status_updated');
        exit;
    }

    /**
     * 重設使用者密碼 (M01-03)
     */
    public function resetPassword(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newPassword = trim($_POST['new_password'] ?? 'admin123');

        if ($userId > 0 && !empty($newPassword)) {
            $pwdHash = password_hash($newPassword, PASSWORD_BCRYPT);
            Database::execute(
                "UPDATE sys_users SET password_hash = ?, failed_login_count = 0 WHERE id = ?",
                [$pwdHash, $userId]
            );

            AuditLogger::log('USER', 'UPDATE', $userId, null, ['action' => 'RESET_PASSWORD']);
        }

        header('Location: ' . Auth::baseUrl() . '/admin/users?msg=password_reset');
        exit;
    }

    /**
     * RBAC 角色矩陣 (M01-02)
     */
    public function roles(): void
    {
        $roles = Database::query("SELECT * FROM sys_roles ORDER BY id ASC");
        View::render('admin/roles', ['roles' => $roles]);
    }

    /**
     * 系統全域稽核日誌 (M01-05)
     */
    public function auditLogs(): void
    {
        $module = $_GET['module'] ?? '';
        $sql = "
            SELECT l.*, u.real_name, u.username
            FROM sys_audit_logs l
            LEFT JOIN sys_users u ON l.user_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($module) {
            $sql .= " AND l.action_module = ?";
            $params[] = $module;
        }
        $sql .= " ORDER BY l.id DESC LIMIT 100";

        $logs = Database::query($sql, $params);

        View::render('admin/audit_logs', [
            'logs'   => $logs,
            'module' => $module
        ]);
    }

    /** Super Admin-only, database-backed system parameters. */
    public function settings(): void
    {
        $definitions = [
            'session_lifetime_minutes' => ['label' => '閒置自動登出時間', 'unit' => '分鐘', 'min' => 5, 'max' => 240, 'description' => '使用者無操作多久後自動登出。縮短可降低共用設備風險，建議 30 分鐘。'],
            'ghg_anomaly_threshold_pct' => ['label' => '活動數據偏差警示門檻', 'unit' => '%', 'min' => 5, 'max' => 100, 'description' => '與已核准歷史平均值的偏差達此百分比時，要求填寫異常原因。建議先維持 20%，並依資料波動校準。'],
            'evidence_max_size_mb' => ['label' => '佐證檔案大小上限', 'unit' => 'MB', 'min' => 1, 'max' => 100, 'description' => '限制單一佐證上傳檔案大小。建議 50 MB；需同時確認 PHP upload_max_filesize 與 post_max_size 不低於此值。'],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldValues = [];
            $newValues = [];
            foreach ($definitions as $key => $definition) {
                $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
                if ($value === false || $value < $definition['min'] || $value > $definition['max']) {
                    http_response_code(422);
                    $settings = [];
                    foreach ($definitions as $settingKey => $_) $settings[$settingKey] = SystemSettings::get($settingKey);
                    View::render('admin/settings', ['definitions' => $definitions, 'settings' => $settings, 'error' => '設定值超出允許範圍，請檢查後重新儲存。']);
                    return;
                }
                $oldValues[$key] = SystemSettings::get($key);
                $newValues[$key] = $value;
            }

            foreach ($newValues as $key => $value) {
                Database::execute(
                    'INSERT INTO sys_settings (setting_key, setting_value, updated_by, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by), updated_at = NOW()',
                    [$key, (string)$value, (int)Auth::user()['id']]
                );
            }
            SystemSettings::replaceCache($newValues);
            AuditLogger::log('SYSTEM_SETTINGS', 'UPDATE', null, $oldValues, $newValues);
            header('Location: ' . Auth::baseUrl() . '/admin/settings?msg=saved');
            exit;
        }

        $settings = [];
        foreach ($definitions as $key => $_) $settings[$key] = SystemSettings::get($key);
        View::render('admin/settings', ['definitions' => $definitions, 'settings' => $settings, 'error' => null]);
    }
}
