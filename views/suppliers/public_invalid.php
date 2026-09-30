<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>問卷憑證無效 - ESG 供應鏈自評</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center p-3" style="min-height: 100vh;">
  <div class="text-center p-4 p-md-5 bg-white shadow-sm rounded-4 w-100" style="max-width: 480px;">
    <h3 class="text-danger mb-3 fw-bold">問卷憑證失效</h3>
    <p class="text-muted"><?= htmlspecialchars($message ?? '該問卷填答連結無效或已過期，請洽詢採購承辦人員。') ?></p>
  </div>
</body>
</html>
