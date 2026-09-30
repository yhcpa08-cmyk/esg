<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-plus text-success me-2"></i>新增溫室氣體排放係數 (New Emission Factor)
    </h4>
    <p class="text-muted mb-0 small">建檔環境部最新公告或國際第三方認證之溫室氣體排放係數</p>
  </div>
  <a href="<?= $baseUrl ?>/ghg/factors" class="btn btn-outline-secondary btn-sm">
    <i class="fa-solid fa-arrow-left me-1"></i> 返回係數庫
  </a>
</div>

<div class="card p-4 bg-white shadow-sm border-0" style="max-width: 700px;">
  <form method="POST" action="<?= $baseUrl ?>/ghg/factors/create">
    <?= Csrf::input() ?>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">盤查範疇分類 <span class="text-danger">*</span></label>
        <select name="scope" class="form-select" required>
          <option value="SCOPE1">範疇一 直接排放 (Scope 1)</option>
          <option value="SCOPE2">範疇二 能源間接 (Scope 2)</option>
          <option value="SCOPE3">範疇三 其他間接 (Scope 3)</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">細項類別 <span class="text-danger">*</span></label>
        <input type="text" name="category" class="form-control" placeholder="例如: 固定燃燒, 移動燃燒, 逸散排放" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label fw-bold small text-muted">燃料 / 能源名稱 <span class="text-danger">*</span></label>
      <input type="text" name="fuel_name" class="form-control" placeholder="例如: 生質柴油 B20, 氫能發電燃料" required>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">排放係數數值 <span class="text-danger">*</span></label>
        <input type="number" step="0.000001" name="factor_value" class="form-control" placeholder="例如: 2.150000" required>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">計量單位 <span class="text-danger">*</span></label>
        <input type="text" name="unit" class="form-control" placeholder="例如: kgCO2e/L, kgCO2e/度, kgCO2e/kg" required>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-8">
        <label class="form-label fw-bold small text-muted">發布機構與文號 <span class="text-danger">*</span></label>
        <input type="text" name="source_org" class="form-control" placeholder="例如: 環境部氣候變遷署 v7.2, IPCC AR6" required>
      </div>

      <div class="col-md-4">
        <label class="form-label fw-bold small text-muted">適用年度 <span class="text-danger">*</span></label>
        <input type="number" name="version_year" class="form-control" value="<?= date('Y') ?>" required>
      </div>
    </div>

    <button type="submit" class="btn btn-success px-4 py-2">
      <i class="fa-solid fa-floppy-disk me-1"></i> 儲存排放係數
    </button>
  </form>
</div>
