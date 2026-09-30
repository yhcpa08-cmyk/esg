<?php use App\Core\Auth; use App\Core\Csrf; use App\Core\View; ?>
<div class="container-fluid px-0">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="fw-bold mb-1"><i class="fa-solid fa-robot text-success me-2"></i>AI 客服小編設定</h4><p class="text-muted mb-0 small">僅回答本系統操作、功能與受控資料庫資訊；非系統問題累計 3 次即中斷該使用者的客服連線。</p></div><span class="badge bg-dark">Super Admin 專用</span></div>
  <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'saved'): ?><div class="alert alert-success">AI 客服設定已儲存。API 金鑰已加密保存，畫面不會再次顯示完整內容。</div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= View::e($error) ?></div><?php endif; ?>
  <div class="row g-4"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-body p-4"><form method="post" action="<?= Auth::baseUrl() ?>/admin/ai-assistant"><?= Csrf::input() ?>
    <div class="mb-3"><label class="form-label fw-semibold" for="aiModel">OpenAI 模型</label><select class="form-select" id="aiModel" name="model"><?php foreach ($models as $model): ?><option value="<?= View::e($model) ?>" <?= $settings['model'] === $model ? 'selected' : '' ?>><?= View::e($model) ?><?= $model === 'gpt-6-luna' ? '（建議：高效率客服）' : '' ?></option><?php endforeach; ?></select><div class="form-text">使用 Responses API。GPT-6 系列為 2026 年 5 月後的可用模型；客服預設選用成本與延遲較適合重複問答的 GPT-6 Luna。</div></div>
    <div class="mb-3"><label class="form-label fw-semibold" for="apiKey">OpenAI API 金鑰</label><input class="form-control font-monospace" id="apiKey" name="api_key" type="password" autocomplete="new-password" placeholder="<?= $settings['has_api_key'] ? '已安全保存；留白即保留原金鑰' : 'sk-...' ?>"><div class="form-text">只在伺服器端使用；以 AES-256-GCM 加密後存入資料庫，解密金鑰保存在伺服器 storage 目錄，不會回傳至瀏覽器。</div></div>
    <div class="form-check form-switch mb-4"><input class="form-check-input" type="checkbox" role="switch" id="aiEnabled" name="is_enabled" <?= $settings['is_enabled'] ? 'checked' : '' ?>><label class="form-check-label" for="aiEnabled">啟用右下角 AI 客服小編</label></div>
    <button class="btn btn-success" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>儲存 AI 設定</button></form></div></div></div>
    <div class="col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h5 class="fw-bold">連線測試</h5><p class="small text-muted">測試會以目前已儲存的 API 金鑰向 OpenAI Responses API 發送固定測試文字，不會傳送系統資料。</p><button id="testAiConnection" class="btn btn-outline-success w-100" type="button"><i class="fa-solid fa-plug-circle-check me-1"></i>測試 OpenAI 連線</button><div id="aiTestResult" class="small mt-3" role="status"></div><hr><h6 class="fw-bold">服務範圍防護</h6><ul class="small ps-3 mb-0"><li>前端送出前由伺服器檢查系統關鍵字。</li><li>範圍外問題不會送至 OpenAI。</li><li>第 3 次範圍外問題立即斷線。</li><li>僅傳送系統操作說明、資料表描述與彙總資料。</li></ul></div></div></div></div>
</div>
<script>
document.getElementById('testAiConnection').addEventListener('click', async function () {
  const button = this, result = document.getElementById('aiTestResult'); button.disabled = true; result.className = 'small mt-3 text-muted'; result.textContent = '正在測試連線…';
  try { const response = await fetch('<?= Auth::baseUrl() ?>/admin/ai-assistant/test', {method:'POST', headers:{'X-CSRF-Token':'<?= Csrf::getToken() ?>'}}); const data = await response.json(); result.className = 'small mt-3 ' + (data.success ? 'text-success' : 'text-danger'); result.textContent = data.message; } catch (e) { result.className = 'small mt-3 text-danger'; result.textContent = '測試失敗：無法解析伺服器回應。'; } finally { button.disabled = false; }
});
</script>
