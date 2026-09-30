<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-plus text-success me-2"></i>錄入月份能資源消耗數據 (Monthly Resource Entry)
    </h4>
    <p class="text-muted mb-0 small">登記水電瓦斯度數、T-REC綠電憑證扣抵度數與廢棄物重量</p>
  </div>
  <a href="<?= $baseUrl ?>/ghg/energy-water" class="btn btn-outline-secondary btn-sm">
    <i class="fa-solid fa-arrow-left me-1"></i> 返回清單
  </a>
</div>

<div class="card p-4 bg-white shadow-sm border-0" style="max-width: 750px;">
  <form method="POST" action="<?= $baseUrl ?>/ghg/energy-water/create">
    <?= Csrf::input() ?>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">營運廠區 <span class="text-danger">*</span></label>
        <select name="site_id" class="form-select" required>
          <?php foreach ($sites as $s): ?>
            <option value="<?= $s['id'] ?>"><?= View::e($s['site_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">申報年月 (YYYY-MM) <span class="text-danger">*</span></label>
        <input type="month" name="period_year_month" class="form-control" value="<?= date('Y-m') ?>" required>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">總用電量 (kWh) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="electricity_kwh" class="form-control" placeholder="如台電電費單用電度數" required>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">再生能源綠電使用量 (kWh)</label>
        <input type="number" step="0.01" name="renewable_kwh" class="form-control" placeholder="自建太陽能或綠電轉供">
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">自來水與總用水量 (m3 / 公噸) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="water_m3" class="form-control" placeholder="如自來水公司水費度數" required>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">製程水資源循環回收量 (m3)</label>
        <input type="number" step="0.01" name="recycled_water_m3" class="form-control" placeholder="回收再利用水">
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">一般生活垃圾重量 (kg)</label>
        <input type="number" step="0.01" name="waste_general_kg" class="form-control" placeholder="焚化/掩埋一般垃圾">
      </div>

      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">有害事業廢棄物重量 (kg)</label>
        <input type="number" step="0.01" name="waste_hazardous_kg" class="form-control" placeholder="化學品廢液等有害事業廢棄物">
      </div>
    </div>

    <button type="submit" class="btn btn-success px-4 py-2">
      <i class="fa-solid fa-floppy-disk me-1"></i> 儲存能資源紀錄
    </button>
  </form>
</div>
