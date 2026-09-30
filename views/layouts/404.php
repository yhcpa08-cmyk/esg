<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 頁面未找到 - ESG 企業永續智慧管理系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center p-3" style="min-height: 100vh;">
  <div class="text-center p-4 p-md-5 bg-white shadow-sm rounded-4 w-100" style="max-width: 500px;">
    <div class="text-success mb-3" style="font-size: 4rem;">
      <i class="fa-solid fa-compass"></i>
    </div>
    <h2 class="fw-bold text-dark">404 找不到該頁面</h2>
    <p class="text-muted">您請求的路徑 <code><?= htmlspecialchars($path ?? '') ?></code> 不存在或已被移除。</p>
    <a href="/ESG/dashboard" class="btn btn-success px-4 py-2 mt-2">返回戰情儀表板</a>
  </div>
</body>
</html>
