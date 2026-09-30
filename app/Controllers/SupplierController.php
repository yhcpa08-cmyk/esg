<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Core\AuditLogger;
use App\Services\SupplierService;

class SupplierController
{
    public function index(): void
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }

        $risk = $_GET['risk'] ?? '';
        $sql = "SELECT * FROM esg_supplier_assessments WHERE 1=1";
        $params = [];
        if ($risk) {
            $sql .= " AND risk_level = ?";
            $params[] = $risk;
        }
        $sql .= " ORDER BY id DESC";

        $suppliers = Database::query($sql, $params);

        View::render('suppliers/index', [
            'suppliers' => $suppliers,
            'risk'      => $risk
        ]);
    }

    public function create(): void
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }
        Auth::requireRoles('ROLE_ESG_MGR');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['supplier_code'] ?? '');
            $name = trim($_POST['supplier_name'] ?? '');
            $year = (int)($_POST['eval_year'] ?? date('Y'));
            $token = SupplierService::generateToken($code);
            if ($code === '' || $name === '' || $year < 2000 || $year > 2100) {
                http_response_code(422);
                die('422 Unprocessable Entity: 供應商資料不正確。');
            }

            $id = Database::insert(
                "INSERT INTO esg_supplier_assessments 
                 (supplier_code, supplier_name, eval_year, survey_token, survey_status, risk_level, capa_status)
                 VALUES (?, ?, ?, ?, 'PENDING', 'LOW_A', 'NONE')",
                [$code, $name, $year, $token]
            );

            AuditLogger::log('SUPPLIER', 'INSERT', $id, null, ['code' => $code, 'name' => $name]);

            header('Location: ' . Auth::baseUrl() . '/suppliers?msg=created');
            exit;
        }
    }

    /**
     * 供應商線上免登入填答問卷入口 (M08-01 規格書專屬安全憑證 Token)
     */
    public function publicSurvey(): void
    {
        $token = $_GET['token'] ?? '';
        $supplier = Database::fetch("SELECT * FROM esg_supplier_assessments WHERE survey_token = ?", [$token]);

        if (!$supplier) {
            View::render('suppliers/public_invalid', ['message' => '問卷填答連結無效或已過期，請洽詢採購窗口。'], null);
            return;
        }

        $submitted = false;
        $evalResult = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!\App\Core\Csrf::validate($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                die('403 Forbidden: 問卷驗證已失效，請重新開啟連結後再試。');
            }
            if ($supplier['survey_status'] === 'SUBMITTED') {
                http_response_code(409);
                die('409 Conflict: 此問卷已提交，無法重複填答。');
            }
            // 從問卷題目計算構面得分 (滿分各100分)
            $envQ1 = (float)($_POST['env_iso14001'] ?? 0); // 30
            $envQ2 = (float)($_POST['env_ghg_inv'] ?? 0);  // 40
            $envQ3 = (float)($_POST['env_renew'] ?? 0);    // 30
            $envScore = min(100.0, $envQ1 + $envQ2 + $envQ3);

            $socQ1 = (float)($_POST['soc_iso45001'] ?? 0); // 35
            $socQ2 = (float)($_POST['soc_labor'] ?? 0);    // 35
            $socQ3 = (float)($_POST['soc_training'] ?? 0); // 30
            $socScore = min(100.0, $socQ1 + $socQ2 + $socQ3);

            $govQ1 = (float)($_POST['gov_ethics'] ?? 0);   // 40
            $govQ2 = (float)($_POST['gov_cyber'] ?? 0);    // 30
            $govQ3 = (float)($_POST['gov_trans'] ?? 0);    // 30
            $govScore = min(100.0, $govQ1 + $govQ2 + $govQ3);

            $comment = trim($_POST['supplier_comment'] ?? '');

            $res = SupplierService::submitSurvey($token, $envScore, $socScore, $govScore, $comment);
            if ($res['success']) {
                $submitted = true;
                $evalResult = $res;
                $supplier = Database::fetch("SELECT * FROM esg_supplier_assessments WHERE survey_token = ?", [$token]);
            }
        }

        View::render('suppliers/public_survey', [
            'supplier'   => $supplier,
            'submitted'  => $submitted,
            'evalResult' => $evalResult,
            'token'      => $token
        ], null);
    }

    public function capaUpdate(): void
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }
        Auth::requireRoles('ROLE_ESG_MGR');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $capaStatus = $_POST['capa_status'] ?? 'NONE';
            $capaComment = trim($_POST['capa_comment'] ?? '');
            if (!in_array($capaStatus, ['NONE', 'REQUIRED', 'RESOLVED'], true) || mb_strlen($capaComment) > 5000) {
                http_response_code(422);
                die('422 Unprocessable Entity: CAPA 資料不正確。');
            }

            Database::execute(
                "UPDATE esg_supplier_assessments SET capa_status = ?, capa_comment = ? WHERE id = ?",
                [$capaStatus, $capaComment, $id]
            );

            AuditLogger::log('SUPPLIER_CAPA', 'UPDATE', $id, null, ['status' => $capaStatus, 'comment' => $capaComment]);

            header('Location: ' . Auth::baseUrl() . '/suppliers?msg=capa_updated');
            exit;
        }
    }
}
