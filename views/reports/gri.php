<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-book-bookmark text-success me-2"></i>GRI Standards 2021 內容索引表 (Content Index)
    </h4>
    <p class="text-muted mb-0 small">自動彙整各模組活動數據與組織治理指標，產出符合全球永續性報告準則之標準索引表 (M06-01)</p>
  </div>

  <div class="d-flex gap-2">
    <a href="<?= $baseUrl ?>/reports/export?year=<?= $year ?>&format=excel" class="btn btn-outline-success btn-sm">
      <i class="fa-solid fa-file-excel me-1"></i> 匯出 Excel / CSV 底稿
    </a>
    <a href="<?= $baseUrl ?>/reports/export?year=<?= $year ?>&format=word" class="btn btn-outline-primary btn-sm">
      <i class="fa-solid fa-file-word me-1"></i> 匯出 Word 報告書草稿
    </a>
    <a href="<?= $baseUrl ?>/reports/export?year=<?= $year ?>&format=html" target="_blank" class="btn btn-success btn-sm">
      <i class="fa-solid fa-print me-1"></i> 列印確信底稿
    </a>
  </div>
</div>

<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th style="width: 20%;">GRI 準則名稱</th>
          <th style="width: 14%;">指標編號</th>
          <th style="width: 26%;">揭露主題說明</th>
          <th style="width: 30%;">年度彙整數值與揭露內容</th>
          <th style="width: 10%;" class="text-center">狀態</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($griItems as $item): ?>
          <tr>
            <td class="fw-bold text-dark"><?= View::e($item['standard']) ?></td>
            <td><code><?= View::e($item['item_code']) ?></code></td>
            <td><?= View::e($item['description']) ?></td>
            <td><?= View::e($item['value']) ?></td>
            <td class="text-center">
              <span class="badge bg-success bg-opacity-25 text-success border border-success"><?= View::e($item['status']) ?></span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
