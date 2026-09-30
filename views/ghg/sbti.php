<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-bullseye text-success me-2"></i>SBTi 科學基礎減量目標追蹤 (Science Based Targets initiative)
    </h4>
    <p class="text-muted mb-0 small">比對 1.5°C 淨零路徑目標軌跡與實際經第三方確信排放量之偏差度 (Gap Analysis)</p>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-4">
    <div class="card p-4 bg-white shadow-sm border-0 h-100">
      <h6 class="fw-bold text-dark mb-3">SBTi 承諾架構設定</h6>
      <ul class="list-group list-group-flush small">
        <li class="list-group-item d-flex justify-content-between px-0">
          <span class="text-muted">基準年 (Baseline Year)</span>
          <strong><?= $sbti['baseline_year'] ?> 年</strong>
        </li>
        <li class="list-group-item d-flex justify-content-between px-0">
          <span class="text-muted">基準年總排放量</span>
          <strong><?= number_format($sbti['baseline_emissions'], 2) ?> tCO2e</strong>
        </li>
        <li class="list-group-item d-flex justify-content-between px-0">
          <span class="text-muted">目標年 (Target Year)</span>
          <strong><?= $sbti['target_year'] ?> 年</strong>
        </li>
        <li class="list-group-item d-flex justify-content-between px-0">
          <span class="text-muted">SBTi 承諾總降幅目標</span>
          <span class="badge bg-success fs-6">42.0%</span>
        </li>
        <li class="list-group-item d-flex justify-content-between px-0">
          <span class="text-muted">國際減碳路徑</span>
          <span class="text-success fw-bold">1.5°C 升溫抑制目標</span>
        </li>
      </ul>

      <hr>

      <div class="p-3 bg-light rounded-3 text-center">
        <small class="text-muted d-block">當前考核狀態 (落後 >5% 觸發紅燈)</small>
        <h5 class="fw-bold mt-1 mb-0 <?= $sbti['is_lagging'] ? 'text-danger' : 'text-success' ?>">
          <?= $sbti['status_label'] ?>
        </h5>
        <div class="small mt-1 text-muted">目前偏差量: <?= number_format($sbti['gap_co2e'], 2) ?> tCO2e (<?= $sbti['gap_pct'] ?>%)</div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card p-4 bg-white shadow-sm border-0 h-100">
      <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-area text-success me-2"></i>2024 - 2030 SBTi 減碳目標軌跡與實際排放比較 (tCO2e)</h6>
      <div style="position: relative; height: 320px;">
        <canvas id="sbtiChart"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
  // 生成 2024 到 2030 之目標曲線
  const years = [2024, 2025, 2026, 2027, 2028, 2029, 2030];
  const baseEm = <?= (float)$sbti['baseline_emissions'] ?>;
  const targetEm = baseEm * (1 - 0.42);
  const step = (baseEm - targetEm) / 6.0;

  const targetLine = years.map((y, idx) => (baseEm - (step * idx)).toFixed(1));
  const actualLine = [
    baseEm,
    (baseEm * 0.94).toFixed(1),
    <?= (float)$sbti['actual_emission_current'] ?>,
    null, null, null, null
  ];

  const ctx = document.getElementById('sbtiChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: years.map(y => y + '年'),
      datasets: [
        {
          label: 'SBTi 1.5°C 目標軌跡 (上限)',
          data: targetLine,
          borderColor: '#198754',
          borderDash: [5, 5],
          tension: 0.2
        },
        {
          label: '實際審批排碳量 (tCO2e)',
          data: actualLine,
          borderColor: '#DC3545',
          backgroundColor: '#DC3545',
          pointRadius: 6,
          pointHoverRadius: 8
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: false, title: { display: true, text: '公噸 CO2e' } }
      }
    }
  });
</script>
