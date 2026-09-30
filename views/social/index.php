<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-users text-primary me-2"></i>社會責任指標 (S - Social Metrics)
    </h4>
    <p class="text-muted mb-0 small">涵蓋多元包容性 (DEI)、職業安全衛生 (OHS GRI 403)、人才培育與社區公益</p>
  </div>

  <div class="d-flex gap-2">
    <a href="<?= $baseUrl ?>/social/create" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-plus me-1"></i> 填報年度社會指標
    </a>
  </div>
</div>

<!-- 關鍵社會指標卡片 (M03-01, M03-02, M03-03) -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card card-stat p-3 bg-white border-start border-4 border-primary">
      <div class="text-muted small fw-bold">女性員工占比 (DEI)</div>
      <h3 class="fw-bold mb-0 text-primary mt-1"><?= $femalePct ?>%</h3>
      <div class="small text-muted mt-1">女性主管: <?= (int)($summary['total_female_mgr'] ?? 0) ?> 人</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-stat p-3 bg-white border-start border-4 border-danger">
      <div class="text-muted small fw-bold">失能傷害頻率 (LTIFR)</div>
      <h3 class="fw-bold mb-0 text-danger mt-1"><?= $ltifr ?></h3>
      <div class="small text-muted mt-1">公式: $(\text{工傷次數} \times 10^6) / \text{總工時}$</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-stat p-3 bg-white border-start border-4 border-warning">
      <div class="text-muted small fw-bold">失能傷害嚴重率 (SR)</div>
      <h3 class="fw-bold mb-0 text-warning mt-1"><?= $sr ?></h3>
      <div class="small text-muted mt-1">公式: $(\text{損失日數} \times 10^6) / \text{總工時}$</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-stat p-3 bg-white border-start border-4 border-success">
      <div class="text-muted small fw-bold">全員人均受訓時數</div>
      <h3 class="fw-bold mb-0 text-success mt-1"><?= $avgTrainingHours ?> <span class="fs-6 text-muted">hr/人</span></h3>
      <div class="small text-muted mt-1">總時數: <?= number_format((float)($summary['total_training_hours'] ?? 0)) ?> 小時</div>
    </div>
  </div>
</div>

<!-- 各廠區社會指標細項清冊 -->
<div class="card bg-white shadow-sm border-0 mb-4">
  <div class="card-header bg-white py-3">
    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list-check me-2"></i><?= $year ?> 年度各廠區社會責任申報清冊</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>廠區</th>
          <th class="text-end">總員工數</th>
          <th class="text-end">女性員工</th>
          <th class="text-end">身心障礙人數</th>
          <th class="text-end">全年度總工時</th>
          <th class="text-end">工傷件數</th>
          <th class="text-end">損失日數</th>
          <th class="text-end">培訓總時數</th>
          <th class="text-end">社區公益投入 (NTD)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($metrics as $m): ?>
          <tr>
            <td class="fw-bold"><?= View::e($m['site_name']) ?></td>
            <td class="text-end fw-bold"><?= number_format($m['total_employees']) ?></td>
            <td class="text-end"><?= number_format($m['female_employees']) ?> (<?= $m['total_employees'] > 0 ? round(($m['female_employees'] / $m['total_employees']) * 100, 1) : 0 ?>%)</td>
            <td class="text-end"><?= number_format($m['disabled_employees']) ?></td>
            <td class="text-end"><?= number_format($m['total_work_hours']) ?></td>
            <td class="text-end <?= $m['occupational_injuries'] > 0 ? 'text-danger fw-bold' : '' ?>"><?= $m['occupational_injuries'] ?></td>
            <td class="text-end"><?= $m['lost_days'] ?></td>
            <td class="text-end text-success"><?= number_format((float)$m['training_hours_total']) ?> hr</td>
            <td class="text-end fw-bold text-primary">$<?= number_format((float)$m['community_investment_ntd']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
