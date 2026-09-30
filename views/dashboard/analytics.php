<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-chart-line text-primary me-2"></i>碳盤查多維度交叉分析 (Analytics & Heatmap)
    </h4>
    <p class="text-muted mb-0 small">深入剖析各能源細項熱點、範疇佔比與能資源消耗趨勢</p>
  </div>

  <form method="GET" action="<?= $baseUrl ?>/dashboard/analytics" class="d-flex align-items-center gap-2">
    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
      <?php for ($y = 2026; $y >= 2024; $y--): ?>
        <option value="<?= $y ?>" <?= $currentYear === $y ? 'selected' : '' ?>><?= $y ?> 年度分析</option>
      <?php endfor; ?>
    </select>
  </form>
</div>

<div class="row g-3 mb-4">
  <!-- 排放源細項列表 -->
  <div class="col-lg-6">
    <div class="card p-3 bg-white shadow-sm border-0 h-100">
      <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-fire text-danger me-2"></i>排放源細項分類統計 (按碳排量排序)</h6>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
          <thead class="table-light">
            <tr>
              <th>細項類別</th>
              <th>所屬範疇</th>
              <th class="text-end">累計排碳量 (tCO2e)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categoryBreakdown as $cat): ?>
              <tr>
                <td class="fw-bold"><?= View::e($cat['category']) ?></td>
                <td>
                  <?php if ($cat['scope'] === 'SCOPE1'): ?>
                    <span class="badge bg-danger">範疇一</span>
                  <?php elseif ($cat['scope'] === 'SCOPE2'): ?>
                    <span class="badge bg-warning text-dark">範疇二</span>
                  <?php else: ?>
                    <span class="badge bg-primary">範疇三</span>
                  <?php endif; ?>
                </td>
                <td class="text-end fw-bold text-success"><?= number_format((float)$cat['total_co2e'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- 能資源月度消耗趨勢 -->
  <div class="col-lg-6">
    <div class="card p-3 bg-white shadow-sm border-0 h-100">
      <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-bolt text-warning me-2"></i><?= $currentYear ?> 能資源消耗趨勢 (電力與水資源)</h6>
      <div style="position: relative; height: 280px;">
        <canvas id="energyTrendChart"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
  const energyTrends = <?= json_encode($energyTrends) ?>;
  const periods = energyTrends.map(e => e.period_year_month);
  const elecData = energyTrends.map(e => parseFloat(e.elec || 0));
  const renewData = energyTrends.map(e => parseFloat(e.renew || 0));

  const ctx = document.getElementById('energyTrendChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: periods,
      datasets: [
        {
          label: '總用電量 (kWh)',
          data: elecData,
          backgroundColor: '#0D6EFD',
          borderRadius: 4
        },
        {
          label: '再生能源綠電 (kWh)',
          data: renewData,
          backgroundColor: '#198754',
          borderRadius: 4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>
