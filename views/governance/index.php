<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-landmark text-secondary me-2"></i>公司治理與氣候風險 (G - Governance & TCFD)
    </h4>
    <p class="text-muted mb-0 small">涵蓋董事會結構、誠信經營反貪腐、TCFD 氣候風險與機會熱圖、資訊安全合規</p>
  </div>

  <form method="GET" action="<?= $baseUrl ?>/governance" class="d-flex align-items-center gap-2">
    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
      <?php for ($y = 2026; $y >= 2024; $y--): ?>
        <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?> 年度治理指標</option>
      <?php endfor; ?>
    </select>
  </form>
</div>

<!-- 治理四大指標卡片 (M04-01, M04-02, M04-04) -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card card-stat p-3 bg-white border-start border-4 border-success">
      <div class="text-muted small fw-bold">獨立董事席次佔比</div>
      <?php 
        $indepPct = $gov['board_seats_total'] > 0 ? round(($gov['independent_directors'] / $gov['board_seats_total']) * 100, 1) : 0;
      ?>
      <h3 class="fw-bold mb-0 text-success mt-1"><?= $indepPct ?>%</h3>
      <div class="small text-muted mt-1"><?= $gov['independent_directors'] ?> 席 / 總席次 <?= $gov['board_seats_total'] ?> 席 (符合 > 1/3)</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-stat p-3 bg-white border-start border-4 border-primary">
      <div class="text-muted small fw-bold">女性董事席次 (多元化)</div>
      <h3 class="fw-bold mb-0 text-primary mt-1"><?= $gov['female_directors'] ?> 席</h3>
      <div class="small text-muted mt-1">符合主管機關女性董事 &ge; 1 席要求</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-stat p-3 bg-white border-start border-4 border-warning">
      <div class="text-muted small fw-bold">董事會平均出席率</div>
      <h3 class="fw-bold mb-0 text-warning mt-1"><?= $gov['board_attendance_rate'] ?>%</h3>
      <div class="small text-muted mt-1">審計/薪酬/永續各委員會平均</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-stat p-3 bg-white border-start border-4 border-info">
      <div class="text-muted small fw-bold">反貪腐教育訓練率</div>
      <h3 class="fw-bold mb-0 text-info mt-1"><?= $gov['anti_corruption_trained'] ?>%</h3>
      <div class="small text-muted mt-1">受理檢舉案件數: <?= $gov['whistleblower_cases'] ?> 件</div>
    </div>
  </div>
</div>

<!-- TCFD 氣候風險鑑別矩陣與熱圖 (M04-03) -->
<div class="card p-4 bg-white shadow-sm border-0 mb-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-temperature-arrow-up text-danger me-2"></i>TCFD 氣候風險鑑別熱圖 (Risk Matrix Heatmap)</h6>
      <small class="text-muted">依據發生機率 (1-5) 與財務衝擊 (1-5) 矩陣運算，得分 &ge; 15 自動標定為重大風險</small>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>風險類別</th>
          <th>氣候風險事件描述</th>
          <th class="text-center">發生機率 (1-5)</th>
          <th class="text-center">財務衝擊 (1-5)</th>
          <th class="text-center">風險得分</th>
          <th>風險等級</th>
          <th>企業因應策略與調適方案</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tcfdRisks as $risk): ?>
          <tr>
            <td class="fw-bold"><?= View::e($risk['type']) ?></td>
            <td><?= View::e($risk['name']) ?></td>
            <td class="text-center fw-bold"><?= $risk['probability'] ?></td>
            <td class="text-center fw-bold"><?= $risk['impact'] ?></td>
            <td class="text-center fs-6 fw-bold <?= $risk['score'] >= 15 ? 'text-danger' : 'text-warning' ?>">
              <?= $risk['score'] ?>
            </td>
            <td>
              <?php if ($risk['score'] >= 15): ?>
                <span class="badge bg-danger">重大風險 (高)</span>
              <?php else: ?>
                <span class="badge bg-warning text-dark">中度風險</span>
              <?php endif; ?>
            </td>
            <td class="small text-muted"><?= View::e($risk['strategy']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 治理指標編輯維護表單 (M04-01 ~ M04-04) -->
<div class="card p-4 bg-white shadow-sm border-0">
  <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-pen-to-square text-success me-2"></i>維護 <?= $year ?> 年度治理與資安合規指標</h6>
  <form method="POST" action="<?= $baseUrl ?>/governance/save">
    <?= Csrf::input() ?>
    <input type="hidden" name="report_year" value="<?= $year ?>">

    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted">董事會總席次</label>
        <input type="number" name="board_seats_total" class="form-control" value="<?= $gov['board_seats_total'] ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted">獨立董事席次</label>
        <input type="number" name="independent_directors" class="form-control" value="<?= $gov['independent_directors'] ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted">女性董事席次</label>
        <input type="number" name="female_directors" class="form-control" value="<?= $gov['female_directors'] ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted">平均出席率 (%)</label>
        <input type="number" step="0.01" name="board_attendance_rate" class="form-control" value="<?= $gov['board_attendance_rate'] ?>" required>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted">反貪腐培訓受訓率 (%)</label>
        <input type="number" step="0.01" name="anti_corruption_trained" class="form-control" value="<?= $gov['anti_corruption_trained'] ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted">受理檢舉案件數</label>
        <input type="number" name="whistleblower_cases" class="form-control" value="<?= $gov['whistleblower_cases'] ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-bold text-muted">重大資安事件數 (目標 0 件)</label>
        <input type="number" name="cyber_security_incidents" class="form-control" value="<?= $gov['cyber_security_incidents'] ?>" required>
      </div>
      <div class="col-md-3 d-flex align-items-center mt-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="iso27001_certified" id="iso27001Check" <?= !empty($gov['iso27001_certified']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold small text-muted" for="iso27001Check">通過 ISO 27001 資安驗證</label>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-success px-4 py-2">
      <i class="fa-solid fa-floppy-disk me-1"></i> 儲存公司治理指標
    </button>
  </form>
</div>
