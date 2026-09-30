<?php
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>
<style>
  .manual-hero { background: linear-gradient(120deg, #123d2c, #2d795a); color: #fff; border-radius: 1rem; padding: 2.25rem; position: relative; overflow: hidden; }
  .manual-hero:after { content: ''; position:absolute; width:280px; height:280px; border:42px solid rgba(255,255,255,.08); border-radius:50%; right:-105px; top:-145px; }
  .manual-search { max-width: 720px; position: relative; z-index: 1; }
  .manual-search .input-group-text { background:#fff; border:0; color:#2d6a4f; }
  .manual-search input { border:0; box-shadow:none!important; }
  .manual-result-count { min-height: 1.5rem; }
  .manual-toc { position: sticky; top: 86px; }
  .manual-toc .list-group-item { border:0; border-left:3px solid transparent; color:#456; font-size:.9rem; padding:.55rem .8rem; }
  .manual-toc .list-group-item:hover, .manual-toc .list-group-item.active { color:#1b4332; background:#eaf5ee; border-left-color:#40916c; font-weight:700; }
  .manual-section { scroll-margin-top: 92px; }
  .manual-section > h2 { color:#173f2e; font-size:1.35rem; font-weight:700; margin: 2.25rem 0 1rem; padding-bottom:.65rem; border-bottom:2px solid #d9eee1; }
  .manual-section:first-child > h2 { margin-top:0; }
  .manual-card { border:0; border-radius:.85rem; box-shadow:0 2px 10px rgba(17,65,47,.07); margin-bottom:1rem; overflow:hidden; }
  .manual-card .card-body { padding:1.25rem; }
  .manual-step { display:flex; align-items:flex-start; gap:.8rem; margin-bottom:.8rem; }
  .manual-step:last-child { margin-bottom:0; }
  .step-no { flex:0 0 28px; width:28px; height:28px; border-radius:50%; color:#fff; background:#2d6a4f; text-align:center; line-height:28px; font-size:.82rem; font-weight:700; }
  .screen-demo { background:#f6faf7; border:1px solid #cfe5d5; border-radius:.65rem; overflow:hidden; font-size:.76rem; margin:1rem 0; color:#334; }
  .screen-top { height:25px; padding:5px 9px; background:#fff; border-bottom:1px solid #dfe9e2; display:flex; align-items:center; gap:5px; }
  .screen-dot { width:7px; height:7px; border-radius:50%; background:#80c59a; }.screen-dot:nth-child(2){background:#f0c36a}.screen-dot:nth-child(3){background:#e78275}
  .screen-body { display:flex; min-height:140px; }
  .screen-nav { width:27%; min-width:105px; background:linear-gradient(#1b4332,#123126); color:#d8f3dc; padding:10px 8px; }
  .screen-nav strong { color:#fff; display:block; padding-bottom:7px; font-size:.78rem; border-bottom:1px solid #4f7f69; }.screen-nav span { display:block; padding:5px 2px; }.screen-nav span.active { background:#4c9574; color:#fff; padding-left:5px; border-radius:3px; }
  .screen-content { padding:14px; flex:1; }.screen-title { font-weight:700; color:#173f2e; font-size:.95rem; margin-bottom:10px; }.screen-kpis { display:flex; gap:7px; }.screen-kpi { flex:1; background:#fff; border-left:3px solid #3d956d; padding:8px; border-radius:3px; }.screen-kpi b { display:block; color:#1d6c4a; font-size:1.05rem; }.screen-table { margin-top:9px; background:#fff; border:1px solid #e0ebe3; }.screen-row { height:16px; border-bottom:1px solid #edf3ee; margin:0 7px; }.screen-row:last-child { border:0; }.screen-form { background:#fff; border-radius:4px; padding:9px; border:1px solid #e0ebe3; }.fake-field { display:inline-block; height:19px; border:1px solid #cadbd0; border-radius:2px; width:43%; margin:3px 2%; background:#fff; }.fake-button { display:inline-block; padding:4px 9px; color:#fff; background:#2d6a4f; border-radius:3px; margin:5px 2%; }
  .manual-faq .accordion-button:not(.collapsed) { color:#1b4332; background:#eaf5ee; box-shadow:none; }.manual-faq .accordion-button:focus { box-shadow:0 0 0 .2rem rgba(45,106,79,.15); }
  .manual-key { background:#e9f5ed; color:#1b5b3f; font-weight:700; padding:.08rem .35rem; border-radius:.2rem; font-size:.85em; }
  @media(max-width:991px){.manual-toc{position:static}.manual-hero{padding:1.5rem}.screen-nav{width:32%}}
</style>

<section class="manual-hero mb-4">
  <span class="badge rounded-pill text-bg-light text-success mb-2">新手管理者版 · SPEC-2026 V1.0</span>
  <h1 class="h3 fw-bold mb-2">ESG 智慧管理系統操作手冊</h1>
  <p class="mb-3 text-white-50">從登入、填報、審核到報表匯出；輸入問題或關鍵字，即可立即找到處理方式。</p>
  <div class="input-group input-group-lg manual-search shadow-sm">
    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
    <input id="manualSearch" type="search" class="form-control" placeholder="例如：如何新增碳排？、找不到送審按鈕、匯出報表" autocomplete="off">
    <button id="manualClear" class="btn btn-light text-success" type="button">清除</button>
  </div>
  <div id="manualResultCount" class="manual-result-count small mt-2 text-white-50" aria-live="polite"></div>
</section>

<div class="row g-4">
  <aside class="col-lg-3">
    <div class="card manual-card manual-toc">
      <div class="card-body p-2">
        <div class="px-2 pt-2 pb-1 text-uppercase text-success fw-bold" style="font-size:.72rem;letter-spacing:.08em">手冊目錄</div>
        <nav class="list-group list-group-flush" id="manualToc">
          <a href="#start" class="list-group-item list-group-item-action active">開始前：登入與權限</a>
          <a href="#overview" class="list-group-item list-group-item-action">認識首頁與導覽</a>
          <a href="#environment" class="list-group-item list-group-item-action">環境資料：碳、能、水</a>
          <a href="#social-governance" class="list-group-item list-group-item-action">社會與治理指標</a>
          <a href="#workflow" class="list-group-item list-group-item-action">工作台與簽核</a>
          <a href="#supplier-report" class="list-group-item list-group-item-action">供應商與報告書</a>
          <?php if ($isAdmin): ?><a href="#admin" class="list-group-item list-group-item-action">系統管理</a><?php endif; ?>
          <a href="#faq" class="list-group-item list-group-item-action">常見問題排除</a>
        </nav>
      </div>
    </div>
    <div class="alert alert-success small mt-3"><i class="fa-solid fa-lightbulb me-1"></i><strong>快速原則：</strong>先選正確的年度、廠區與部門，再輸入數值；資料送出後請到「填報審批工作台」追蹤狀態。</div>
  </aside>
  <div class="col-lg-9">
    <div id="manualContent">
      <section class="manual-section" id="start" data-search="登入 登出 帳號 密碼 權限 角色 管理者">
        <h2><i class="fa-solid fa-right-to-bracket me-2"></i>開始前：登入與權限</h2>
        <div class="card manual-card"><div class="card-body"><h3 class="h6 fw-bold">第一次使用的順序</h3>
          <div class="manual-step"><span class="step-no">1</span><div>開啟登入頁，輸入由系統管理員建立的帳號與密碼後按「登入系統」。連續輸入錯誤達 5 次，帳號會暫時鎖定 15 分鐘。</div></div>
          <div class="manual-step"><span class="step-no">2</span><div>登入後，右上角會顯示姓名與角色。請先確認角色正確，再開始填報或審核。</div></div>
          <div class="manual-step"><span class="step-no">3</span><div>完成作業請點右上角 <span class="manual-key">登出圖示</span>，尤其是共用電腦。</div></div>
          <div class="alert alert-warning small mt-3 mb-0"><strong>看不到功能不是系統故障：</strong>系統依角色與資料範圍顯示功能。填報人只能操作自己的部門資料；廠區主管限所屬廠區；系統管理員才能進入「系統管理中心」。</div>
        </div></div>
      </section>

      <section class="manual-section" id="overview" data-search="首頁 戰情總覽 儀表板 圖表 篩選 年度 廠區 導覽">
        <h2><i class="fa-solid fa-chart-pie me-2"></i>認識首頁與左側導覽</h2>
        <div class="card manual-card"><div class="card-body"><p class="mb-2">登入後預設進入「戰情總覽首頁」。上方下拉選單可切換 <strong>年度</strong> 與 <strong>廠區</strong>；畫面上的數字、圖表與待辦會同步更新。</p>
          <div class="screen-demo" role="img" aria-label="戰情總覽首頁示意畫面，左側有模組選單，右側有年度與廠區篩選、碳排指標卡與圖表。"><div class="screen-top"><i class="screen-dot"></i><i class="screen-dot"></i><i class="screen-dot"></i></div><div class="screen-body"><div class="screen-nav"><strong><i class="fa-solid fa-leaf"></i> ESG 智慧管理</strong><span class="active">◉ 戰情總覽首頁</span><span>≋ 溫室氣體碳盤查</span><span>✓ 填報審批工作台</span><span>▣ 永續報告書</span></div><div class="screen-content"><div class="screen-title">ESG 戰情決策中心　　<span class="badge text-bg-light border">2026 年度</span></div><div class="screen-kpis"><div class="screen-kpi">集團總排放量<b>1,248.50</b>tCO2e</div><div class="screen-kpi">範疇一<b>248.20</b>tCO2e</div><div class="screen-kpi">範疇二<b>680.40</b>tCO2e</div></div><div class="screen-table"><div class="screen-row"></div><div class="screen-row"></div><div class="screen-row"></div></div></div></div></div>
          <p class="small text-muted mb-0"><strong>畫面說明：</strong>左側是所有工作模組；右側卡片顯示已核定資料。若需要輸入或修改，請從左側進入對應的新增功能。</p>
        </div></div>
      </section>

      <section class="manual-section" id="environment" data-search="碳排 溫室氣體 活動數據 範疇 Scope 排放係數 能源 用電 用水 廢棄物 SBTi 佐證 檔案">
        <h2><i class="fa-solid fa-smog me-2"></i>環境資料：碳盤查、能資源與 SBTi</h2>
        <div class="card manual-card"><div class="card-body"><h3 class="h6 fw-bold text-success">新增一筆溫室氣體活動數據</h3>
          <div class="manual-step"><span class="step-no">1</span><div>選 <a href="<?= $baseUrl ?>/ghg/create">環境管理 → 活動數據錄入</a>。</div></div>
          <div class="manual-step"><span class="step-no">2</span><div>依序選擇廠區、部門、排放源係數與日期；再輸入活動量。例如電力用量、天然氣量或油料量。</div></div>
          <div class="manual-step"><span class="step-no">3</span><div>可上傳發票、抄表或其他佐證檔。系統會依排放係數自動換算 tCO2e。</div></div>
          <div class="manual-step"><span class="step-no">4</span><div>若本次數值與歷史平均差異超過設定門檻，填寫「異常原因說明」後才能提送。這是避免誤填的必要檢查。</div></div>
          <div class="screen-demo" role="img" aria-label="活動數據錄入表單示意畫面。"><div class="screen-top"><i class="screen-dot"></i><i class="screen-dot"></i><i class="screen-dot"></i></div><div class="screen-body"><div class="screen-nav"><strong>環境管理 (E)</strong><span>≋ 碳盤查清冊</span><span class="active">＋ 活動數據錄入</span><span>▣ 排放係數庫</span><span>ϟ 能水耗監控</span></div><div class="screen-content"><div class="screen-title">新增活動數據填報</div><div class="screen-form">廠區　部門　排放源係數<br><i class="fake-field"></i><i class="fake-field"></i><br>活動日期　活動量　佐證檔案<br><i class="fake-field"></i><i class="fake-field"></i><br><i class="fake-button">儲存並提送</i></div></div></div></div>
          <p class="small mb-0"><strong>接著做：</strong>到 <a href="<?= $baseUrl ?>/ghg/records">溫室氣體碳盤查</a>，用範疇、廠區或狀態篩選確認紀錄。要查看用電、綠電、用水與廢棄物，進入 <a href="<?= $baseUrl ?>/ghg/energy-water">能水耗監控</a>；選「新增資料」填入月份資料。SBTi 減碳目標與年度落差可在 <a href="<?= $baseUrl ?>/ghg/sbti">SBTi 減碳路徑</a> 檢視。</p>
        </div></div>
      </section>

      <section class="manual-section" id="social-governance" data-search="DEI 職安 員工 女性 訓練 工傷 社會責任 董事會 TCFD 治理 資安">
        <h2><i class="fa-solid fa-people-group me-2"></i>社會與治理指標</h2>
        <div class="row g-3"><div class="col-md-6"><div class="card manual-card h-100"><div class="card-body"><h3 class="h6 fw-bold">DEI 多元與職安</h3><p class="small">進入 <a href="<?= $baseUrl ?>/social">DEI 多元與職安</a> 先以年度查看彙總。具填報權限者點「新增年度指標」，輸入員工數、女性員工／主管、身心障礙員工、總工時、工傷、失能日、訓練時數及社會投入。</p><p class="small mb-0 text-muted">注意：女性員工、女性主管與身心障礙員工的人數不可大於總員工數。</p></div></div></div><div class="col-md-6"><div class="card manual-card h-100"><div class="card-body"><h3 class="h6 fw-bold">董事會與 TCFD</h3><p class="small">在 <a href="<?= $baseUrl ?>/governance">董事會與 TCFD</a> 選年度檢視董事席次、獨立／女性董事、出席率、反貪腐訓練、吹哨與資安事件，以及氣候風險矩陣。</p><p class="small mb-0 text-muted">只有永續長與永續專責小組可儲存治理指標；輸入比例時請使用 0–100 的數字。</p></div></div></div></div>
      </section>

      <section class="manual-section" id="workflow" data-search="工作台 任務 工單 審核 簽核 退回 核定 待填報">
        <h2><i class="fa-solid fa-list-check me-2"></i>填報審批工作台</h2>
        <div class="card manual-card"><div class="card-body"><p>所有填報任務及簽核狀態都在 <a href="<?= $baseUrl ?>/tasks">填報審批工作台</a>。使用上方「狀態」篩選快速找出待處理項目。</p>
          <div class="row text-center g-2 small"><div class="col-6 col-md-3"><div class="p-2 border rounded bg-light"><i class="fa-solid fa-pen text-secondary d-block mb-1"></i><strong>待填報</strong><br>填報人處理</div></div><div class="col-6 col-md-3"><div class="p-2 border rounded bg-warning bg-opacity-10"><i class="fa-solid fa-clock text-warning d-block mb-1"></i><strong>待主管審核</strong><br>簽核人處理</div></div><div class="col-6 col-md-3"><div class="p-2 border rounded bg-success bg-opacity-10"><i class="fa-solid fa-check text-success d-block mb-1"></i><strong>已核定</strong><br>納入統計</div></div><div class="col-6 col-md-3"><div class="p-2 border rounded bg-danger bg-opacity-10"><i class="fa-solid fa-arrow-rotate-left text-danger d-block mb-1"></i><strong>已退回</strong><br>依意見修正</div></div></div>
          <ol class="small mt-3 mb-0"><li>永續長或永續專責小組建立任務，指定填報人、簽核人與截止日。</li><li>簽核人開啟任務，選擇核定或退回，並填寫意見。</li><li>核定後的環境資料才會被納入首頁趨勢與報表統計；退回時請依意見補正後重新處理。</li></ol>
        </div></div>
      </section>

      <section class="manual-section" id="supplier-report" data-search="供應商 問卷 CAPA 改善 報告書 GRI SASB 匯出 Excel Word 查驗底稿">
        <h2><i class="fa-solid fa-file-export me-2"></i>供應商評鑑與永續報告書</h2>
        <div class="card manual-card"><div class="card-body"><h3 class="h6 fw-bold">供應商 ESG 評鑑</h3><p class="small">在 <a href="<?= $baseUrl ?>/suppliers">供應商 ESG 評鑑</a> 查看風險分級、問卷狀態與 CAPA 改善進度。永續專責小組可新增供應商並取得專屬問卷連結；供應商自行完成 E、S、G 題組後，系統會計算風險等級。高風險案件請更新 CAPA 狀態與改善說明。</p><hr><h3 class="h6 fw-bold">產出報告書</h3><p class="small mb-0">先到 <a href="<?= $baseUrl ?>/reports/gri">GRI 2021 索引</a> 或 <a href="<?= $baseUrl ?>/reports/sasb">SASB 會計指標</a> 核對年度數據；再點「確信底稿匯出」，選擇預覽列印、Excel 相容 CSV 或 Word。匯出內容會留下稽核紀錄。</p></div></div>
      </section>

      <?php if ($isAdmin): ?><section class="manual-section" id="admin" data-search="系統管理 廠區 部門 帳號 使用者 RBAC 角色 權限 參數 稽核日誌 密碼">
        <h2><i class="fa-solid fa-user-gear me-2"></i>系統管理（僅系統管理員）</h2>
        <div class="card manual-card"><div class="card-body"><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>功能</th><th>何時使用</th><th>操作提醒</th></tr></thead><tbody><tr><td><a href="<?= $baseUrl ?>/admin/sites">廠區與邊界</a></td><td>新增營運據點、設定盤查邊界</td><td>先建立廠區，才能建立部門與帳號。</td></tr><tr><td><a href="<?= $baseUrl ?>/admin/departments">組織部門樹</a></td><td>建立部門及上下層關係</td><td>部門必須屬於既有廠區。</td></tr><tr><td><a href="<?= $baseUrl ?>/admin/users">使用者帳號</a></td><td>建立、停用帳號或重設密碼</td><td>設定正確角色、廠區與部門，決定資料可見範圍。</td></tr><tr><td><a href="<?= $baseUrl ?>/admin/roles">RBAC 權限</a></td><td>查看角色權限矩陣</td><td>修改帳號前，先確認職務所需的最小權限。</td></tr><tr><td><a href="<?= $baseUrl ?>/admin/settings">系統參數設定</a></td><td>調整異常門檻、附件大小等</td><td>調整前應取得永續治理負責人的確認。</td></tr><tr><td><a href="<?= $baseUrl ?>/admin/audit-logs">系統稽核日誌</a></td><td>追查新增、更新、登入與匯出</td><td>以時間、操作者、模組及動作查核。</td></tr></tbody></table></div></div></div>
      </section><?php endif; ?>

      <section class="manual-section manual-faq" id="faq" data-search="問題 排除 找不到 失敗 錯誤 異常 不能 佐證 上傳 密碼 匯出">
        <h2><i class="fa-solid fa-life-ring me-2"></i>常見問題與快速排除</h2>
        <div class="accordion" id="faqAccordion">
          <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">為什麼看不到「新增」或「儲存」按鈕？</button></h3><div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body small">先確認右上角角色。部分功能只開放給永續專責小組、廠區主管或填報人；系統管理功能僅系統管理員可用。若職務已變更，請請系統管理員在「使用者帳號」調整角色與資料範圍。</div></div></div>
          <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">碳排活動量被提示異常，怎麼處理？</button></h3><div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body small">系統會和歷史平均比較。請確認單位、抄表區間與小數點；數值正確但確有異動時，在表單填寫具體的異常原因（如產量增加、設備維修或補登月份），再提送並附佐證檔。</div></div></div>
          <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq3">填報後，首頁的統計數字沒有變？</button></h3><div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body small">首頁統計採用「已核定」資料。請到填報審批工作台確認是否仍為待填報／待審核，並請指定簽核人完成核定；同時確認首頁篩選的是正確年度與廠區。</div></div></div>
          <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq4">無法上傳佐證檔案或匯出報表？</button></h3><div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body small">上傳時請確認檔案副檔名、大小符合系統設定，並重新整理頁面後再試。匯出前請先確認年度資料與 GRI 索引；若瀏覽器封鎖下載，允許此網站下載檔案後重試。持續發生時，將發生時間、操作頁面及錯誤畫面提供給系統管理員查閱稽核日誌。</div></div></div>
          <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq5">忘記密碼或帳號被鎖定怎麼辦？</button></h3><div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body small">請聯絡系統管理員重設密碼。連續錯誤 5 次會鎖定 15 分鐘；可等待解除後再登入，或由管理員協助處理。請勿共用帳號，以維持稽核紀錄正確。</div></div></div>
        </div>
      </section>
      <div id="manualNoResult" class="alert alert-light border text-center py-4 d-none"><i class="fa-solid fa-magnifying-glass text-success fs-4 d-block mb-2"></i>找不到相符內容。請改用較短的關鍵字，例如「碳排」、「審核」、「匯出」或「帳號」。</div>
    </div>
  </div>
</div>

<script>
(() => {
  const input = document.getElementById('manualSearch'), clear = document.getElementById('manualClear');
  const sections = [...document.querySelectorAll('.manual-section')], count = document.getElementById('manualResultCount'), noResult = document.getElementById('manualNoResult');
  const normalise = v => v.toLowerCase().replace(/\s+/g, ' ').trim();
  function filter() {
    const query = normalise(input.value);
    const terms = query.split(/[，,、。！？?\s]+/).filter(term => term.length >= 2);
    let visible = 0;
    sections.forEach(section => {
      const searchable = normalise(section.textContent + ' ' + (section.dataset.search || ''));
      // 支援完整關鍵字，也支援「如何新增碳排」這類自然語句的任一有效詞彙。
      const matched = !query || searchable.includes(query) || terms.some(term => searchable.includes(term));
      section.classList.toggle('d-none', !matched);
      if (matched) visible++;
    });
    count.textContent = query ? `找到 ${visible} 個相關章節` : '';
    noResult.classList.toggle('d-none', visible !== 0);
  }
  input.addEventListener('input', filter);
  clear.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
  document.querySelectorAll('#manualToc a').forEach(link => link.addEventListener('click', () => {
    document.querySelectorAll('#manualToc a').forEach(item => item.classList.remove('active')); link.classList.add('active');
  }));
})();
</script>
