<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;

class AuthController
{
    public function login(): void
    {
        Auth::startSession();
        if (Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/dashboard');
            exit;
        }

        $error = null;
        $msg = null;

        if (isset($_GET['msg']) && $_GET['msg'] === 'timeout') {
            $msg = '您已閒置超過 30 分鐘，系統已自動為您安全登出。';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = '請輸入使用者帳號與密碼';
            } else {
                $res = Auth::login($username, $password);
                if ($res['success']) {
                    header('Location: ' . Auth::baseUrl() . '/dashboard');
                    exit;
                } else {
                    $error = $res['message'];
                }
            }
        }

        View::render('auth/login', [
            'error' => $error,
            'msg'   => $msg
        ], null);
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . Auth::baseUrl() . '/auth/login');
        exit;
    }

    /**
     * 快速切換測試身分 (規格書 1.3 角色驗證)
     */
    public function quickLogin(): void
    {
        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        if (empty($appConfig['allow_quick_login'])) {
            http_response_code(404);
            die('404 Not Found');
        }
        Auth::startSession();
        $userKey = $_GET['user'] ?? 'admin';
        $validUsers = ['admin', 'cso_lin', 'esg_chen', 'site_wang', 'op_zhang', 'auditor_liu'];

        if (in_array($userKey, $validUsers, true)) {
            $user = Database::fetch(
                "SELECT u.*, r.role_code, r.role_name, r.data_scope, s.site_name, d.dept_name
                 FROM sys_users u
                 JOIN sys_roles r ON u.role_id = r.id
                 JOIN org_sites s ON u.site_id = s.id
                 JOIN org_departments d ON u.dept_id = d.id
                 WHERE u.username = ?",
                [$userKey]
            );

            if ($user) {
                unset($user['password_hash']);
                $_SESSION['user'] = $user;
                $_SESSION['last_activity'] = time();
            }
        }

        header('Location: ' . Auth::baseUrl() . '/dashboard');
        exit;
    }
}
