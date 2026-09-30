<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>供應商 ESG 永續自評問卷 (SAQ) - 線上填答入口</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    body {
      background-color: #f4f7f6;
      font-family: "微軟正黑體", sans-serif;
      padding-bottom: 50px;
    }
    .survey-header {
      background: linear-gradient(135deg, #1B4332 0%, #2D6A4F 100%);
      color: #fff;
      padding: 2.5rem 1rem;
      border-bottom: 3px solid #52B788;
      text-align: center;
    }
    .section-card {
      border: none;
      border-radius: 0.75rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      margin-bottom: 1.5rem;
    }
    @media (max-width: 575.98px) {
      .survey-header { padding: 1.75rem 1rem; }
      .survey-header h3 { font-size: 1.2rem; }
      .section-card { margin-bottom: 1rem; }
      .supplier-summary { align-items: flex-start !important; flex-direction: column; gap: 0.75rem; }
      .supplier-summary .text-end { text-align: left !important; }
    }
  </style>
</head>
<body>

<header class="survey-header">
  <div class="container" style="max-width: 800px;">
    <div style="font-size: 2.2rem; margin-bottom: 0.5rem;"><i class="fa-solid fa-leaf text-success"></i></div>
    <h3 class="fw-bold mb-1">企業綠色供應鏈 - ESG 永續績效自評問卷 (SAQ)</h3>
    <p class="mb-0 text-white-50">Enterprise Sustainable Supply Chain Self-Assessment Questionnaire</p>
  </div>
</header>

<div class="container my-4" style="max-width: 800px;">
  <!-- 受評供應商基本資料卡片 -->
  <div class="card p-3 section-card bg-white">
    <div class="d-flex justify-content-between align-items-center supplier-summary">
      <div>
        <span class="text-muted small">受評廠商：</span>
        <h5 class="fw-bold text-dark mb-0"><?= View::e($supplier['supplier_name']) ?> (統編: <?= View::e($supplier['supplier_code']) ?>)</h5>
      </div>
      <div class="text-end">
        <span class="text-muted small">評鑑年度：</span>
        <span class="badge bg-success fs-6"><?= $supplier['eval_year'] ?> 年度</span>
      </div>
    </div>
  </div>

  <?php if ($submitted): ?>
    <!-- 提交完成提示與即時評級結果 -->
    <div class="card p-5 section-card bg-white text-center shadow-sm">
      <div class="text-success mb-3" style="font-size: 3.5rem;">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <h3 class="fw-bold text-dark mb-2">感謝您的填答！問卷已成功送達</h3>
      <p class="text-muted">本系統已依權重公式 (環境 40%、社會 30%、治理 30%) 完成即時評鑑運算：</p>
      
      <div class="p-4 bg-light rounded-3 my-3 mx-auto" style="max-width: 450px;">
        <div class="text-muted small">綜合加權評鑑總分</div>
        <h1 class="fw-bold text-dark my-1"><?= $evalResult['total_score'] ?> <span class="fs-6 text-muted">/ 100 分</span></h1>
        <div class="mt-2">
          <span class="badge bg-<?= $evalResult['risk_level'] === 'LOW_A' ? 'success' : ($evalResult['risk_level'] === 'MED_B' ? 'warning text-dark' : 'danger') ?> fs-6 px-3 py-2">
            <?= $evalResult['risk_label'] ?>
          </span>
        </div>
      </div>

      <?php if ($evalResult['risk_level'] === 'HIGH_C'): ?>
        <div class="alert alert-danger mx-auto mt-2" style="max-width: 500px;">
          <i class="fa-solid fa-triangle-exclamation me-1"></i>
          評鑑總分未達 60 分 (C級)，系統已立案 CAPA 限期改善工單，敬請配合後續採購稽核作業。
        </div>
      <?php endif; ?>

      <div class="mt-4">
        <a href="<?= $baseUrl ?>/suppliers/public-survey?token=<?= View::e($token) ?>" class="btn btn-outline-secondary btn-sm">
          查看填答紀錄
        </a>
      </div>
    </div>
  <?php else: ?>
    <!-- 問卷填寫表單 -->
    <form method="POST" action="<?= $baseUrl ?>/suppliers/public-survey?token=<?= View::e($token) ?>">
      <?= \App\Core\Csrf::input() ?>
      
      <!-- 一、環境構面 (40%) -->
      <div class="card p-4 section-card bg-white">
        <h5 class="fw-bold text-success border-bottom pb-2 mb-3">
          <i class="fa-solid fa-earth-americas me-2"></i>一、環境保護構面 (Environmental - 權重 40%)
        </h5>

        <div class="mb-3">
          <label class="form-label fw-bold small text-dark">1. 貴公司是否已取得 ISO 14001 環境管理系統第三方認證？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="env_iso14001" id="e1_1" value="30" checked>
              <label class="form-check-label" for="e1_1">是，已取得有效證書 (30分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="env_iso14001" id="e1_2" value="15">
              <label class="form-check-label" for="e1_2">輔導建置中 (15分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="env_iso14001" id="e1_3" value="0">
              <label class="form-check-label" for="e1_3">尚未建置 (0分)</label>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold small text-dark">2. 貴公司是否定期進行溫室氣體盤查 (ISO 14064-1 或 GHG Protocol)？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="env_ghg_inv" id="e2_1" value="40" checked>
              <label class="form-check-label" for="e2_1">每年完成外部第三方確信 (40分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="env_ghg_inv" id="e2_2" value="25">
              <label class="form-check-label" for="e2_2">內部自主盤查試算 (25分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="env_ghg_inv" id="e2_3" value="0">
              <label class="form-check-label" for="e2_3">尚未盤查 (0分)</label>
            </div>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label fw-bold small text-dark">3. 貴公司是否已設定使用綠電或節能減碳目標？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="env_renew" id="e3_1" value="30" checked>
              <label class="form-check-label" for="e3_1">有明確減碳路徑與綠電使用 (30分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="env_renew" id="e3_2" value="0">
              <label class="form-check-label" for="e3_2">尚未規劃 (0分)</label>
            </div>
          </div>
        </div>
      </div>

      <!-- 二、社會責任構面 (30%) -->
      <div class="card p-4 section-card bg-white">
        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
          <i class="fa-solid fa-people-roof me-2"></i>二、社會責任構面 (Social - 權重 30%)
        </h5>

        <div class="mb-3">
          <label class="form-label fw-bold small text-dark">1. 貴公司是否通過 ISO 45001 職業安全衛生管理驗證？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="soc_iso45001" id="s1_1" value="35" checked>
              <label class="form-check-label" for="s1_1">是，已取得驗證 (35分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="soc_iso45001" id="s1_2" value="0">
              <label class="form-check-label" for="s1_2">否 (0分)</label>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold small text-dark">2. 貴公司是否嚴格禁止強迫勞動、童工並遵守人權準則？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="soc_labor" id="s2_1" value="35" checked>
              <label class="form-check-label" for="s2_1">簽署人權承諾書且無違規情事 (35分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="soc_labor" id="s2_2" value="0">
              <label class="form-check-label" for="s2_2">無 (0分)</label>
            </div>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label fw-bold small text-dark">3. 貴公司是否每年定期為全體同仁辦理專業技能與工安訓練？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="soc_training" id="s3_1" value="30" checked>
              <label class="form-check-label" for="s3_1">是，每年全體人均 > 15 小時 (30分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="soc_training" id="s3_2" value="15">
              <label class="form-check-label" for="s3_2">不定期辦理 (15分)</label>
            </div>
          </div>
        </div>
      </div>

      <!-- 三、公司治理構面 (30%) -->
      <div class="card p-4 section-card bg-white">
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
          <i class="fa-solid fa-scale-balanced me-2"></i>三、公司治理構面 (Governance - 權重 30%)
        </h5>

        <div class="mb-3">
          <label class="form-label fw-bold small text-dark">1. 貴公司是否制定誠信經營守則、反貪腐政策與檢舉管道？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gov_ethics" id="g1_1" value="40" checked>
              <label class="form-check-label" for="g1_1">有完備制度並落實全員宣導 (40分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gov_ethics" id="g1_2" value="0">
              <label class="form-check-label" for="g1_2">未建立 (0分)</label>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold small text-dark">2. 貴公司是否建立營業秘密與資訊安全保護機制？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gov_cyber" id="g2_1" value="30" checked>
              <label class="form-check-label" for="g2_1">通過 ISO 27001 或有嚴格資安管制 (30分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gov_cyber" id="g2_2" value="15">
              <label class="form-check-label" for="g2_2">一般內部管制 (15分)</label>
            </div>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label fw-bold small text-dark">3. 財務報表是否定期由執業會計師查核簽證？</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gov_trans" id="g3_1" value="30" checked>
              <label class="form-check-label" for="g3_1">是，具備無保留意見簽證 (30分)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gov_trans" id="g3_2" value="15">
              <label class="form-check-label" for="g3_2">僅委託記帳 (15分)</label>
            </div>
          </div>
        </div>
      </div>

      <!-- 廠商備註 -->
      <div class="card p-4 section-card bg-white">
        <label class="form-label fw-bold small text-muted">其他補充說明或證書說明</label>
        <textarea name="supplier_comment" class="form-control" rows="2" placeholder="可簡述已取得之證書有效期限或後續推動規劃。"></textarea>
      </div>

      <div class="text-center mt-4">
        <button type="submit" class="btn btn-success btn-lg px-5 py-2 shadow">
          <i class="fa-solid fa-paper-plane me-2"></i>確認送出自評問卷
        </button>
      </div>
    </form>
  <?php endif; ?>
</div>

</body>
</html>
