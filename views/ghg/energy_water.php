<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-bolt text-warning me-2"></i>能源與水資源消耗監控 (Energy & Water Monitoring)
    </h4>
    <p class="text-muted mb-0 small">監控各廠區總用電、再生能源綠電抵換、用水量、製程回收水及廢棄物處置</p>
  </div>

  <div class="d-flex gap-2">
    <a href="<?= $baseUrl ?>/ghg/energy-water/create" class="btn btn-success btn-sm">
      <i class="fa-solid fa-plus me-1"></i> 錄入月份能水耗數據
    </a>
  </div>
</div>

<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>申報年月</th>
          <th>廠區</th>
          <th class="text-end">總用電量 (kWh)</th>
          <th class="text-end">綠電使用 (kWh)</th>
          <th class="text-end">綠電比率 (%)</th>
          <th class="text-end">總用水量 (m3)</th>
          <th class="text-end">水回收量 (m3)</th>
          <th class="text-end">一般廢棄物 (kg)</th>
          <th class="text-end">有害廢棄物 (kg)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $d): ?>
          <?php 
            $elec = (float)$d['electricity_kwh'];
            $renew = (float)$d['renewable_kwh'];
            $renewPct = $elec > 0 ? round(($renew / $elec) * 100, 1) : 0;
          ?>
          <tr>
            <td class="fw-bold"><?= View::e($d['period_year_month']) ?></td>
            <td><?= View::e($d['site_name']) ?></td>
            <td class="text-end fw-bold"><?= number_format($elec) ?></td>
            <td class="text-end text-success fw-bold"><?= number_format($renew) ?></td>
            <td class="text-end">
              <span class="badge bg-success bg-opacity-25 text-success"><?= $renewPct ?>%</span>
            </td>
            <td class="text-end"><?= number_format((float)$d['water_m3']) ?></td>
            <td class="text-end text-primary fw-bold"><?= number_format((float)$d['recycled_water_m3']) ?></td>
            <td class="text-end"><?= number_format((float)$d['waste_general_kg']) ?></td>
            <td class="text-end text-danger"><?= number_format((float)$d['waste_hazardous_kg']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
