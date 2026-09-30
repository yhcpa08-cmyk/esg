<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-building text-success me-2"></i>營運據點與盤查邊界管理 (Site & Boundary)
    </h4>
    <p class="text-muted mb-0 small">依據 ISO 14064-1:2018 設定集團營運邊界 (Operational Boundary) (M01-01)</p>
  </div>

  <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSiteModal">
    <i class="fa-solid fa-plus me-1"></i> 新增營運廠區
  </button>
</div>

<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>編號</th>
          <th>廠區代碼</th>
          <th>廠區名稱</th>
          <th>所在國家</th>
          <th>納入 ISO 14064-1 盤查邊界</th>
          <th>建立時間</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sites as $s): ?>
          <tr>
            <td><code>#<?= $s['id'] ?></code></td>
            <td><span class="badge bg-secondary"><?= View::e($s['site_code']) ?></span></td>
            <td class="fw-bold"><?= View::e($s['site_name']) ?></td>
            <td><?= View::e($s['country']) ?></td>
            <td>
              <?php if ($s['is_in_boundary']): ?>
                <span class="badge bg-success"><i class="fa-solid fa-check"></i> 納入邊界</span>
              <?php else: ?>
                <span class="badge bg-light text-muted border">排除</span>
              <?php endif; ?>
            </td>
            <td class="small text-muted"><?= $s['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 新增廠區 Modal -->
<div class="modal fade" id="addSiteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/admin/sites">
        <?= Csrf::input() ?>
        <div class="modal-header">
          <h6 class="modal-title fw-bold">新增營運據點廠區</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">廠區代號 (如 SITE-04) <span class="text-danger">*</span></label>
            <input type="text" name="site_code" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">廠區名稱 <span class="text-danger">*</span></label>
            <input type="text" name="site_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">所在國家</label>
            <input type="text" name="country" class="form-control" value="Taiwan">
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="is_in_boundary" id="inBoundaryCheck" checked>
            <label class="form-check-label small fw-bold" for="inBoundaryCheck">納入 ISO 14064-1 組織盤查實質邊界</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i> 儲存據點</button>
        </div>
      </form>
    </div>
  </div>
</div>
