<?php
use App\Core\View;
use App\Core\Csrf;
use App\Core\Auth;
$baseUrl = Auth::baseUrl();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
  <div>
    <h4 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-truck-fast text-success me-2"></i>供應鏈 ESG 評鑑與風險管理 (Supply Chain ESG)
    </h4>
    <p class="text-muted mb-0 small">實作免登入專屬安全 Token 問卷發放、加權評估燈號 (A/B/C) 與 CAPA 缺失改善追蹤 (M08-01 ~ M08-03)</p>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
      <i class="fa-solid fa-plus me-1"></i> 新增受評供應商與產生 Token
    </button>
  </div>
</div>

<!-- 篩選列 -->
<div class="mb-3 d-flex gap-2">
  <a href="<?= $baseUrl ?>/suppliers" class="btn btn-sm <?= empty($risk) ? 'btn-success' : 'btn-outline-secondary' ?>">全部供應商</a>
  <a href="<?= $baseUrl ?>/suppliers?risk=LOW_A" class="btn btn-sm <?= $risk === 'LOW_A' ? 'btn-success' : 'btn-outline-success' ?>"><i class="fa-solid fa-circle text-success"></i> A 級 (綠燈 優良)</a>
  <a href="<?= $baseUrl ?>/suppliers?risk=MED_B" class="btn btn-sm <?= $risk === 'MED_B' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>"><i class="fa-solid fa-circle text-warning"></i> B 級 (黃燈 觀察)</a>
  <a href="<?= $baseUrl ?>/suppliers?risk=HIGH_C" class="btn btn-sm <?= $risk === 'HIGH_C' ? 'btn-danger' : 'btn-outline-danger' ?>"><i class="fa-solid fa-circle text-danger"></i> C 級 (紅燈 高風險)</a>
</div>

<!-- 供應商清冊表格 -->
<div class="card bg-white shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
      <thead class="table-light">
        <tr>
          <th>統編 / 代碼</th>
          <th>供應商名稱</th>
          <th>評鑑年度</th>
          <th class="text-center">環境 (E 40%)</th>
          <th class="text-center">社會 (S 30%)</th>
          <th class="text-center">治理 (G 30%)</th>
          <th class="text-center">加權總分</th>
          <th class="text-center">風險等級</th>
          <th>問卷狀態</th>
          <th>CAPA 改善狀態</th>
          <th>免登入填答連結</th>
          <th class="text-end">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($suppliers as $s): ?>
          <tr>
            <td><code><?= View::e($s['supplier_code']) ?></code></td>
            <td class="fw-bold"><?= View::e($s['supplier_name']) ?></td>
            <td><?= $s['eval_year'] ?></td>
            <td class="text-center"><?= $s['env_score'] ?></td>
            <td class="text-center"><?= $s['soc_score'] ?></td>
            <td class="text-center"><?= $s['gov_score'] ?></td>
            <td class="text-center fw-bold fs-6 <?= $s['total_score'] >= 80 ? 'text-success' : ($s['total_score'] >= 60 ? 'text-warning' : 'text-danger') ?>">
              <?= $s['total_score'] ?>
            </td>
            <td class="text-center">
              <?php if ($s['risk_level'] === 'LOW_A'): ?>
                <span class="badge bg-success">A 級 (綠燈)</span>
              <?php elseif ($s['risk_level'] === 'MED_B'): ?>
                <span class="badge bg-warning text-dark">B 級 (黃燈)</span>
              <?php else: ?>
                <span class="badge bg-danger">C 級 (紅燈)</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($s['survey_status'] === 'SUBMITTED'): ?>
                <span class="badge bg-success"><i class="fa-solid fa-check"></i> 已完成填答</span>
              <?php else: ?>
                <span class="badge bg-secondary"><i class="fa-regular fa-clock"></i> 待填答</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($s['capa_status'] === 'REQUIRED'): ?>
                <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation"></i> 需改善 (CAPA)</span>
              <?php elseif ($s['capa_status'] === 'RESOLVED'): ?>
                <span class="badge bg-info text-dark">改善已複核結案</span>
              <?php else: ?>
                <span class="text-muted small">正常無缺失</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= $baseUrl ?>/suppliers/public-survey?token=<?= View::e($s['survey_token']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:0.75rem;" title="開啟線上問卷">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> 線上填答
              </a>
            </td>
            <td class="text-end">
              <button class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size:0.75rem;"
                      data-bs-toggle="modal" 
                      data-bs-target="#capaModal"
                      data-supplier-id="<?= $s['id'] ?>"
                      data-supplier-name="<?= View::e($s['supplier_name']) ?>"
                      data-capa-status="<?= $s['capa_status'] ?>"
                      data-capa-comment="<?= View::e($s['capa_comment']) ?>">
                <i class="fa-solid fa-file-pen me-1"></i> CAPA
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 新增受評供應商 Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/suppliers/create">
        <?= Csrf::input() ?>
        <div class="modal-header">
          <h6 class="modal-title fw-bold">新增受評供應商 (自動產生免登入問卷憑證 Token)</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">統一編號 / 代碼 <span class="text-danger">*</span></label>
            <input type="text" name="supplier_code" class="form-control" placeholder="例如: 84930192" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">供應商公司全名 <span class="text-danger">*</span></label>
            <input type="text" name="supplier_name" class="form-control" placeholder="例如: 茂林綠能材料股份有限公司" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">評鑑年度 <span class="text-danger">*</span></label>
            <input type="number" name="eval_year" class="form-control" value="<?= date('Y') ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-key me-1"></i> 建立並派發安全憑證</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CAPA 工單維護 Modal (M08-03) -->
<div class="modal fade" id="capaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/suppliers/capa-update">
        <?= Csrf::input() ?>
        <input type="hidden" name="id" id="modalSupplierId">
        <div class="modal-header">
          <h6 class="modal-title fw-bold">供應商缺失改善工單 (CAPA Management)</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small text-muted">供應商名稱：</label>
            <div class="fw-bold text-dark fs-6" id="modalSupplierName"></div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">CAPA 改善狀態</label>
            <select name="capa_status" id="modalCapaStatus" class="form-select">
              <option value="NONE">正常 (無重大缺失)</option>
              <option value="REQUIRED">需改善 (已開立改善工單)</option>
              <option value="RESOLVED">已完成改善並複核結案</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">缺失改善說明與複核紀錄</label>
            <textarea name="capa_comment" id="modalCapaComment" class="form-control" rows="3" placeholder="註記缺失項目（如未具備ISO 14001驗證、未建立人權政策等）及供應商提出之對策。"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i> 更新 CAPA 狀態</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const capaModal = document.getElementById('capaModal');
  if (capaModal) {
    capaModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      document.getElementById('modalSupplierId').value = button.getAttribute('data-supplier-id');
      document.getElementById('modalSupplierName').innerText = button.getAttribute('data-supplier-name');
      document.getElementById('modalCapaStatus').value = button.getAttribute('data-capa-status');
      document.getElementById('modalCapaComment').value = button.getAttribute('data-capa-comment') || '';
    });
  }
</script>
