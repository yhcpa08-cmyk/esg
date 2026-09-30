<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<!-- 頂部篩選工具列 -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-chart-pie text-success me-2"></i>ESG 戰情決策中心 (Executive Dashboard)
    </h4>
    <p class="text-muted mb-0 small">遵循 ISO 14064-1:2018 與 GHG Protocol 標準，即時監控全集團碳排與永續指標</p>
  </div>

  <form method="GET" action="<?= $baseUrl ?>/dashboard" class="d-flex align-items-center gap-2">
    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
      <?php for ($y = 2026; $y >= 2024; $y--): ?>
        <option value="<?= $y ?>" <?= $currentYear === $y ? 'selected' : '' ?>><?= $y ?> 年度盤查</option>
      <?php endfor; ?>
    </select>

    <select name="site_id" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="">全集團營運據點 (全部)</option>
      <?php foreach ($sites as $s): ?>
        <option value="<?= $s['id'] ?>" <?= $siteId == $s['id'] ? 'selected' : '' ?>><?= View::e($s['site_name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<!-- 頂部四大核心 KPI 指標卡片 (M07-01) -->
<div class="row g-3 mb-4">
  <!-- 總碳排卡片 -->
  <div class="col-xl-3 col-md-6">
    <div class="card card-stat p-3 bg-white border-start border-4 border-success">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small fw-bold">集團總溫室氣體排放量</span>
          <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format($ghgSummary['TOTAL'], 2) ?> <span class="fs-6 text-muted">tCO2e</span></h3>
        </div>
        <div class="p-3 bg-success bg-opacity-10 text-success rounded-3 fs-4">
          <i class="fa-solid fa-smog"></i>
        </div>
      </div>
      <div class="mt-2 small text-muted d-flex justify-content-between">
        <span>人均排放量: <strong><?= $perCapitaEmissions ?></strong> t/人</span>
        <span class="text-success"><i class="fa-solid fa-check-circle"></i> ISO 邊界涵蓋</span>
      </div>
    </div>
  </div>

  <!-- 範疇一 -->
  <div class="col-xl-3 col-md-6">
    <div class="card card-stat p-3 bg-white border-start border-4 border-danger">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small fw-bold">範疇一 直接排放 (Scope 1)</span>
          <h3 class="fw-bold mb-0 text-danger mt-1"><?= number_format($ghgSummary['SCOPE1'], 2) ?> <span class="fs-6 text-muted">tCO2e</span></h3>
        </div>
        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3 fs-4">
          <i class="fa-solid fa-fire-flame-curved"></i>
        </div>
      </div>
      <div class="mt-2 small text-muted">
        天然氣、公務油料與冷媒逸散
      </div>
    </div>
  </div>

  <!-- 範疇二 -->
  <div class="col-xl-3 col-md-6">
    <div class="card card-stat p-3 bg-white border-start border-4 border-warning">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small fw-bold">範疇二 能源間接 (Scope 2)</span>
          <h3 class="fw-bold mb-0 text-warning mt-1"><?= number_format($ghgSummary['SCOPE2'], 2) ?> <span class="fs-6 text-muted">tCO2e</span></h3>
        </div>
        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3 fs-4">
          <i class="fa-solid fa-bolt"></i>
        </div>
      </div>
      <div class="mt-2 small text-muted">
        外購電力排碳 (台電排碳係數 0.495)
      </div>
    </div>
  </div>

  <!-- 範疇三 -->
  <div class="col-xl-3 col-md-6">
    <div class="card card-stat p-3 bg-white border-start border-4 border-primary">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted small fw-bold">範疇三 其他間接 (Scope 3)</span>
          <h3 class="fw-bold mb-0 text-primary mt-1"><?= number_format($ghgSummary['SCOPE3'], 2) ?> <span class="fs-6 text-muted">tCO2e</span></h3>
        </div>
        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 fs-4">
          <i class="fa-solid fa-route"></i>
        </div>
      </div>
      <div class="mt-2 small text-muted">
        員工商務差旅、廢棄物與價值鏈
      </div>
    </div>
  </div>
</div>

<!-- SBTi 減碳目標偏差卡片 (M02-07 減碳路徑與警示) -->
<div class="alert <?= $sbti['is_lagging'] ? 'alert-danger border-danger' : 'alert-success border-success' ?> p-3 rounded-3 mb-4 shadow-sm">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-center gap-3">
      <div class="fs-2 text-<?= $sbti['is_lagging'] ? 'danger' : 'success' ?>">
        <i class="fa-solid fa-bullseye"></i>
      </div>
      <div>
        <h6 class="fw-bold mb-1">
          SBTi 1.5°C 減碳目標路徑監控 (基準年 <?= $sbti['baseline_year'] ?> -> 目標年 <?= $sbti['target_year'] ?> 總減碳 42%)
        </h6>
        <div class="small">
          <?= $currentYear ?> 當年度目標上限: <strong><?= number_format($sbti['target_emission_current'], 1) ?> tCO2e</strong> |
          實際審核排放: <strong><?= number_format($sbti['actual_emission_current'], 1) ?> tCO2e</strong> |
          偏差落後率: <span class="badge bg-<?= $sbti['is_lagging'] ? 'danger' : 'success' ?>"><?= $sbti['gap_pct'] ?>%</span>
        </div>
      </div>
    </div>
    <div>
      <a href="<?= $baseUrl ?>/ghg/sbti" class="btn btn-sm <?= $sbti['is_lagging'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
        查看減碳路徑模擬 <i class="fa-solid fa-arrow-right ms-1"></i>
      </a>
    </div>
  </div>
</div>

<!-- 圖表展示區 (Chart.js 視覺化 - M07-02) -->
<div class="row g-3 mb-4">
  <!-- 範疇占比圓餅圖 -->
  <div class="col-lg-4">
    <div class="card p-3 bg-white h-100 shadow-sm border-0">
      <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-pie text-success me-2"></i>溫室氣體範疇排放占比</h6>
      <div style="position: relative; height: 260px;">
        <canvas id="scopePieChart"></canvas>
      </div>
      <div class="mt-3 text-center small text-muted">
        範疇一: <?= number_format($ghgSummary['SCOPE1'], 1) ?> | 範疇二: <?= number_format($ghgSummary['SCOPE2'], 1) ?> | 範疇三: <?= number_format($ghgSummary['SCOPE3'], 1) ?>
      </div>
    </div>
  </div>

  <!-- 月度碳排走勢圖 -->
  <div class="col-lg-8">
    <div class="card p-3 bg-white h-100 shadow-sm border-0">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-line text-primary me-2"></i><?= $currentYear ?> 年度月份碳排放走勢 (tCO2e)</h6>
        <span class="badge bg-light text-dark">每月申報已核定</span>
      </div>
      <div style="position: relative; height: 260px;">
        <canvas id="monthlyTrendChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- 廠區比較與能源水資源指標 -->
<div class="row g-3 mb-4">
  <!-- 廠區排放量柱狀圖 -->
  <div class="col-lg-6">
    <div class="card p-3 bg-white shadow-sm border-0 h-100">
      <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-building-circle-check text-secondary me-2"></i>各廠區碳排放量分佈 (tCO2e)</h6>
      <div style="position: relative; height: 220px;">
        <canvas id="siteBarChart"></canvas>
      </div>
    </div>
  </div>

  <!-- 待辦簽核工作工單 (M05-03 多級審批) -->
  <div class="col-lg-6">
    <div class="card p-3 bg-white shadow-sm border-0 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-success me-2"></i>最新待辦與審批工單</h6>
        <a href="<?= $baseUrl ?>/tasks" class="btn btn-sm btn-outline-success">全覽</a>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
          <thead class="table-light">
            <tr>
              <th>任務標題</th>
              <th>填報人</th>
              <th>截止日</th>
              <th>狀態</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tasks as $t): ?>
              <tr>
                <td class="fw-bold text-truncate" style="max-width: 180px;"><?= View::e($t['task_title']) ?></td>
                <td><?= View::e($t['assigned_name']) ?></td>
                <td><?= View::e($t['due_date']) ?></td>
                <td>
                  <?php if ($t['current_status'] === 'APPROVED'): ?>
                    <span class="badge bg-success">已核定</span>
                  <?php elseif ($t['current_status'] === 'UNDER_REVIEW'): ?>
                    <span class="badge bg-warning text-dark">待主管審核</span>
                  <?php elseif ($t['current_status'] === 'REJECTED'): ?>
                    <span class="badge bg-danger">已退回</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">待填報</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- 系統稽核日誌留痕 (M01-05) -->
<div class="card p-3 bg-white shadow-sm border-0">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-clipboard-list text-info me-2"></i>最新資料異動稽核軌跡 (Audit Trail)</h6>
    <?php if (Auth::hasRole('ROLE_ADMIN')): ?>
      <a href="<?= $baseUrl ?>/admin/audit-logs" class="btn btn-sm btn-outline-secondary">完整稽核日誌</a>
    <?php endif; ?>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle mb-0" style="font-size: 0.82rem;">
      <thead class="table-light">
        <tr>
          <th>時間</th>
          <th>操作者</th>
          <th>客戶端 IP</th>
          <th>模組</th>
          <th>動作</th>
          <th>紀錄詳細</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentLogs as $log): ?>
          <tr>
            <td><?= View::e($log['created_at']) ?></td>
            <td class="fw-bold"><?= View::e($log['real_name'] ?? '系統自動') ?></td>
            <td><code><?= View::e($log['ip_address']) ?></code></td>
            <td><span class="badge bg-light text-dark border"><?= View::e($log['action_module']) ?></span></td>
            <td>
              <?php if ($log['action_type'] === 'INSERT'): ?>
                <span class="badge bg-success">新增</span>
              <?php elseif ($log['action_type'] === 'UPDATE'): ?>
                <span class="badge bg-primary">更新</span>
              <?php elseif ($log['action_type'] === 'DELETE'): ?>
                <span class="badge bg-danger">刪除</span>
              <?php elseif ($log['action_type'] === 'LOGIN'): ?>
                <span class="badge bg-info text-dark">登入</span>
              <?php else: ?>
                <span class="badge bg-secondary"><?= View::e($log['action_type']) ?></span>
              <?php endif; ?>
            </td>
            <td class="text-truncate" style="max-width: 300px;">
              <code><?= View::e($log['new_values'] ?? $log['old_values'] ?? '-') ?></code>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Chart.js 繪製腳本 -->
