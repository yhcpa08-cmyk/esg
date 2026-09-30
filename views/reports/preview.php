<?php
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>永續報告書確信底稿 - <?= $year ?> 年度</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: "微軟正黑體", sans-serif; background: #fff; color: #212529; }
    @media print {
      .no-print { display: none !important; }
      body { font-size: 11pt; }
    }
    @media (max-width: 575.98px) {
      body { padding: 1rem !important; font-size: 0.9rem; }
      .no-print { gap: 0.5rem; flex-wrap: wrap; }
      .text-center.mb-5 { margin-bottom: 2rem !important; }
      h2 { font-size: 1.35rem; }
      .table-responsive { margin: 0 -1rem 1.5rem; padding: 0 1rem; overflow-x: auto; }
    }
  </style>
</head>
<body class="p-5">

  <div class="no-print d-flex justify-content-between mb-4 pb-2 border-bottom">
    <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm">返回</a>
    <button onclick="window.print()" class="btn btn-success btn-sm">列印此確信底稿 (Print)</button>
  </div>

  <div class="text-center mb-5">
    <div style="color: #1B4332; font-weight: bold; font-size: 1.2rem;">ESG 企業永續智慧管理系統</div>
    <h2 class="fw-bold my-2" style="color: #1B4332;"><?= $year ?> 年度企業永續報告書查證確信底稿</h2>
    <div class="text-muted small">遵循 ISO 14064-1:2018 / GHG Protocol / GRI Standards 2021</div>
    <div class="small text-muted mt-1">產出時間：<?= date('Y-m-d H:i:s') ?> | 文件編號：AUD-ESG-<?= $year ?>-V1.0</div>
  </div>

  <h4 class="fw-bold mb-3" style="color: #1B4332; border-bottom: 2px solid #1B4332; padding-bottom: 6px;">
    一、溫室氣體組織盤查統計清單
  </h4>
  <div class="table-responsive"><table class="table table-bordered mb-4">
    <thead class="table-light">
      <tr>
        <th>盤查範疇</th>
        <th>排放源涵蓋項目</th>
        <th class="text-end">碳排放當量 (公噸 CO2e)</th>
        <th class="text-end">占比 (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>範疇一 (Scope 1)</strong></td>
        <td>天然氣固定燃燒、公務車柴汽油移動燃燒、冷媒設備逸散</td>
        <td class="text-end fw-bold"><?= number_format($ghgSummary['SCOPE1'], 2) ?></td>
        <td class="text-end"><?= $ghgSummary['TOTAL'] > 0 ? round(($ghgSummary['SCOPE1'] / $ghgSummary['TOTAL']) * 100, 1) : 0 ?>%</td>
      </tr>
      <tr>
        <td><strong>範疇二 (Scope 2)</strong></td>
        <td>外購公用電力 (台電電力排放係數 0.495 kgCO2e/度)</td>
        <td class="text-end fw-bold"><?= number_format($ghgSummary['SCOPE2'], 2) ?></td>
        <td class="text-end"><?= $ghgSummary['TOTAL'] > 0 ? round(($ghgSummary['SCOPE2'] / $ghgSummary['TOTAL']) * 100, 1) : 0 ?>%</td>
      </tr>
      <tr>
        <td><strong>範疇三 (Scope 3)</strong></td>
        <td>員工商務高鐵飛機差旅、廢棄物清運處置、原料運輸</td>
        <td class="text-end fw-bold"><?= number_format($ghgSummary['SCOPE3'], 2) ?></td>
        <td class="text-end"><?= $ghgSummary['TOTAL'] > 0 ? round(($ghgSummary['SCOPE3'] / $ghgSummary['TOTAL']) * 100, 1) : 0 ?>%</td>
      </tr>
      <tr class="table-success fw-bold fs-6">
        <td colspan="2">全集團溫室氣體排放總量合計</td>
        <td class="text-end"><?= number_format($ghgSummary['TOTAL'], 2) ?></td>
        <td class="text-end">100.0%</td>
      </tr>
    </tbody>
  </table></div>

  <h4 class="fw-bold mb-3" style="color: #1B4332; border-bottom: 2px solid #1B4332; padding-bottom: 6px;">
    二、GRI Standards 2021 內容索引對照清冊
  </h4>
  <div class="table-responsive"><table class="table table-bordered mb-4" style="font-size: 0.95rem;">
    <thead class="table-light">
      <tr>
        <th>GRI 準則</th>
        <th>指標編號</th>
        <th>揭露項目說明</th>
        <th>確信數值與揭露內容</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($griItems as $item): ?>
        <tr>
          <td class="fw-bold"><?= View::e($item['standard']) ?></td>
          <td><code><?= View::e($item['item_code']) ?></code></td>
          <td><?= View::e($item['description']) ?></td>
          <td><?= View::e($item['value']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>

  <div class="row mt-5 pt-4 text-center">
    <div class="col-4">
      <div class="border-top pt-2">填報製表人員</div>
    </div>
    <div class="col-4">
      <div class="border-top pt-2">永續專責主管覆核</div>
    </div>
    <div class="col-4">
      <div class="border-top pt-2">獨立第三方查驗機構簽證</div>
    </div>
  </div>

</body>
</html>
