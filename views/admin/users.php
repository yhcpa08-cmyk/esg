<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-user-gear text-success me-2"></i>使用者帳號管理 (User Accounts)
    </h4>
    <p class="text-muted mb-0 small">管理企業人員帳號生命週期、綁定廠區/部門與 RBAC 角色授權 (M01-03)</p>
  </div>

  <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="fa-solid fa-user-plus me-1"></i> 新增使用者帳號
  </button>
</div>

<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>登入帳號 / 員編</th>
          <th>真實姓名</th>
          <th>公務電子郵件</th>
          <th>指派角色</th>
          <th>所屬廠區 / 部門</th>
          <th>帳號狀態</th>
          <th>登入失敗次數</th>
          <th>最後登入時間</th>
          <th class="text-end">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><code>#<?= $u['id'] ?></code></td>
            <td class="fw-bold"><?= View::e($u['username']) ?></td>
            <td><?= View::e($u['real_name']) ?></td>
            <td><?= View::e($u['email']) ?></td>
            <td><span class="badge bg-secondary"><?= View::e($u['role_name']) ?></span></td>
            <td>
              <div><?= View::e($u['site_name']) ?></div>
              <small class="text-muted"><?= View::e($u['dept_name']) ?></small>
            </td>
            <td>
              <?php if ($u['status'] === 'ACTIVE'): ?>
                <span class="badge bg-success">正常啟用</span>
              <?php elseif ($u['status'] === 'LOCKED'): ?>
                <span class="badge bg-danger"><i class="fa-solid fa-lock"></i> 密碼錯誤鎖定</span>
              <?php else: ?>
                <span class="badge bg-secondary">已停用</span>
              <?php endif; ?>
            </td>
            <td><?= $u['failed_login_count'] ?> / 5</td>
            <td class="small text-muted"><?= $u['last_login_at'] ?: '從未登入' ?></td>
            <td class="text-end text-nowrap">
              <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 me-1" style="font-size:0.75rem;" 
                      onclick="openResetPwdModal('<?= $u['id'] ?>', '<?= View::e($u['real_name']) ?>', '<?= View::e($u['username']) ?>')">
                <i class="fa-solid fa-key"></i> 密碼
              </button>
              <form method="POST" action="<?= $baseUrl ?>/admin/toggle-user-status" class="d-inline">
                <?= Csrf::input() ?>
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <?php if ($u['status'] === 'ACTIVE'): ?>
                  <button type="submit" name="status" value="DISABLED" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size:0.75rem;">停用</button>
                <?php else: ?>
                  <button type="submit" name="status" value="ACTIVE" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size:0.75rem;">解鎖啟用</button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 重設密碼 Modal (M01-03) -->
<div class="modal fade" id="resetPwdModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/admin/reset-password">
        <?= Csrf::input() ?>
        <input type="hidden" name="user_id" id="resetPwdUserId" value="">
        <div class="modal-header">
          <h6 class="modal-title fw-bold"><i class="fa-solid fa-key text-warning me-2"></i>重設使用者密碼</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">
            即將為 <strong id="resetPwdUserName"></strong> (<code id="resetPwdUserAcc"></code>) 設定新密碼。
          </p>
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">新密碼 (強制長度 &ge; 8 碼) <span class="text-danger">*</span></label>
            <input type="password" name="new_password" class="form-control" placeholder="請輸入新密碼 (例如: Corp@2026)" required minlength="6">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-warning btn-sm"><i class="fa-solid fa-check me-1"></i> 確認變更密碼</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openResetPwdModal(userId, realName, username) {
  document.getElementById('resetPwdUserId').value = userId;
  document.getElementById('resetPwdUserName').textContent = realName;
  document.getElementById('resetPwdUserAcc').textContent = username;
  new bootstrap.Modal(document.getElementById('resetPwdModal')).show();
}
</script>

<!-- 新增帳號 Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/admin/users">
        <?= Csrf::input() ?>
        <div class="modal-header">
          <h6 class="modal-title fw-bold">新增系統使用者帳號</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">登入帳號 / 員編 <span class="text-danger">*</span></label>
              <input type="text" name="username" class="form-control" placeholder="如 user01" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">真實姓名 <span class="text-danger">*</span></label>
              <input type="text" name="real_name" class="form-control" placeholder="如 李永續" required>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">電子郵件 <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" placeholder="user@corp.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">預設密碼 <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" value="admin123" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">指派角色 <span class="text-danger">*</span></label>
            <select name="role_id" class="form-select" required>
              <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>"><?= View::e($r['role_name']) ?> (<?= View::e($r['role_code']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">所屬廠區 <span class="text-danger">*</span></label>
              <select name="site_id" class="form-select" required>
                <?php foreach ($sites as $s): ?>
                  <option value="<?= $s['id'] ?>"><?= View::e($s['site_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">所屬部門 <span class="text-danger">*</span></label>
              <select name="dept_id" class="form-select" required>
                <?php foreach ($departments as $d): ?>
                  <option value="<?= $d['id'] ?>"><?= View::e($d['dept_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i> 建立帳號</button>
        </div>
      </form>
    </div>
  </div>
</div>
