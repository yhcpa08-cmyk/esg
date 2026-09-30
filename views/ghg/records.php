<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-smog text-success me-2"></i>溫室氣體盤查清冊 (GHG Inventory)
    </h4>
    <p class="text-muted mb-0 small">符合 ISO 14064-1:2018 與 GHG Protocol 之組織層級活動數據與碳排當量清冊</p>
  </div>

  <div class="d-flex gap-2">
    <a href="<?= $baseUrl ?>/ghg/create" class="btn btn-success btn-sm">
      <i class="fa-solid fa-plus me-1"></i> 新增活動數據填報
    </a>
  </div>
</div>

<!-- 篩選器 -->
<div class="card p-3 bg-white shadow-sm border-0 mb-4">
  <form method="GET" action="<?= $baseUrl ?>/ghg/records" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label small fw-bold text-muted">盤查範疇</label>
      <select name="scope" class="form-select form-select-sm">
        <option value="">全部範疇 (Scope 1 ~ 3)</option>
        <option value="SCOPE1" <?= $scope === 'SCOPE1' ? 'selected' : '' ?>>範疇一 直接排放 (Scope 1)</option>
        <option value="SCOPE2" <?= $scope === 'SCOPE2' ? 'selected' : '' ?>>範疇二 能源間接 (Scope 2)</option>
        <option value="SCOPE3" <?= $scope === 'SCOPE3' ? 'selected' : '' ?>>範疇三 其他間接 (Scope 3)</option>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label small fw-bold text-muted">營運廠區</label>
      <select name="site_id" class="form-select form-select-sm">
        <option value="">全部廠區</option>
        <?php foreach ($sites as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $siteId == $s['id'] ? 'selected' : '' ?>><?= View::e($s['site_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label small fw-bold text-muted">審批狀態</label>
      <select name="status" class="form-select form-select-sm">
        <option value="">全部狀態</option>
        <option value="SUBMITTED" <?= $status === 'SUBMITTED' ? 'selected' : '' ?>>已提交 (待審批)</option>
        <option value="APPROVED" <?= $status === 'APPROVED' ? 'selected' : '' ?>>已核准 (有效碳排)</option>
        <option value="REJECTED" <?= $status === 'REJECTED' ? 'selected' : '' ?>>已退回</option>
      </select>
    </div>

    <div class="col-md-3">
      <button type="submit" class="btn btn-outline-success btn-sm w-100">
        <i class="fa-solid fa-filter me-1"></i> 套用篩選
      </button>
    </div>
  </form>
</div>

<!-- 資料清冊表格 -->
<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>編號</th>
          <th>歸屬日期</th>
          <th>廠區 / 部門</th>
          <th>範疇</th>
          <th>能源燃料名稱</th>
          <th>原始活動量</th>
          <th>套用係數</th>
          <th class="text-end">碳排放當量 (tCO2e)</th>
          <th>佐證憑證</th>
          <th>審批狀態</th>
          <th>填報人員</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($records)): ?>
          <tr><td colspan="11" class="text-center py-4 text-muted">查無相符之溫室氣體活動數據紀錄</td></tr>
        <?php else: ?>
          <?php foreach ($records as $r): ?>
            <tr>
              <td><code>#<?= $r['id'] ?></code></td>
              <td><?= View::e($r['record_date']) ?></td>
              <td>
                <div class="fw-bold"><?= View::e($r['site_name']) ?></div>
                <small class="text-muted"><?= View::e($r['dept_name']) ?></small>
              </td>
              <td>
                <?php if ($r['scope'] === 'SCOPE1'): ?>
                  <span class="badge bg-danger">範疇一</span>
                <?php elseif ($r['scope'] === 'SCOPE2'): ?>
                  <span class="badge bg-warning text-dark">範疇二</span>
                <?php else: ?>
                  <span class="badge bg-primary">範疇三</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="fw-bold"><?= View::e($r['fuel_name']) ?></div>
                <small class="text-muted"><?= View::e($r['category']) ?></small>
              </td>
              <td>
                <strong><?= number_format((float)$r['activity_amount'], 2) ?></strong>
                <span class="text-muted small"><?= View::e(explode('/', $r['unit'])[1] ?? '') ?></span>
                <?php if (!empty($r['anomaly_reason'])): ?>
                  <span class="badge bg-danger-subtle text-danger ms-1" title="<?= View::e($r['anomaly_reason']) ?>">
                    <i class="fa-solid fa-triangle-exclamation"></i> 異動備註
                  </span>
                <?php endif; ?>
              </td>
              <td class="text-muted small">
                <?= $r['factor_value'] ?> <?= View::e($r['unit']) ?>
              </td>
              <td class="text-end fw-bold text-success fs-6">
                <?= number_format((float)$r['calculated_co2e'], 4) ?>
              </td>
              <td>
                <?php if (!empty($r['evidence_file_url'])): ?>
                  <a href="<?= $baseUrl ?>/<?= View::e($r['evidence_file_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:0.75rem;">
                    <i class="fa-solid fa-file-arrow-down"></i> 單據
                  </a>
                <?php else: ?>
                  <span class="text-muted small">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($r['data_status'] === 'APPROVED'): ?>
                  <span class="badge bg-success">已核定</span>
                <?php elseif ($r['data_status'] === 'SUBMITTED'): ?>
                  <span class="badge bg-warning text-dark">待主管審核</span>
                <?php else: ?>
                  <span class="badge bg-secondary"><?= View::e($r['data_status']) ?></span>
                <?php endif; ?>
              </td>
              <td class="small text-muted"><?= View::e($r['creator_name']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
