<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Core\AuditLogger;
use App\Core\SystemSettings;
use App\Core\UploadValidator;
use App\Services\CarbonCalculator;

class GhgController
{
    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }
    }

    /**
     * 溫室氣體盤查紀錄清冊 (M02-02, M02-03, M02-04)
     */
    public function records(): void
    {
        $scope = $_GET['scope'] ?? '';
        $siteId = $_GET['site_id'] ?? '';
        $status = $_GET['status'] ?? '';

        $sql = "
            SELECT r.*, f.scope, f.category, f.fuel_name, f.factor_value, f.unit,
                   s.site_name, d.dept_name, u.real_name as creator_name
            FROM esg_ghg_records r
            JOIN esg_ghg_factors f ON r.factor_id = f.id
            JOIN org_sites s ON r.site_id = s.id
            JOIN org_departments d ON r.dept_id = d.id
            JOIN sys_users u ON r.created_by = u.id
            WHERE 1=1
        ";
        $params = [];

        $user = Auth::user();
        if (($user['data_scope'] ?? '') === 'SITE') { $sql .= ' AND r.site_id = ?'; $params[] = (int)$user['site_id']; }
        if (($user['data_scope'] ?? '') === 'DEPT') { $sql .= ' AND r.dept_id = ?'; $params[] = (int)$user['dept_id']; }
        if (($user['data_scope'] ?? '') === 'SELF') { $sql .= ' AND r.created_by = ?'; $params[] = (int)$user['id']; }

        if ($scope) {
            $sql .= " AND f.scope = ?";
            $params[] = $scope;
        }
        if ($siteId) {
            $sql .= " AND r.site_id = ?";
            $params[] = $siteId;
        }
        if ($status) {
            $sql .= " AND r.data_status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.record_date DESC, r.id DESC";

        $records = Database::query($sql, $params);
        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");

        View::render('ghg/records', [
            'records' => $records,
            'sites'   => $sites,
            'scope'   => $scope,
            'siteId'  => $siteId,
            'status'  => $status
        ]);
    }

    /**
     * 新增活動數據填報 (含智慧運算、佐證上傳與 ±20% 偏差防錯)
     */
    public function create(): void
    {
        Auth::requireRoles('ROLE_ESG_MGR', 'ROLE_DEPT_MGR', 'ROLE_OPERATOR');
        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");
        $departments = Database::query("SELECT * FROM org_departments ORDER BY site_id, id ASC");
        $factors = Database::query("SELECT * FROM esg_ghg_factors WHERE is_active = 1 ORDER BY scope, id ASC");

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteId = (int)($_POST['site_id'] ?? 0);
            $deptId = (int)($_POST['dept_id'] ?? 0);
            $factorId = (int)($_POST['factor_id'] ?? 0);
            $recordDate = $_POST['record_date'] ?? date('Y-m-d');
            $activityAmount = (float)($_POST['activity_amount'] ?? 0.0);
            $anomalyReason = trim($_POST['anomaly_reason'] ?? '');
            Auth::requireScope($siteId, $deptId);
            $department = Database::fetch('SELECT id FROM org_departments WHERE id = ? AND site_id = ?', [$deptId, $siteId]);

            // 檢查係數
            $factor = Database::fetch("SELECT * FROM esg_ghg_factors WHERE id = ?", [$factorId]);
            if (!$department || !$factor || $activityAmount <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $recordDate)) {
                $error = '請選擇有效的排放源係數並輸入大於 0 之活動量';
            } else {
                // ±20% 智能偏差檢查 (M05-04)
                $anomaly = CarbonCalculator::checkAnomaly($siteId, $factorId, $activityAmount);
                if ($anomaly['is_anomaly'] && empty($anomalyReason)) {
                    $error = sprintf(
                    '【智能防錯警示】本次填報量 (%.2f) 與歷史平均 (%.2f) 偏差達 %.1f%% (門檻 ±%d%%)，依規章必須填寫「異常原因說明」方可提送！',
                        $activityAmount,
                        $anomaly['avg_amount'],
                    $anomaly['deviation_pct'],
                    SystemSettings::get('ghg_anomaly_threshold_pct')
                    );
                } else {
                    // 計算碳排放當量
                    $calculatedCo2e = CarbonCalculator::calculateEmission($activityAmount, (float)$factor['factor_value'], $factor['unit']);

                    // 處理發票佐證檔案上傳
                    $fileUrl = null;
                    if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
                        $maxBytes = SystemSettings::get('evidence_max_size_mb') * 1024 * 1024;
                        [$validFile, $result] = UploadValidator::validate($_FILES['evidence_file'], $appConfig['allowed_extensions'], $maxBytes);
                        if (!$validFile) {
                            $error = $result;
                        } else {
                            $ext = $result;
                            $filename = 'ev_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                            $targetPath = $appConfig['upload_path'] . $filename;
                            if (!is_dir($appConfig['upload_path'])) mkdir($appConfig['upload_path'], 0750, true);
                            if (move_uploaded_file($_FILES['evidence_file']['tmp_name'], $targetPath)) {
                                $fileUrl = 'uploads/' . $filename;
                            } else {
                                $error = '佐證檔案無法安全儲存，請稍後再試。';
                            }
                        }
                    }

                    // 寫入資料庫
                    if ($error !== null) {
                        View::render('ghg/create', compact('sites', 'departments', 'factors', 'error', 'success'));
                        return;
                    }
                    $userId = (int)Auth::user()['id'];
                    $recordId = Database::insert(
                        "INSERT INTO esg_ghg_records 
                         (site_id, dept_id, factor_id, record_date, activity_amount, calculated_co2e, evidence_file_url, data_status, anomaly_reason, created_by, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'SUBMITTED', ?, ?, NOW())",
                        [$siteId, $deptId, $factorId, $recordDate, $activityAmount, $calculatedCo2e, $fileUrl, $anomalyReason ?: null, $userId]
                    );

                    AuditLogger::log('GHG_RECORD', 'INSERT', $recordId, null, [
                        'site_id'  => $siteId,
                        'factor'   => $factor['fuel_name'],
                        'amount'   => $activityAmount,
                        'co2e'     => $calculatedCo2e
                    ]);

                    header('Location: ' . Auth::baseUrl() . '/ghg/records?msg=created');
                    exit;
                }
            }
        }

        View::render('ghg/create', [
            'sites'       => $sites,
            'departments' => $departments,
            'factors'     => $factors,
            'error'       => $error,
            'success'     => $success
        ]);
    }

    /**
     * 排放係數庫管理 (M02-01)
     */
    public function factors(): void
    {
        $scope = $_GET['scope'] ?? '';
        $sql = "SELECT * FROM esg_ghg_factors WHERE 1=1";
        $params = [];
        if ($scope) {
            $sql .= " AND scope = ?";
            $params[] = $scope;
        }
        $sql .= " ORDER BY scope, id ASC";

        $factors = Database::query($sql, $params);

        View::render('ghg/factors', [
            'factors' => $factors,
            'scope'   => $scope
        ]);
    }

    public function factorsCreate(): void
    {
        Auth::requireRoles('ROLE_ESG_MGR');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $scope = $_POST['scope'] ?? 'SCOPE1';
            $category = trim($_POST['category'] ?? '');
            $fuelName = trim($_POST['fuel_name'] ?? '');
            $factorValue = (float)($_POST['factor_value'] ?? 0.0);
            $unit = trim($_POST['unit'] ?? '');
            $sourceOrg = trim($_POST['source_org'] ?? '');
            $versionYear = (int)($_POST['version_year'] ?? date('Y'));

            $id = Database::insert(
                "INSERT INTO esg_ghg_factors (scope, category, fuel_name, factor_value, unit, source_org, version_year, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
                [$scope, $category, $fuelName, $factorValue, $unit, $sourceOrg, $versionYear]
            );

            AuditLogger::log('GHG_FACTOR', 'INSERT', $id, null, ['fuel_name' => $fuelName, 'factor' => $factorValue]);

            header('Location: ' . Auth::baseUrl() . '/ghg/factors');
            exit;
        }

        View::render('ghg/factors_create');
    }

    /**
     * 能源與水資源監控 (M02-05, M02-06)
     */
    public function energyWater(): void
    {
        $siteId = $_GET['site_id'] ?? '';
        $sql = "
            SELECT ew.*, s.site_name
            FROM esg_energy_water_data ew
            JOIN org_sites s ON ew.site_id = s.id
            WHERE 1=1
        ";
        $params = [];
        if ($siteId) {
            $sql .= " AND ew.site_id = ?";
            $params[] = $siteId;
        }
        $sql .= " ORDER BY ew.period_year_month DESC, ew.site_id ASC";

        $data = Database::query($sql, $params);
        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");

        View::render('ghg/energy_water', [
            'data'   => $data,
            'sites'  => $sites,
            'siteId' => $siteId
        ]);
    }

    public function energyWaterCreate(): void
    {
        Auth::requireRoles('ROLE_ESG_MGR', 'ROLE_DEPT_MGR', 'ROLE_OPERATOR');
        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteId = (int)($_POST['site_id'] ?? 0);
            $period = trim($_POST['period_year_month'] ?? date('Y-m'));
            $elec = (float)($_POST['electricity_kwh'] ?? 0);
            $renew = (float)($_POST['renewable_kwh'] ?? 0);
            $water = (float)($_POST['water_m3'] ?? 0);
            $recWater = (float)($_POST['recycled_water_m3'] ?? 0);
            $wasteGen = (float)($_POST['waste_general_kg'] ?? 0);
            $wasteHaz = (float)($_POST['waste_hazardous_kg'] ?? 0);
            Auth::requireScope($siteId, (int)Auth::user()['dept_id']);
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period) || min($elec, $renew, $water, $recWater, $wasteGen, $wasteHaz) < 0 || $renew > $elec || $recWater > $water) {
                http_response_code(422);
                die('422 Unprocessable Entity: 能水資料格式或數值範圍不正確。');
            }

            Database::execute(
                "INSERT INTO esg_energy_water_data 
                 (site_id, period_year_month, electricity_kwh, renewable_kwh, water_m3, recycled_water_m3, waste_general_kg, waste_hazardous_kg)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 electricity_kwh = VALUES(electricity_kwh), renewable_kwh = VALUES(renewable_kwh), 
                 water_m3 = VALUES(water_m3), recycled_water_m3 = VALUES(recycled_water_m3),
                 waste_general_kg = VALUES(waste_general_kg), waste_hazardous_kg = VALUES(waste_hazardous_kg)",
                [$siteId, $period, $elec, $renew, $water, $recWater, $wasteGen, $wasteHaz]
            );

            AuditLogger::log('ENERGY_WATER', 'INSERT', null, null, ['site_id' => $siteId, 'period' => $period]);

            header('Location: ' . Auth::baseUrl() . '/ghg/energy-water');
            exit;
        }

        View::render('ghg/energy_water_create', ['sites' => $sites]);
    }

    /**
     * SBTi 減碳目標追蹤儀表 (M02-07)
     */
    public function sbti(): void
    {
        $currentYear = (int)($_GET['year'] ?? date('Y'));
        $sbti = CarbonCalculator::getSbtiProgress($currentYear);

        View::render('ghg/sbti', [
            'sbti' => $sbti
        ]);
    }
}
