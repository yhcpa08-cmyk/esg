<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-sitemap text-success me-2"></i>組織部門架構管理 (Organization Departments)
    </h4>
    <p class="text-muted mb-0 small">支援多層級樹狀組織設定 (Parent-Child) 與責任部門審核關係 (M01-01)</p>
  </div>

  <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addDeptModal">
    <i class="fa-solid fa-plus me-1"></i> 新增組織部門
  </button>
</div>

<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>部門 ID</th>
          <th>隸屬廠區</th>
          <th>部門名稱</th>
          <th>上級部門</th>
          <th>建立時間</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($departments as $d): ?>
          <tr>
            <td><code>#<?= $d['id'] ?></code></td>
            <td><span class="badge bg-light text-dark border"><?= View::e($d['site_name']) ?></span></td>
            <td class="fw-bold text-success"><?= View::e($d['dept_name']) ?></td>
            <td><?= View::e($d['parent_name'] ?: '（頂層部門）') ?></td>
            <td class="small text-muted"><?= $d['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 新增部門 Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/admin/departments">
        <?= Csrf::input() ?>
        <div class="modal-header">
          <h6 class="modal-title fw-bold">新增組織部門節點</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">所屬廠區 <span class="text-danger">*</span></label>
            <select name="site_id" class="form-select" required>
              <?php foreach ($sites as $s): ?>
                <option value="<?= $s['id'] ?>"><?= View::e($s['site_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">部門名稱 <span class="text-danger">*</span></label>
            <input type="text" name="dept_name" class="form-control" placeholder="例如: 智能製造部" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">上級部門 (選填)</label>
            <select name="parent_dept_id" class="form-select">
              <option value="">-- 無（作為一級頂層部門）--</option>
              <?php foreach ($departments as $pd): ?>
                <option value="<?= $pd['id'] ?>"><?= View::e($pd['dept_name']) ?> (<?= View::e($pd['site_name']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i> 建立部門</button>
        </div>
      </form>
    </div>
  </div>
</div>
