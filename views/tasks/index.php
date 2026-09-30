<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
$user = Auth::user();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-list-check text-success me-2"></i>數據收集與工作流引擎 (Workflow Engine)
    </h4>
    <p class="text-muted mb-0 small">實作週期派案、填報提送、廠區覆核與決審多級簽核流轉 (M05-01 ~ M05-03)</p>
  </div>

  <div class="d-flex gap-2">
    <?php if (Auth::hasRole('ROLE_ADMIN', 'ROLE_ESG_MGR', 'ROLE_DEPT_MGR')): ?>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal">
        <i class="fa-solid fa-paper-plane me-1"></i> 派發填報任務工單
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- 篩選列 -->
<div class="mb-3 d-flex gap-2">
  <a href="<?= $baseUrl ?>/tasks" class="btn btn-sm <?= empty($status) ? 'btn-success' : 'btn-outline-secondary' ?>">全部工單</a>
  <a href="<?= $baseUrl ?>/tasks?status=PENDING" class="btn btn-sm <?= $status === 'PENDING' ? 'btn-secondary' : 'btn-outline-secondary' ?>">待填報</a>
  <a href="<?= $baseUrl ?>/tasks?status=UNDER_REVIEW" class="btn btn-sm <?= $status === 'UNDER_REVIEW' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>">待主管審核</a>
  <a href="<?= $baseUrl ?>/tasks?status=APPROVED" class="btn btn-sm <?= $status === 'APPROVED' ? 'btn-success' : 'btn-outline-success' ?>">已核定通過</a>
  <a href="<?= $baseUrl ?>/tasks?status=REJECTED" class="btn btn-sm <?= $status === 'REJECTED' ? 'btn-danger' : 'btn-outline-danger' ?>">已退回重填</a>
</div>

<!-- 工單清冊表格 -->
<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>工單號碼</th>
          <th>任務標題</th>
          <th>構面模組</th>
          <th>責任填報人</th>
          <th>待簽核主管</th>
          <th>截止日期</th>
          <th>流轉狀態</th>
          <th>審核備註 / 退回理由</th>
          <th>簽核時間</th>
          <th class="text-end">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tasks as $t): ?>
          <tr>
            <td><code>#TASK-<?= $t['task_id'] ?></code></td>
            <td class="fw-bold"><?= View::e($t['task_title']) ?></td>
            <td>
              <?php if ($t['module_type'] === 'ENV'): ?>
                <span class="badge bg-success">環境 (E)</span>
              <?php elseif ($t['module_type'] === 'SOC'): ?>
                <span class="badge bg-primary">社會 (S)</span>
              <?php elseif ($t['module_type'] === 'GOV'): ?>
                <span class="badge bg-info text-dark">治理 (G)</span>
              <?php else: ?>
                <span class="badge bg-secondary">供應鏈</span>
              <?php endif; ?>
            </td>
            <td><?= View::e($t['assigned_name']) ?></td>
            <td><?= View::e($t['approver_name']) ?></td>
            <td><?= View::e($t['due_date']) ?></td>
            <td>
              <?php if ($t['current_status'] === 'APPROVED'): ?>
                <span class="badge bg-success">已核定</span>
              <?php elseif ($t['current_status'] === 'UNDER_REVIEW'): ?>
                <span class="badge bg-warning text-dark">待主管審核</span>
              <?php elseif ($t['current_status'] === 'REJECTED'): ?>
                <span class="badge bg-danger">已駁回退回</span>
              <?php else: ?>
                <span class="badge bg-secondary">待填報</span>
              <?php endif; ?>
            </td>
            <td class="small text-muted text-truncate" style="max-width: 200px;">
              <?= View::e($t['approval_comment'] ?: '-') ?>
            </td>
            <td class="small text-muted">
              <?= View::e($t['signed_at'] ?: '-') ?>
            </td>
            <td class="text-end">
              <!-- 若為當前簽核人或管理員，且工單非已通過狀態，可執行審查 -->
              <?php if (($t['approver_id'] == $user['id'] || Auth::hasRole('ROLE_ADMIN', 'ROLE_ESG_MGR')) && $t['current_status'] !== 'APPROVED'): ?>
                <button class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size:0.78rem;" 
                        data-bs-toggle="modal" 
                        data-bs-target="#reviewModal" 
                        data-task-id="<?= $t['task_id'] ?>" 
                        data-task-title="<?= View::e($t['task_title']) ?>">
                  <i class="fa-solid fa-stamp me-1"></i> 審批
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 派發工單 Modal (M05-02) -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/tasks/create">
        <?= Csrf::input() ?>
        <div class="modal-header">
          <h6 class="modal-title fw-bold">派發定期 ESG 數據填報任務工單</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">任務標題 <span class="text-danger">*</span></label>
            <input type="text" name="task_title" class="form-control" placeholder="例如: 2026年Q2 台中廠溫室氣體活動量申報" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">所屬 ESG 構面 <span class="text-danger">*</span></label>
            <select name="module_type" class="form-select" required>
              <option value="ENV">環境管理 (E - 碳排、能源水耗)</option>
              <option value="SOC">社會責任 (S - 工傷、DEI、培訓)</option>
              <option value="GOV">公司治理 (G - 董事會、誠信)</option>
              <option value="SUPPLIER">供應鏈評鑑 (SAQ 問卷填答)</option>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">指派責任填報人 <span class="text-danger">*</span></label>
              <select name="assigned_to" class="form-select" required>
                <?php foreach ($users as $u): ?>
                  <option value="<?= $u['id'] ?>"><?= View::e($u['real_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">指派審批主管 <span class="text-danger">*</span></label>
              <select name="approver_id" class="form-select" required>
                <?php foreach ($users as $u): ?>
                  <option value="<?= $u['id'] ?>"><?= View::e($u['real_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">填報截止期限 <span class="text-danger">*</span></label>
            <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-paper-plane me-1"></i> 正式派發工單</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 審批簽核 Modal (M05-03) -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/tasks/review">
        <?= Csrf::input() ?>
        <input type="hidden" name="task_id" id="modalTaskId">

        <div class="modal-header">
          <h6 class="modal-title fw-bold">主管審查與簽核工作台</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small text-muted">審查任務：</label>
            <div class="fw-bold text-dark fs-6" id="modalTaskTitle"></div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">審查決策 <span class="text-danger">*</span></label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="action" id="actApprove" value="APPROVE" checked>
                <label class="form-check-label text-success fw-bold" for="actApprove">
                  <i class="fa-solid fa-check-circle"></i> 核准通過
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="action" id="actReject" value="REJECT">
                <label class="form-check-label text-danger fw-bold" for="actReject">
                  <i class="fa-solid fa-xmark-circle"></i> 駁回退回 (需補件)
                </label>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">審核意見 / 退回理由備註</label>
            <textarea name="approval_comment" class="form-control" rows="3" placeholder="單據查驗無誤，准予備查。若駁回請具體指明需補充之電費發票或數據疑點。"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-signature me-1"></i> 完成簽署</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const reviewModal = document.getElementById('reviewModal');
  if (reviewModal) {
    reviewModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const taskId = button.getAttribute('data-task-id');
      const taskTitle = button.getAttribute('data-task-title');

      document.getElementById('modalTaskId').value = taskId;
      document.getElementById('modalTaskTitle').innerText = taskTitle;
    });
  }
</script>
