<?php
use App\Core\Auth;
use App\Core\View;
use App\Services\AiAssistantService;

$user = Auth::user();
$baseUrl = Auth::baseUrl();
$appConfig = require dirname(__DIR__, 2) . '/config/app.php';
$aiAssistantEnabled = AiAssistantService::settings()['is_enabled'];
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= View::e($pageTitle ?? 'ESG 企業永續智慧管理系統') ?></title>
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons & FontAwesome -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  <style>
    :root {
      --esg-primary: #1B4332;
      --esg-secondary: #2D6A4F;
      --esg-accent: #40916C;
      --esg-light: #F4F8F5;
      --esg-dark: #081C15;
      --esg-sidebar-width: 260px;
    }
    body {
      font-family: "微軟正黑體", "Microsoft JhengHei", "Segoe UI", sans-serif;
      background-color: #f7faf8;
      color: #212529;
      min-height: 100vh;
      overflow-x: hidden;
    }
    /* 側邊導航欄 */
    #sidebar {
      width: var(--esg-sidebar-width);
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      background: linear-gradient(180deg, #1B4332 0%, #081C15 100%);
      color: #fff;
      overflow-y: auto;
      z-index: 1000;
      transition: all 0.3s;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    #sidebar .brand {
      padding: 1.25rem 1rem;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    #sidebar .brand-title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #fff;
      letter-spacing: 0.5px;
    }
    #sidebar .nav-section {
      font-size: 0.72rem;
      font-weight: 700;
      color: #74C69D;
      text-transform: uppercase;
      padding: 1rem 1.25rem 0.35rem;
      letter-spacing: 1px;
    }
    #sidebar .nav-link {
      color: #D8F3DC;
      padding: 0.6rem 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 0.92rem;
      transition: all 0.2s;
      border-left: 3px solid transparent;
    }
    #sidebar .nav-link:hover, #sidebar .nav-link.active {
      color: #fff;
      background: rgba(255,255,255,0.12);
      border-left-color: #52B788;
    }
    #sidebar .nav-link i {
      width: 1.25rem;
      font-size: 1.05rem;
      text-align: center;
    }
    /* 主內容區域 */
    #main-content {
      margin-left: var(--esg-sidebar-width);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    /* 頂部導航欄 */
    .top-header {
      background: #fff;
      border-bottom: 1px solid #e2e8f0;
      padding: 0.75rem 1.5rem;
      position: sticky;
      top: 0;
      z-index: 999;
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .mobile-menu-toggle { display: none; }
    .sidebar-backdrop { display: none; }
    .user-pill {
      background: #E8F5E9;
      border: 1px solid #C8E6C9;
      border-radius: 50rem;
      padding: 0.35rem 0.85rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.88rem;
    }
    .role-badge {
      background: #1B4332;
      color: #fff;
      font-size: 0.75rem;
      padding: 0.2rem 0.5rem;
      border-radius: 4px;
    }
    .card-stat {
      border: none;
      border-radius: 0.75rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.04);
      transition: transform 0.2s;
    }
    .card-stat:hover {
      transform: translateY(-2px);
    }
    .table-custom th {
      background-color: #E9F3EC;
      color: #1B4332;
      font-weight: 600;
      border-bottom: 2px solid #C8E6C9;
    }
    .table-responsive {
      -webkit-overflow-scrolling: touch;
    }
    canvas { max-width: 100%; }
    @media (max-width: 991.98px) {
      #sidebar {
        transform: translateX(-100%);
        width: min(82vw, var(--esg-sidebar-width));
      }
      #sidebar.is-open { transform: translateX(0); }
      .sidebar-backdrop.is-visible {
        display: block;
        position: fixed;
        inset: 0;
        z-index: 999;
        background: rgba(8, 28, 21, 0.45);
      }
      #main-content { margin-left: 0; }
      .mobile-menu-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
      }
      .top-header { padding: 0.75rem 1rem; }
      .top-header h5 { font-size: 1rem; }
      main.p-4 { padding: 1.25rem !important; }
      footer .d-flex { flex-direction: column; gap: 0.35rem; }
    }
    @media (max-width: 575.98px) {
      .top-header { align-items: flex-start !important; gap: 0.75rem; }
      .top-header > .d-flex:last-child { gap: 0.4rem !important; }
      .top-header .user-pill { padding: 0.25rem 0.5rem; }
      .top-header .user-pill div, .top-header .dropdown { display: none; }
      main.p-4 { padding: 1rem !important; }
      .card-body { padding: 1rem; }
      .btn { min-height: 38px; }
      .table-responsive { margin-left: -1rem; margin-right: -1rem; padding-left: 1rem; padding-right: 1rem; }
      footer { padding-left: 1rem !important; padding-right: 1rem !important; }
    }
  </style>
