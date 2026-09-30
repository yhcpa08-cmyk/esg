<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-file-circle-plus text-success me-2"></i>活動數據填報錄入 (GHG Activity Data Entry)
    </h4>
    <p class="text-muted mb-0 small">套用最新環境部/IPCC排放係數，智慧即時試算碳排當量，內建 ±20% 偏差防錯機制</p>
  </div>
  <a href="<?= $baseUrl ?>/ghg/records" class="btn btn-outline-secondary btn-sm">
    <i class="fa-solid fa-arrow-left me-1"></i> 返回清冊
  </a>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger py-3 d-flex align-items-center gap-2 mb-4 shadow-sm border-danger">
    <i class="fa-solid fa-circle-exclamation fs-4"></i>
    <div><?= $error ?></div>
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-8">
    <div class="card p-4 bg-white shadow-sm border-0 mb-4">
      <form method="POST" action="<?= $baseUrl ?>/ghg/create" enctype="multipart/form-data">
        <?= Csrf::input() ?>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold small text-muted">歸屬營運廠區 <span class="text-danger">*</span></label>
            <select name="site_id" id="siteSelect" class="form-select" required>
              <?php foreach ($sites as $s): ?>
                <option value="<?= $s['id'] ?>"><?= View::e($s['site_name']) ?> (<?= View::e($s['site_code']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold small text-muted">責任部門 <span class="text-danger">*</span></label>
            <select name="dept_id" class="form-select" required>
              <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>"><?= View::e($d['dept_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-8">
            <label class="form-label fw-bold small text-muted">排放源與燃料係數 <span class="text-danger">*</span></label>
            <select name="factor_id" id="factorSelect" class="form-select" required onchange="updateCalcPreview()">
              <option value="">-- 請選擇燃料/能源類別 --</option>
              <?php foreach ($factors as $f): ?>
                <option value="<?= $f['id'] ?>" 
                        data-factor="<?= $f['factor_value'] ?>" 
                        data-unit="<?= View::e($f['unit']) ?>"
                        data-scope="<?= $f['scope'] ?>">
                  [<?= $f['scope'] ?>] <?= View::e($f['fuel_name']) ?> (<?= $f['factor_value'] ?> <?= View::e($f['unit']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold small text-muted">數據歸屬日期 <span class="text-danger">*</span></label>
            <input type="date" name="record_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold small text-muted">原始活動消耗量 <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" step="0.0001" name="activity_amount" id="activityAmount" class="form-control" placeholder="例如: 3500" required oninput="updateCalcPreview()">
              <span class="input-group-text bg-light text-muted" id="unitLabel">單位</span>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold small text-muted">發票 / 電單等單據佐證 (PDF/圖片)</label>
            <input type="file" name="evidence_file" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.xlsx">
          </div>
        </div>

        <!-- 異常防錯說明欄 (M05-04) -->
        <div class="mb-4">
          <label class="form-label fw-bold small text-muted">
            <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
            異常數值偏差說明 (若數值與歷史平均偏差達 ±20% 時強制必填)
          </label>
          <textarea name="anomaly_reason" class="form-control" rows="2" placeholder="如遇歲修、冰水主機擴建、產線增產或冷媒灌注等特殊狀況，請詳述原因說明供主管審查核定。"></textarea>
        </div>

        <button type="submit" class="btn btn-success px-4 py-2">
          <i class="fa-solid fa-check me-1"></i> 提送審批工作流
        </button>
      </form>
    </div>
  </div>

  <!-- 右側即時運算卡片 (Live Preview) -->
  <div class="col-lg-4">
    <div class="card p-4 bg-light border-0 shadow-sm rounded-3 mb-4">
      <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-calculator text-success me-2"></i>即時運算預覽 (Live Calc)</h6>
      
      <div class="mb-3">
        <small class="text-muted d-block">套用係數數值：</small>
        <span class="fw-bold fs-5 text-dark" id="previewFactorVal">-</span>
        <span class="text-muted small" id="previewFactorUnit"></span>
      </div>

      <div class="mb-3">
        <small class="text-muted d-block">預估溫室氣體排放量：</small>
        <h2 class="fw-bold text-success mb-0" id="previewCo2eVal">0.0000</h2>
        <span class="text-muted small">公噸 CO2e (Metric Tons)</span>
      </div>

      <div class="alert alert-info py-2 px-3 small mb-0 border-0">
        <i class="fa-solid fa-info-circle me-1"></i>
        公式：$E = \frac{\text{活動量} \times \text{排放係數}}{1000}$<br>
        提送後將自動寫入稽核軌跡，並派送至主管審批工作台。
      </div>
    </div>
  </div>
</div>

<script>
  function updateCalcPreview() {
    const factorSelect = document.getElementById('factorSelect');
    const selectedOption = factorSelect.options[factorSelect.selectedIndex];
    const amount = parseFloat(document.getElementById('activityAmount').value || 0);

    if (selectedOption && selectedOption.dataset.factor) {
      const factor = parseFloat(selectedOption.dataset.factor);
      const unit = selectedOption.dataset.unit || '';
      
      document.getElementById('unitLabel').innerText = unit;
      document.getElementById('previewFactorVal').innerText = factor;
      document.getElementById('previewFactorUnit').innerText = unit;

      let co2e = 0.0;
      if (unit.toLowerCase().includes('tco2e') || unit.includes('公噸')) {
        co2e = (amount * factor);
      } else {
        co2e = (amount * factor) / 1000.0;
      }
      document.getElementById('previewCo2eVal').innerText = co2e.toFixed(4);
    } else {
      document.getElementById('previewFactorVal').innerText = '-';
      document.getElementById('previewCo2eVal').innerText = '0.0000';
    }
  }
</script>
