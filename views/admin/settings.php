<?php use App\Core\Auth; use App\Core\View; ?>
<div class="container-fluid px-0">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="fa-solid fa-sliders text-success me-2"></i>系統參數設定</h4>
      <p class="text-muted mb-0 small">集中調整 ESG 作業與安全參數；僅 Super Admin（系統最高管理者）可修改。</p>
    </div>
    <span class="badge bg-dark">Super Admin 專用</span>
  </div>

  <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
    <div class="alert alert-success">設定已儲存並開始套用。</div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= View::e($error) ?></div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <form method="post" action="<?= Auth::baseUrl() ?>/admin/settings">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>可調整項目</th><th style="width: 180px">目前設定</th><th>用途與建議</th></tr></thead>
            <tbody>
              <?php foreach ($definitions as $key => $definition): ?>
                <tr>
                  <td class="fw-semibold"><?= View::e($definition['label']) ?></td>
                  <td>
                    <div class="input-group">
                      <input class="form-control" type="number" name="<?= View::e($key) ?>" min="<?= (int)$definition['min'] ?>" max="<?= (int)$definition['max'] ?>" value="<?= (int)$settings[$key] ?>" required>
                      <span class="input-group-text"><?= View::e($definition['unit']) ?></span>
                    </div>
                    <small class="text-muted">範圍 <?= (int)$definition['min'] ?>–<?= (int)$definition['max'] ?> <?= View::e($definition['unit']) ?></small>
                  </td>
                  <td class="text-muted small"><?= View::e($definition['description']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-success" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>儲存設定</button>
        </div>
      </form>
    </div>
  </div>
  <div class="alert alert-info mt-3 small mb-0"><i class="fa-solid fa-circle-info me-1"></i>每次變更都會記錄在系統稽核日誌。檔案上限也受伺服器 PHP 上傳限制影響。</div>
</div>
