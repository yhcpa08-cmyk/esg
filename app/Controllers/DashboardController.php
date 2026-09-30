<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Services\CarbonCalculator;

class DashboardController
{
    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }
    }

    public function index(): void
    {
        $currentYear = (int)($_GET['year'] ?? date('Y'));
        $siteId = isset($_GET['site_id']) && $_GET['site_id'] !== '' ? (int)$_GET['site_id'] : null;

        // 廠區選單
        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");

        // 核心碳排匯總 (Scope 1, 2, 3)
        $ghgSummary = CarbonCalculator::getSummaryByYear($currentYear, $siteId);

        // SBTi 減碳目標進度
        $sbti = CarbonCalculator::getSbtiProgress($currentYear);

        // 能資源指標
        $ewSummary = Database::fetch(
            "SELECT 
                COALESCE(SUM(electricity_kwh), 0) as total_elec,
                COALESCE(SUM(renewable_kwh), 0) as total_renew,
                COALESCE(SUM(water_m3), 0) as total_water,
                COALESCE(SUM(recycled_water_m3), 0) as total_recycled_water
             FROM esg_energy_water_data
             WHERE LEFT(period_year_month, 4) = ?" . ($siteId ? " AND site_id = $siteId" : ""),
            [(string)$currentYear]
        );

        // 總員工數
        $socSummary = Database::fetch(
            "SELECT COALESCE(SUM(total_employees), 0) as total_emp 
             FROM esg_social_metrics 
             WHERE report_year = ?" . ($siteId ? " AND site_id = $siteId" : ""),
            [$currentYear]
        );
        $totalEmp = max(1, (int)($socSummary['total_emp'] ?? 2000));
        $perCapitaEmissions = round($ghgSummary['TOTAL'] / $totalEmp, 2);

        // 月度走勢數據 (1-12月)
        $monthlyGhg = array_fill(1, 12, 0.0);
        $siteFilter = $siteId ? " AND site_id = $siteId" : "";
        $monthlyRows = Database::query(
            "SELECT MONTH(record_date) as m, SUM(calculated_co2e) as total
             FROM esg_ghg_records
             WHERE YEAR(record_date) = ? AND data_status = 'APPROVED' $siteFilter
             GROUP BY MONTH(record_date)",
            [$currentYear]
        );
        foreach ($monthlyRows as $row) {
            $monthlyGhg[(int)$row['m']] = round((float)$row['total'], 2);
        }

        // 廠區分佈數據
        $siteEmissions = Database::query(
            "SELECT s.site_name, COALESCE(SUM(r.calculated_co2e), 0) as total
             FROM org_sites s
             LEFT JOIN esg_ghg_records r ON s.id = r.site_id AND YEAR(r.record_date) = ? AND r.data_status = 'APPROVED'
             GROUP BY s.id, s.site_name
             ORDER BY s.id ASC",
            [$currentYear]
        );

        // 待辦工單 (最新 5 筆)
        $user = Auth::user();
        $tasks = Database::query(
            "SELECT t.*, u1.real_name as assigned_name, u2.real_name as approver_name
             FROM esg_tasks t
             JOIN sys_users u1 ON t.assigned_to = u1.id
             JOIN sys_users u2 ON t.approver_id = u2.id
             ORDER BY t.task_id DESC LIMIT 5"
        );

        // 最新異動日誌 (最新 5 筆)
        $recentLogs = Database::query(
            "SELECT l.*, u.real_name 
             FROM sys_audit_logs l 
             LEFT JOIN sys_users u ON l.user_id = u.id 
             ORDER BY l.id DESC LIMIT 5"
        );

        View::render('dashboard/index', [
            'currentYear'        => $currentYear,
            'siteId'             => $siteId,
            'sites'              => $sites,
            'ghgSummary'         => $ghgSummary,
            'sbti'               => $sbti,
            'ewSummary'          => $ewSummary,
            'perCapitaEmissions' => $perCapitaEmissions,
            'monthlyGhg'         => array_values($monthlyGhg),
            'siteEmissions'      => $siteEmissions,
            'tasks'              => $tasks,
            'recentLogs'         => $recentLogs
        ]);
    }

    public function analytics(): void
    {
        $currentYear = (int)($_GET['year'] ?? date('Y'));
        
        // 排放源細項統計 (按燃料類別)
        $categoryBreakdown = Database::query(
            "SELECT f.category, f.scope, SUM(r.calculated_co2e) as total_co2e
             FROM esg_ghg_records r
             JOIN esg_ghg_factors f ON r.factor_id = f.id
             WHERE YEAR(r.record_date) = ? AND r.data_status = 'APPROVED'
             GROUP BY f.category, f.scope
             ORDER BY total_co2e DESC",
            [$currentYear]
        );

        // 能源消耗趨勢 (按月份)
        $energyTrends = Database::query(
            "SELECT period_year_month, 
                    SUM(electricity_kwh) as elec, 
                    SUM(renewable_kwh) as renew, 
                    SUM(water_m3) as water
             FROM esg_energy_water_data
             WHERE LEFT(period_year_month, 4) = ?
             GROUP BY period_year_month
             ORDER BY period_year_month ASC",
            [(string)$currentYear]
        );

        View::render('dashboard/analytics', [
            'currentYear'       => $currentYear,
            'categoryBreakdown' => $categoryBreakdown,
            'energyTrends'      => $energyTrends
        ]);
    }
}