</head>
<body>

  <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

  <!-- 側邊選單 -->
  <nav id="sidebar" aria-label="主要導覽">
    <div class="brand">
      <div style="background:#52B788; color:#081C15; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.2rem;">
        <i class="fa-solid fa-leaf"></i>
      </div>
      <div>
        <div class="brand-title">ESG 智慧管理</div>
        <small class="text-white-50" style="font-size:0.75rem;">SPEC-2026 V1.0</small>
      </div>
    </div>

    <div class="nav-section">戰情決策中心</div>
    <a href="<?= $baseUrl ?>/dashboard" class="nav-link"><i class="fa-solid fa-chart-pie"></i> 戰情總覽首頁</a>
    <a href="<?= $baseUrl ?>/dashboard/analytics" class="nav-link"><i class="fa-solid fa-chart-line"></i> 交叉維度分析</a>

    <div class="nav-section">環境管理 (E)</div>
    <a href="<?= $baseUrl ?>/ghg/records" class="nav-link"><i class="fa-solid fa-smog"></i> 溫室氣體碳盤查</a>
    <a href="<?= $baseUrl ?>/ghg/create" class="nav-link"><i class="fa-solid fa-file-circle-plus"></i> 活動數據錄入</a>
    <a href="<?= $baseUrl ?>/ghg/factors" class="nav-link"><i class="fa-solid fa-database"></i> 排放係數庫</a>
    <a href="<?= $baseUrl ?>/ghg/energy-water" class="nav-link"><i class="fa-solid fa-bolt"></i> 能水耗監控</a>
    <a href="<?= $baseUrl ?>/ghg/sbti" class="nav-link"><i class="fa-solid fa-bullseye"></i> SBTi 減碳路徑</a>

    <div class="nav-section">社會責任 (S)</div>
    <a href="<?= $baseUrl ?>/social" class="nav-link"><i class="fa-solid fa-users"></i> DEI 多元與職安</a>

    <div class="nav-section">公司治理 (G)</div>
    <a href="<?= $baseUrl ?>/governance" class="nav-link"><i class="fa-solid fa-landmark"></i> 董事會與 TCFD</a>

    <div class="nav-section">流程與供應鏈</div>
    <a href="<?= $baseUrl ?>/tasks" class="nav-link"><i class="fa-solid fa-list-check"></i> 填報審批工作台</a>
    <a href="<?= $baseUrl ?>/suppliers" class="nav-link"><i class="fa-solid fa-truck-fast"></i> 供應商 ESG 評鑑</a>

    <div class="nav-section">永續報告書</div>
    <a href="<?= $baseUrl ?>/reports/gri" class="nav-link"><i class="fa-solid fa-book-bookmark"></i> GRI 2021 索引</a>
    <a href="<?= $baseUrl ?>/reports/sasb" class="nav-link"><i class="fa-solid fa-industry"></i> SASB 會計指標</a>
    <a href="<?= $baseUrl ?>/reports/export" target="_blank" class="nav-link"><i class="fa-solid fa-file-export"></i> 確信底稿匯出</a>

    <?php if (Auth::isSuperAdmin()): ?>
    <div class="nav-section">系統管理中心</div>
    <a href="<?= $baseUrl ?>/admin/sites" class="nav-link"><i class="fa-solid fa-building"></i> 廠區與邊界</a>
    <a href="<?= $baseUrl ?>/admin/departments" class="nav-link"><i class="fa-solid fa-sitemap"></i> 組織部門樹</a>
    <a href="<?= $baseUrl ?>/admin/users" class="nav-link"><i class="fa-solid fa-user-gear"></i> 使用者帳號</a>
    <a href="<?= $baseUrl ?>/admin/roles" class="nav-link"><i class="fa-solid fa-shield-halved"></i> RBAC 權限</a>
    <a href="<?= $baseUrl ?>/admin/settings" class="nav-link"><i class="fa-solid fa-sliders"></i> 系統參數設定</a>
    <a href="<?= $baseUrl ?>/admin/ai-assistant" class="nav-link"><i class="fa-solid fa-robot"></i> AI 客服小編設定</a>
    <a href="<?= $baseUrl ?>/admin/audit-logs" class="nav-link"><i class="fa-solid fa-clipboard-list"></i> 系統稽核日誌</a>
    <?php endif; ?>

    <div class="nav-section">協助與支援</div>
    <a href="<?= $baseUrl ?>/manual" class="nav-link"><i class="fa-solid fa-circle-question"></i> 系統操作手冊</a>
    <div style="height: 24px;"></div>
  </nav>

  <!-- 主內容容器 -->
  <div id="main-content">
    <!-- 頂部 Header -->
    <header class="top-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-success btn-sm mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="開啟主選單">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h5 class="mb-0 text-dark fw-bold"><?= View::e($pageTitle ?? 'ESG 企業永續智慧管理系統') ?></h5>
      </div>

      <div class="d-flex align-items-center gap-3">
        <?php if (!empty($appConfig['allow_quick_login'])): ?>
        <!-- 僅限開發環境的角色快速切換 -->
        <div class="dropdown">
          <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-user-tag me-1"></i> 切換模擬角色
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.88rem;">
            <li><h6 class="dropdown-header">角色權限矩陣測試</h6></li>
            <li><a class="dropdown-item" href="<?= $baseUrl ?>/auth/quick-login?user=admin">系統管理員 (SysAdmin)</a></li>
            <li><a class="dropdown-item" href="<?= $baseUrl ?>/auth/quick-login?user=cso_lin">永續長 / 高階決策 (CSO)</a></li>
            <li><a class="dropdown-item" href="<?= $baseUrl ?>/auth/quick-login?user=esg_chen">永續專責小組 (ESG Mgr)</a></li>
            <li><a class="dropdown-item" href="<?= $baseUrl ?>/auth/quick-login?user=site_wang">廠區主管 (Dept Mgr)</a></li>
            <li><a class="dropdown-item" href="<?= $baseUrl ?>/auth/quick-login?user=op_zhang">基層填報人 (Operator)</a></li>
            <li><a class="dropdown-item" href="<?= $baseUrl ?>/auth/quick-login?user=auditor_liu">第三方查驗員 (Auditor)</a></li>
          </ul>
        </div>
        <?php endif; ?>

        <!-- 當前登入者資訊 -->
        <div class="user-pill">
          <i class="fa-solid fa-circle-user text-success fs-5"></i>
          <div>
            <span class="fw-bold text-dark"><?= View::e($user['real_name'] ?? 'Guest') ?></span>
            <span class="role-badge ms-1"><?= View::e($user['role_name'] ?? '訪客') ?></span>
          </div>
        </div>

        <!-- 登出 -->
        <a href="<?= $baseUrl ?>/auth/logout" class="btn btn-sm btn-outline-danger" title="安全登出">
          <i class="fa-solid fa-right-from-bracket"></i>
        </a>
      </div>
    </header>

    <!-- 頁面本體內容 -->
    <main class="p-4 flex-grow-1">
      <?= $content ?>
    </main>

    <!-- 頁尾 -->
    <footer class="bg-white border-top py-3 px-4 text-center text-muted" style="font-size:0.85rem;">
      <div class="d-flex justify-content-between align-items-center">
        <div><strong>ESG 企業永續智慧管理系統</strong> | SPEC-ESG-2026-V1.0</div>
        <div>遵循 ISO 14064-1:2018 / GHG Protocol / GRI Standards 2021 / TCFD / SASB</div>
      </div>
    </footer>
  </div>

  <?php if ($aiAssistantEnabled): ?>
  <!-- 受限範圍 AI 客服：所有問題均先由伺服器檢查服務範圍。 -->
  <div id="aiAssistant" class="ai-assistant" aria-live="polite">
    <button class="ai-launcher" id="aiLauncher" type="button" aria-expanded="false" aria-controls="aiPanel"><i class="fa-solid fa-robot"></i><span>AI 客服</span></button>
    <section class="ai-panel shadow" id="aiPanel" aria-label="ESG 系統 AI 客服" hidden>
      <header><div><strong><i class="fa-solid fa-robot me-1"></i>ESG 系統客服</strong><small>僅限系統操作與資料庫問題</small></div><button id="aiClose" type="button" class="btn-close btn-close-white" aria-label="關閉 AI 客服"></button></header>
      <div class="ai-messages" id="aiMessages"><div class="ai-message ai-bot">您好！我只協助 ESG 智慧管理系統的操作、功能、報表與資料庫問題。您可以問：「如何新增碳排活動數據？」</div></div>
      <form id="aiForm" class="ai-form"><input id="aiInput" maxlength="1200" autocomplete="off" placeholder="輸入系統相關問題…" aria-label="輸入系統相關問題"><button type="submit" class="btn btn-success" aria-label="送出問題"><i class="fa-solid fa-paper-plane"></i></button></form>
    </section>
  </div>
  <?php endif; ?>

  <!-- Bootstrap 5.3 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      const sidebar = document.getElementById('sidebar');
      const toggle = document.getElementById('mobileMenuToggle');
      const backdrop = document.getElementById('sidebarBackdrop');
      const closeMenu = () => {
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
        toggle.setAttribute('aria-expanded', 'false');
      };
      const openMenu = () => {
        sidebar.classList.add('is-open');
        backdrop.classList.add('is-visible');
        toggle.setAttribute('aria-expanded', 'true');
      };
      toggle.addEventListener('click', () => sidebar.classList.contains('is-open') ? closeMenu() : openMenu());
      backdrop.addEventListener('click', closeMenu);
      sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
        if (window.innerWidth < 992) closeMenu();
      }));
      window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) closeMenu();
      });
    })();
  </script>
  <?php if ($aiAssistantEnabled): ?>
  <style>
    .ai-assistant{position:fixed;right:22px;bottom:22px;z-index:1050;font-family:"微軟正黑體","Microsoft JhengHei",sans-serif}.ai-launcher{border:0;border-radius:50rem;background:#1b6b4c;color:#fff;padding:.7rem 1rem;box-shadow:0 5px 18px rgba(20,73,50,.3);font-weight:700}.ai-launcher i{margin-right:.45rem}.ai-panel{width:min(390px,calc(100vw - 32px));background:#fff;border-radius:1rem;overflow:hidden;margin-bottom:.7rem;border:1px solid #cce2d2}.ai-panel header{background:linear-gradient(120deg,#1b4332,#2d795a);color:#fff;padding:.85rem 1rem;display:flex;justify-content:space-between;align-items:center}.ai-panel header small{display:block;color:#d8f3dc;font-size:.72rem;margin-top:.1rem}.ai-messages{height:330px;overflow-y:auto;padding:.9rem;background:#f6faf7}.ai-message{max-width:88%;padding:.65rem .75rem;border-radius:.65rem;margin-bottom:.65rem;white-space:pre-wrap;font-size:.88rem;line-height:1.55}.ai-bot{background:#e5f3e9;color:#173f2e;border-bottom-left-radius:.15rem}.ai-user{background:#1b6b4c;color:#fff;margin-left:auto;border-bottom-right-radius:.15rem}.ai-error{background:#fff2f2;color:#922;border:1px solid #f0c4c4}.ai-form{display:flex;gap:.5rem;padding:.65rem;border-top:1px solid #dce9df}.ai-form input{min-width:0;flex:1;border:1px solid #c9dbcf;border-radius:.45rem;padding:.5rem .65rem;font-size:.88rem}.ai-form input:focus{outline:2px solid rgba(45,106,79,.2);border-color:#40916c}.ai-form button{width:42px}.ai-panel.is-disconnected .ai-form{display:none}@media(max-width:575px){.ai-assistant{right:12px;bottom:12px}.ai-messages{height:280px}}
  </style>
  <script>
    (() => {
      const panel=document.getElementById('aiPanel'), launcher=document.getElementById('aiLauncher'), close=document.getElementById('aiClose'), form=document.getElementById('aiForm'), input=document.getElementById('aiInput'), messages=document.getElementById('aiMessages');
      const append=(text,kind)=>{const box=document.createElement('div');box.className='ai-message '+kind;box.textContent=text;messages.appendChild(box);messages.scrollTop=messages.scrollHeight;};
      const toggle=open=>{panel.hidden=!open;launcher.setAttribute('aria-expanded',String(open));if(open)input.focus();};
      launcher.addEventListener('click',()=>toggle(panel.hidden)); close.addEventListener('click',()=>toggle(false));
      form.addEventListener('submit',async event=>{event.preventDefault();const message=input.value.trim();if(!message)return;append(message,'ai-user');input.value='';input.disabled=true;const submit=form.querySelector('button');submit.disabled=true;append('正在查詢系統說明…','ai-bot');const pending=messages.lastElementChild;
        try{const response=await fetch('<?= $baseUrl ?>/ai-assistant/chat',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':'<?= \App\Core\Csrf::getToken() ?>'},body:JSON.stringify({message})});const data=await response.json();pending.remove();append(data.answer||data.error||'客服暫時無法回應。',data.error?'ai-error':'ai-bot');if(data.disconnected){panel.classList.add('is-disconnected');launcher.disabled=true;launcher.innerHTML='<i class="fa-solid fa-lock"></i><span>客服已斷線</span>';}if(data.security_locked){const delay=Math.max(0,(data.locked_until*1000)-Date.now());panel.classList.add('is-disconnected');launcher.disabled=true;launcher.innerHTML='<i class="fa-solid fa-shield-halved"></i><span>資安鎖定中</span>';setTimeout(()=>{panel.classList.remove('is-disconnected');launcher.disabled=false;launcher.innerHTML='<i class="fa-solid fa-robot"></i><span>AI 客服</span>';append('3 分鐘安全鎖定已解除。請只提出本系統的操作、功能或資料庫問題。','ai-bot');},delay);}}
        catch(error){pending.remove();append('連線失敗，請稍後再試或聯絡系統管理員。','ai-error');}finally{input.disabled=false;submit.disabled=false;input.focus();}
      });
    })();
  </script>
  <?php endif; ?>
</body>
</html>
