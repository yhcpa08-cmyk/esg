<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;

/** 為首次使用者提供的系統內建操作手冊。 */
class ManualController
{
    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }
    }

    public function index(): void
    {
        View::render('manual/index', [
            'isAdmin' => Auth::isSuperAdmin(),
            'roleCode' => Auth::user()['role_code'] ?? ''
        ]);
    }
}
