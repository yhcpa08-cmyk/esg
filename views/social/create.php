<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-plus text-primary me-2"></i>填報年度社會責任指標 (Social Metrics Entry)
    </h4>
    <p class="text-muted mb-0 small">錄入員工多元統計、職安工傷時數與公益投入</p>
  </div>
  <a href="<?= $baseUrl ?>/social" class="btn btn-outline-secondary btn-sm">
    <i class="fa-solid fa-arrow-left me-1"></i> 返回清冊
  </a>
</div>

<div class="card p-4 bg-white shadow-sm border-0" style="max-width: 800px;">
  <form method="POST" action="<?= $baseUrl ?>/social/create">
    <?= Csrf::input() ?>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">申報廠區 <span class="text-danger">*</span></label>
        <select name="site_id" class="form-select" required>
          <?php foreach ($sites as $s): ?>
            <option value="<?= $s['id'] ?>"><?= View::e($s['site_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">申報年度 <span class="text-danger">*</span></label>
        <input type="number" name="report_year" class="form-control" value="<?= date('Y') ?>" required>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">期末員工總人數</label>
        <input type="number" name="total_employees" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">女性同仁人數</label>
        <input type="number" name="female_employees" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">女性主管人數</label>
        <input type="number" name="female_managers" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">身心障礙人數</label>
        <input type="number" name="disabled_employees" class="form-control" value="0">
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label fw-bold small text-muted">年度全體總工作工時</label>
        <input type="number" name="total_work_hours" class="form-control" placeholder="如 1720000" required>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold small text-muted">工傷事故件數 (失能傷害)</label>
        <input type="number" name="occupational_injuries" class="form-control" value="0" required>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold small text-muted">損失工作日數</label>
        <input type="number" name="lost_days" class="form-control" value="0" required>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">年度培訓總時數 (Hours)</label>
        <input type="number" step="0.1" name="training_hours_total" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">社區公益投入總金額 (NTD)</label>
        <input type="number" step="1" name="community_investment_ntd" class="form-control" placeholder="如 2500000" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary px-4 py-2">
      <i class="fa-solid fa-floppy-disk me-1"></i> 儲存指標數據
    </button>
  </form>
</div>
