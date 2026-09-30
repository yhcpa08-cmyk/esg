<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-industry text-info me-2"></i>SASB 永續會計準則指標揭露表 (SASB Standards)
    </h4>
    <p class="text-muted mb-0 small">半導體與電子零組件業重大性主題指標映射 (M06-02)</p>
  </div>

  <div class="d-flex gap-2">
    <a href="<?= $baseUrl ?>/reports/gri" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-book-bookmark me-1"></i> 切換至 GRI 索引表
    </a>
  </div>
</div>

<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>重大主題 (Topic)</th>
          <th>SASB 會計指標代碼</th>
          <th>指標說明 (Metric Description)</th>
          <th>申報數值 (Reported Value)</th>
          <th>計量單位</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sasbMetrics as $s): ?>
          <tr>
            <td class="fw-bold"><?= View::e($s['topic']) ?></td>
            <td><code><?= View::e($s['code']) ?></code></td>
            <td><?= View::e($s['description']) ?></td>
            <td class="fw-bold text-success"><?= View::e($s['value']) ?></td>
            <td class="text-muted small"><?= View::e($s['unit']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
