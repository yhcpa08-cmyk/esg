<?php
use App\Core\Csrf;
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
$appConfig = require dirname(__DIR__, 2) . '/config/app.php';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>系統登入 - ESG 企業永續智慧管理系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #1B4332 0%, #2D6A4F 50%, #081C15 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: "微軟正黑體", "Microsoft JhengHei", sans-serif;
      padding: 1.5rem;
    }
    .login-card {
      background: #ffffff;
      border-radius: 1rem;
      box-shadow: 0 15px 35px rgba(0,0,0,0.25);
      width: 100%;
      max-width: 460px;
      overflow: hidden;
    }
    .login-header {
      background: #E8F5E9;
      padding: 2rem 2rem 1.5rem;
      text-align: center;
      border-bottom: 2px solid #C8E6C9;
    }
    .login-logo {
      width: 60px;
      height: 60px;
      background: #1B4332;
      color: #52B788;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      margin-bottom: 0.75rem;
    }
    .btn-esg {
      background-color: #1B4332;
      color: #fff;
      font-weight: 600;
      padding: 0.65rem;
      transition: background 0.2s;
    }
    .btn-esg:hover {
      background-color: #2D6A4F;
      color: #fff;
    }
    .quick-role-btn {
      font-size: 0.78rem;
      padding: 0.3rem 0.6rem;
      border-radius: 4px;
    }
    @media (max-width: 575.98px) {
      body { padding: 1rem; align-items: flex-start; }
      .login-card { margin: 1rem 0; }
      .login-header { padding: 1.5rem 1rem 1.25rem; }
      .login-header h4 { font-size: 1.15rem; }
      .login-card > .p-4 { padding: 1.25rem !important; }
      .quick-role-btn { flex: 1 1 46%; padding: 0.45rem; }
    }
  </style>
</head>
<body>

<div class="login-card">
  <div class="login-header">
    <div class="login-logo">
      <i class="fa-solid fa-leaf"></i>
    </div>
    <h4 class="fw-bold text-dark mb-1">ESG 企業永續智慧管理系統</h4>
    <small class="text-muted">Enterprise Sustainability Management System</small>
  </div>

  <div class="p-4">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div><?= View::e($error) ?></div>
      </div>
    <?php endif; ?>

    <?php if (!empty($msg)): ?>
      <div class="alert alert-warning py-2 small d-flex align-items-center gap-2 mb-3">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <div><?= View::e($msg) ?></div>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= $baseUrl ?>/auth/login">
      <?= Csrf::input() ?>

      <div class="mb-3">
        <label class="form-label small fw-bold text-secondary">帳號 / 員工編號</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted"><i class="fa-regular fa-user"></i></span>
          <input type="text" name="username" class="form-control" placeholder="例如: admin 或 cso_lin" required autofocus>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-bold text-secondary">登入密碼</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
          <input type="password" name="password" class="form-control" placeholder="預設密碼: admin123" required>
        </div>
      </div>

      <button type="submit" class="btn btn-esg w-100 mt-2">
        <i class="fa-solid fa-right-to-bracket me-1"></i> 安全登入
      </button>
    </form>

    <hr class="my-4">

    <?php if (!empty($appConfig['allow_quick_login'])): ?>
    <!-- 僅限明確啟用的開發環境：測試角色一鍵快捷登入 -->
    <div>
      <div class="small fw-bold text-muted mb-2"><i class="fa-solid fa-flask-vial text-success me-1"></i> 角色切換快捷測試 (預設密碼: admin123)</div>
      <div class="d-flex flex-wrap gap-1">
        <a href="<?= $baseUrl ?>/auth/quick-login?user=admin" class="btn btn-outline-dark quick-role-btn">SysAdmin 資訊管理員</a>
        <a href="<?= $baseUrl ?>/auth/quick-login?user=cso_lin" class="btn btn-outline-success quick-role-btn">CSO 永續長</a>
        <a href="<?= $baseUrl ?>/auth/quick-login?user=esg_chen" class="btn btn-outline-primary quick-role-btn">ESG Manager 經理</a>
        <a href="<?= $baseUrl ?>/auth/quick-login?user=site_wang" class="btn btn-outline-info quick-role-btn">Dept Mgr 廠長</a>
        <a href="<?= $baseUrl ?>/auth/quick-login?user=op_zhang" class="btn btn-outline-secondary quick-role-btn">Operator 填報員</a>
        <a href="<?= $baseUrl ?>/auth/quick-login?user=auditor_liu" class="btn btn-outline-warning text-dark quick-role-btn">Auditor 查驗員</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