<script>
  // 1. 範疇圓餅圖
  const scopeCtx = document.getElementById('scopePieChart').getContext('2d');
  new Chart(scopeCtx, {
    type: 'doughnut',
    data: {
      labels: ['範疇一 (直接)', '範疇二 (電力)', '範疇三 (其他)'],
      datasets: [{
        data: [<?= (float)$ghgSummary['SCOPE1'] ?>, <?= (float)$ghgSummary['SCOPE2'] ?>, <?= (float)$ghgSummary['SCOPE3'] ?>],
        backgroundColor: ['#DC3545', '#FFC107', '#0D6EFD'],
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });

  // 2. 月度趨勢折線圖
  const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
  new Chart(trendCtx, {
    type: 'line',
    data: {
      labels: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
      datasets: [{
        label: '月度碳排 (tCO2e)',
        data: <?= json_encode($monthlyGhg) ?>,
        borderColor: '#198754',
        backgroundColor: 'rgba(25, 135, 84, 0.1)',
        fill: true,
        tension: 0.35,
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true, title: { display: true, text: '公噸 CO2e' } }
      }
    }
  });

  // 3. 廠區比較橫條圖
  const siteCtx = document.getElementById('siteBarChart').getContext('2d');
  const siteLabels = <?= json_encode(array_column($siteEmissions, 'site_name')) ?>;
  const siteData = <?= json_encode(array_map('floatval', array_column($siteEmissions, 'total'))) ?>;
  new Chart(siteCtx, {
    type: 'bar',
    data: {
      labels: siteLabels,
      datasets: [{
        label: '廠區排放量 (tCO2e)',
        data: siteData,
        backgroundColor: '#40916C',
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { beginAtZero: true }
      }
    }
  });
</script>
