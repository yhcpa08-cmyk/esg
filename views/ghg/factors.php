<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-database text-success me-2"></i>溫室氣體排放係數庫 (Emission Factors Database)
    </h4>
    <p class="text-muted mb-0 small">內建環境部氣候變遷署、台電電力排碳係數與 IPCC 第五次評估報告 (AR5) 係數庫</p>
  </div>

  <div class="d-flex gap-2">
    <a href="<?= $baseUrl ?>/ghg/factors/create" class="btn btn-success btn-sm">
      <i class="fa-solid fa-plus me-1"></i> 新增自訂排放係數
    </a>
  </div>
</div>

<!-- 範疇切換按鈕 -->
<div class="mb-3 d-flex gap-2">
  <a href="<?= $baseUrl ?>/ghg/factors" class="btn btn-sm <?= empty($scope) ? 'btn-success' : 'btn-outline-secondary' ?>">全部係數</a>
  <a href="<?= $baseUrl ?>/ghg/factors?scope=SCOPE1" class="btn btn-sm <?= $scope === 'SCOPE1' ? 'btn-danger' : 'btn-outline-danger' ?>">範疇一 (直接燃燒/逸散)</a>
  <a href="<?= $baseUrl ?>/ghg/factors?scope=SCOPE2" class="btn btn-sm <?= $scope === 'SCOPE2' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>">範疇二 (外購電力/蒸氣)</a>
  <a href="<?= $baseUrl ?>/ghg/factors?scope=SCOPE3" class="btn btn-sm <?= $scope === 'SCOPE3' ? 'btn-primary' : 'btn-outline-primary' ?>">範疇三 (差旅/原料/廢棄物)</a>
</div>

<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>編號</th>
          <th>範疇</th>
          <th>細項類別</th>
          <th>能源 / 燃料名稱</th>
          <th class="text-end">係數數值</th>
          <th>計量單位</th>
          <th>發布機構與版本</th>
          <th>適用年度</th>
          <th>啟用狀態</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($factors as $f): ?>
          <tr>
            <td><code>#<?= $f['id'] ?></code></td>
            <td>
              <?php if ($f['scope'] === 'SCOPE1'): ?>
                <span class="badge bg-danger">範疇一</span>
              <?php elseif ($f['scope'] === 'SCOPE2'): ?>
                <span class="badge bg-warning text-dark">範疇二</span>
              <?php else: ?>
                <span class="badge bg-primary">範疇三</span>
              <?php endif; ?>
            </td>
            <td class="fw-bold"><?= View::e($f['category']) ?></td>
            <td><?= View::e($f['fuel_name']) ?></td>
            <td class="text-end fw-bold text-success fs-6"><?= $f['factor_value'] ?></td>
            <td><code><?= View::e($f['unit']) ?></code></td>
            <td><?= View::e($f['source_org']) ?></td>
            <td><?= View::e($f['version_year']) ?></td>
            <td>
              <?php if ($f['is_active']): ?>
                <span class="badge bg-success">啟用中</span>
              <?php else: ?>
                <span class="badge bg-secondary">停用</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
