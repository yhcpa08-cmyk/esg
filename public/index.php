<?php
/**
 * ESG 企業永續智慧管理系統 - 單一入口與路由分發
 * SPEC-ESG-2026-V1.0
 */

// 註冊 PSR-4 簡易 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Auth;
use App\Core\Router;
use App\Core\SecurityHeaders;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\GhgController;
use App\Controllers\SocialController;
use App\Controllers\GovernanceController;
use App\Controllers\TaskController;
use App\Controllers\SupplierController;
use App\Controllers\ReportController;
use App\Controllers\AdminController;
use App\Controllers\ManualController;
use App\Controllers\AiAssistantController;

// 在任何輸出前套用瀏覽器安全標頭與 Session 防護。
SecurityHeaders::send();
Auth::startSession();

$router = new Router();

// 1. 首頁與身份驗證路由
$router->get('/', function() {
    if (Auth::check()) {
        header('Location: ' . Auth::baseUrl() . '/dashboard');
    } else {
        header('Location: ' . Auth::baseUrl() . '/auth/login');
    }
    exit;
});
$router->get('/auth/login', [AuthController::class, 'login']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/logout', [AuthController::class, 'logout']);
$router->get('/auth/quick-login', [AuthController::class, 'quickLogin']);

// 2. 戰情儀表板路由 (M07)
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/analytics', [DashboardController::class, 'analytics']);

// 3. 環境管理 (E) 溫室氣體盤查路由 (M02)
$router->get('/ghg/records', [GhgController::class, 'records']);
$router->get('/ghg/create', [GhgController::class, 'create']);
$router->post('/ghg/create', [GhgController::class, 'create']);
$router->get('/ghg/factors', [GhgController::class, 'factors']);
$router->get('/ghg/factors/create', [GhgController::class, 'factorsCreate']);
$router->post('/ghg/factors/create', [GhgController::class, 'factorsCreate']);
$router->get('/ghg/energy-water', [GhgController::class, 'energyWater']);
$router->get('/ghg/energy-water/create', [GhgController::class, 'energyWaterCreate']);
$router->post('/ghg/energy-water/create', [GhgController::class, 'energyWaterCreate']);
$router->get('/ghg/sbti', [GhgController::class, 'sbti']);

// 4. 社會責任 (S) 路由 (M03)
$router->get('/social', [SocialController::class, 'index']);
$router->get('/social/create', [SocialController::class, 'create']);
$router->post('/social/create', [SocialController::class, 'create']);

// 5. 公司治理 (G) 與 TCFD 路由 (M04)
$router->get('/governance', [GovernanceController::class, 'index']);
$router->post('/governance/save', [GovernanceController::class, 'save']);

// 6. 數據收集與工作流引擎路由 (M05)
$router->get('/tasks', [TaskController::class, 'index']);
$router->post('/tasks/create', [TaskController::class, 'create']);
$router->post('/tasks/review', [TaskController::class, 'review']);

// 7. 供應鏈 ESG 評鑑路由 (M08)
$router->get('/suppliers', [SupplierController::class, 'index']);
$router->post('/suppliers/create', [SupplierController::class, 'create']);
$router->get('/suppliers/public-survey', [SupplierController::class, 'publicSurvey']);
$router->post('/suppliers/public-survey', [SupplierController::class, 'publicSurvey']);
$router->post('/suppliers/capa-update', [SupplierController::class, 'capaUpdate']);

// 8. 永續報告書與國際標準映射路由 (M06)
$router->get('/reports/gri', [ReportController::class, 'gri']);
$router->get('/reports/sasb', [ReportController::class, 'sasb']);
$router->get('/reports/export', [ReportController::class, 'export']);

// 9. 系統管理與組織架構路由 (M01)
$router->get('/admin/sites', [AdminController::class, 'sites']);
$router->post('/admin/sites', [AdminController::class, 'sites']);
$router->get('/admin/departments', [AdminController::class, 'departments']);
$router->post('/admin/departments', [AdminController::class, 'departments']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/toggle-user-status', [AdminController::class, 'toggleUserStatus']);
$router->post('/admin/reset-password', [AdminController::class, 'resetPassword']);
$router->get('/admin/roles', [AdminController::class, 'roles']);
$router->get('/admin/audit-logs', [AdminController::class, 'auditLogs']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings', [AdminController::class, 'settings']);
$router->get('/admin/ai-assistant', [AiAssistantController::class, 'settings']);
$router->post('/admin/ai-assistant', [AiAssistantController::class, 'settings']);
$router->post('/admin/ai-assistant/test', [AiAssistantController::class, 'testConnection']);

// 10. 系統操作手冊
$router->get('/manual', [ManualController::class, 'index']);
$router->post('/ai-assistant/chat', [AiAssistantController::class, 'chat']);

// 啟動路由分發
$router->dispatch();
