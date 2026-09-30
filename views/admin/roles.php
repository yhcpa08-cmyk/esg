<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-shield-halved text-success me-2"></i>RBAC 角色與二維權限矩陣 (Role & Permissions Matrix)
    </h4>
    <p class="text-muted mb-0 small">功能模組權限 × 資料管轄邊界範圍配置 (M01-02)</p>
  </div>
</div>

<div class="card bg-white shadow-sm border-0 mb-4">
  <div class="card-header bg-white py-3">
    <h6 class="fw-bold mb-0 text-dark">核心 6 大角色定義清冊</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>角色代號</th>
          <th>角色名稱</th>
          <th>資料管轄範圍 (Data Scope)</th>
          <th>職責與權限說明</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($roles as $r): ?>
          <tr>
            <td><code><?= View::e($r['role_code']) ?></code></td>
            <td class="fw-bold"><?= View::e($r['role_name']) ?></td>
            <td>
              <?php if ($r['data_scope'] === 'ALL'): ?>
                <span class="badge bg-success">全集團跨廠區 (ALL)</span>
              <?php elseif ($r['data_scope'] === 'SITE'): ?>
                <span class="badge bg-primary">單一廠區維度 (SITE)</span>
              <?php else: ?>
                <span class="badge bg-secondary">單一部門維度 (DEPT)</span>
              <?php endif; ?>
            </td>
            <td><?= View::e($r['description']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card bg-white shadow-sm border-0 p-4">
  <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-table-cells me-2"></i>二維權限控制對照矩陣</h6>
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle" style="font-size: 0.82rem;">
      <thead class="table-light">
        <tr>
          <th>功能模組 / 使用者角色</th>
          <th>系統管理員 (Admin)</th>
          <th>永續長 (CSO)</th>
          <th>永續專責小組 (Manager)</th>
          <th>廠區主管 (Dept Mgr)</th>
          <th>基層填報員 (Operator)</th>
          <th>第三方查驗員 (Auditor)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-start fw-bold">戰情決策首頁 (M07)</td>
          <td><span class="badge bg-success">全權</span></td>
          <td><span class="badge bg-success">全覽</span></td>
          <td><span class="badge bg-success">全覽</span></td>
          <td><span class="badge bg-primary">所屬廠區</span></td>
          <td><span class="badge bg-secondary">唯讀</span></td>
          <td><span class="badge bg-secondary">審計唯讀</span></td>
        </tr>
        <tr>
          <td class="text-start fw-bold">溫室氣體活動填報 (M02)</td>
          <td><span class="badge bg-success">可編輯</span></td>
          <td><span class="badge bg-light text-muted border">唯讀</span></td>
          <td><span class="badge bg-success">全權管理</span></td>
          <td><span class="badge bg-primary">覆核審批</span></td>
          <td><span class="badge bg-success">新增 / 編輯</span></td>
          <td><span class="badge bg-secondary">審計唯讀</span></td>
        </tr>
        <tr>
          <td class="text-start fw-bold">排放係數庫維護 (M02-01)</td>
          <td><span class="badge bg-success">可編輯</span></td>
          <td><span class="badge bg-light text-muted border">唯讀</span></td>
          <td><span class="badge bg-success">全權管理</span></td>
          <td><span class="badge bg-light text-muted border">唯讀</span></td>
          <td><span class="badge bg-light text-muted border">唯讀</span></td>
          <td><span class="badge bg-secondary">審計唯讀</span></td>
        </tr>
        <tr>
          <td class="text-start fw-bold">工作流多級審批 (M05)</td>
          <td><span class="badge bg-success">決審</span></td>
          <td><span class="badge bg-success">決審</span></td>
          <td><span class="badge bg-success">派發 / 決審</span></td>
          <td><span class="badge bg-primary">初審覆核</span></td>
          <td><span class="badge bg-secondary">提送</span></td>
          <td><span class="badge bg-secondary">審計調閱</span></td>
        </tr>
        <tr>
          <td class="text-start fw-bold">供應商 ESG 評鑑 (M08)</td>
          <td><span class="badge bg-success">全權</span></td>
          <td><span class="badge bg-light text-muted border">查閱</span></td>
          <td><span class="badge bg-success">派發問卷</span></td>
          <td><span class="badge bg-primary">CAPA 追蹤</span></td>
          <td><span class="badge bg-light text-muted border">無權限</span></td>
          <td><span class="badge bg-secondary">審計調閱</span></td>
        </tr>
        <tr>
          <td class="text-start fw-bold">GRI / SASB 報告書產製 (M06)</td>
          <td><span class="badge bg-success">匯出</span></td>
          <td><span class="badge bg-success">簽核發布</span></td>
          <td><span class="badge bg-success">編製匯出</span></td>
          <td><span class="badge bg-light text-muted border">無權限</span></td>
          <td><span class="badge bg-light text-muted border">無權限</span></td>
          <td><span class="badge bg-success">確信下載</span></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
