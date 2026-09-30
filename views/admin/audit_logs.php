<?php
use App\Core\View;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-clipboard-list text-success me-2"></i>系統全域稽核日誌 (Audit Trail Logs)
    </h4>
    <p class="text-muted mb-0 small">具備不可竄改之全流程軌跡留痕，精確記錄操作者、時間、IP 與變更前後數值 (Diff) (M01-05)</p>
  </div>
</div>

<div class="card bg-white shadow-sm border-0 mb-4 p-3">
  <form method="GET" action="<?= $baseUrl ?>/admin/audit-logs" class="row g-2 align-items-center">
    <div class="col-md-4">
      <select name="module" class="form-select form-select-sm">
        <option value="">全部模組</option>
        <option value="AUTH" <?= $module === 'AUTH' ? 'selected' : '' ?>>身分驗證 (AUTH)</option>
        <option value="GHG_RECORD" <?= $module === 'GHG_RECORD' ? 'selected' : '' ?>>碳盤查填報 (GHG_RECORD)</option>
        <option value="GHG_FACTOR" <?= $module === 'GHG_FACTOR' ? 'selected' : '' ?>>係數管理 (GHG_FACTOR)</option>
        <option value="TASK_WORKFLOW" <?= $module === 'TASK_WORKFLOW' ? 'selected' : '' ?>>工作流簽核 (TASK_WORKFLOW)</option>
        <option value="SUPPLIER" <?= $module === 'SUPPLIER' ? 'selected' : '' ?>>供應鏈評鑑 (SUPPLIER)</option>
        <option value="REPORT" <?= $module === 'REPORT' ? 'selected' : '' ?>>報表匯出 (REPORT)</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-outline-success btn-sm w-100">
        <i class="fa-solid fa-filter me-1"></i> 篩選
      </button>
    </div>
  </form>
</div>

<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
      <thead class="table-light">
        <tr>
          <th>日誌 ID</th>
          <th>發生時間</th>
          <th>操作者</th>
          <th>客戶端 IP</th>
          <th>功能模組</th>
          <th>動作類型</th>
          <th>資料列 ID</th>
          <th>異動前數值 (Old)</th>
          <th>異動後數值 (New)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $l): ?>
          <tr>
            <td><code>#<?= $l['id'] ?></code></td>
            <td><?= $l['created_at'] ?></td>
            <td class="fw-bold">
              <?= View::e($l['real_name'] ?: ($l['username'] ?: '外部訪客 / 系統')) ?>
            </td>
            <td><code><?= View::e($l['ip_address']) ?></code></td>
            <td><span class="badge bg-light text-dark border"><?= View::e($l['action_module']) ?></span></td>
            <td>
              <?php if ($l['action_type'] === 'INSERT'): ?>
                <span class="badge bg-success">新增 INSERT</span>
              <?php elseif ($l['action_type'] === 'UPDATE'): ?>
                <span class="badge bg-primary">更新 UPDATE</span>
              <?php elseif ($l['action_type'] === 'DELETE'): ?>
                <span class="badge bg-danger">刪除 DELETE</span>
              <?php elseif ($l['action_type'] === 'LOGIN'): ?>
                <span class="badge bg-info text-dark">登入 LOGIN</span>
              <?php else: ?>
                <span class="badge bg-secondary"><?= View::e($l['action_type']) ?></span>
              <?php endif; ?>
            </td>
            <td><?= $l['record_id'] ? '#' . $l['record_id'] : '-' ?></td>
            <td style="max-width: 200px;">
              <?php if (!empty($l['old_values'])): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 text-truncate w-100 font-monospace small" style="font-size:0.75rem;" 
                        onclick="showDiffModal(<?= htmlspecialchars(json_encode($l['old_values'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>, <?= htmlspecialchars(json_encode($l['new_values'] ?: '{}', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>, '<?= $l['id'] ?>', '<?= View::e($l['action_module']) ?>')">
                  <i class="fa-solid fa-code-compare me-1"></i><?= View::e($l['old_values']) ?>
                </button>
              <?php else: ?>
                <span class="text-muted small">-</span>
              <?php endif; ?>
            </td>
            <td style="max-width: 250px;">
              <?php if (!empty($l['new_values'])): ?>
                <button type="button" class="btn btn-sm btn-outline-success py-0 px-1 text-truncate w-100 font-monospace small" style="font-size:0.75rem;" 
                        onclick="showDiffModal(<?= htmlspecialchars(json_encode($l['old_values'] ?: '{}', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>, <?= htmlspecialchars(json_encode($l['new_values'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>, '<?= $l['id'] ?>', '<?= View::e($l['action_module']) ?>')">
                  <i class="fa-solid fa-file-lines me-1"></i><?= View::e($l['new_values']) ?>
                </button>
              <?php else: ?>
                <span class="text-muted small">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 異動前後對比 Diff Modal (M01-05) -->
<div class="modal fade" id="diffModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-bold" id="diffModalTitle"><i class="fa-solid fa-code-compare text-success me-2"></i>資料異動前後對比 (Audit Diff)</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <h6 class="fw-bold text-danger border-bottom pb-2"><i class="fa-solid fa-history me-1"></i> 異動前原始數值 (Old Values)</h6>
            <pre class="bg-light p-3 border rounded text-danger small" id="diffOldContent" style="max-height: 350px; overflow-y:auto;"></pre>
          </div>
          <div class="col-md-6">
            <h6 class="fw-bold text-success border-bottom pb-2"><i class="fa-solid fa-sparkles me-1"></i> 異動後新數值 (New Values)</h6>
            <pre class="bg-light p-3 border rounded text-success small" id="diffNewContent" style="max-height: 350px; overflow-y:auto;"></pre>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">關閉</button>
      </div>
    </div>
  </div>
</div>

<script>
function showDiffModal(oldStr, newStr, logId, moduleName) {
  document.getElementById('diffModalTitle').innerHTML = '<i class="fa-solid fa-code-compare text-success me-2"></i>異動日誌 #' + logId + ' (' + moduleName + ') 數值對比';
  
  try {
    const oldObj = (typeof oldStr === 'string' && oldStr.startsWith('{')) ? JSON.parse(oldStr) : oldStr;
    document.getElementById('diffOldContent').textContent = JSON.stringify(oldObj, null, 2);
  } catch (e) {
    document.getElementById('diffOldContent').textContent = oldStr || '(無)';
  }

  try {
    const newObj = (typeof newStr === 'string' && newStr.startsWith('{')) ? JSON.parse(newStr) : newStr;
    document.getElementById('diffNewContent').textContent = JSON.stringify(newObj, null, 2);
  } catch (e) {
    document.getElementById('diffNewContent').textContent = newStr || '(無)';
  }

  const modal = new bootstrap.Modal(document.getElementById('diffModal'));
  modal.show();
}
</script>
