<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Core\AuditLogger;

class SocialController
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
        $year = (int)($_GET['year'] ?? date('Y'));

        $metrics = Database::query(
            "SELECT sm.*, s.site_name
             FROM esg_social_metrics sm
             JOIN org_sites s ON sm.site_id = s.id
             WHERE sm.report_year = ?
             ORDER BY sm.site_id ASC",
            [$year]
        );

        // 彙整指標
        $summary = Database::fetch(
            "SELECT 
                SUM(total_employees) as total_emp,
                SUM(female_employees) as total_female,
                SUM(female_managers) as total_female_mgr,
                SUM(disabled_employees) as total_disabled,
                SUM(total_work_hours) as total_hours,
                SUM(occupational_injuries) as total_injuries,
                SUM(lost_days) as total_lost_days,
                SUM(training_hours_total) as total_training_hours,
                SUM(community_investment_ntd) as total_community_ntd
             FROM esg_social_metrics
             WHERE report_year = ?",
            [$year]
        );

        $totalEmp = max(1, (int)($summary['total_emp'] ?? 0));
        $femalePct = round(((int)($summary['total_female'] ?? 0) / $totalEmp) * 100, 1);
        $totalHours = max(1, (int)($summary['total_hours'] ?? 0));
        $ltifr = round(((int)($summary['total_injuries'] ?? 0) * 1000000.0) / $totalHours, 3);
        $sr = round(((int)($summary['total_lost_days'] ?? 0) * 1000000.0) / $totalHours, 3);
        $avgTrainingHours = round(((float)($summary['total_training_hours'] ?? 0)) / $totalEmp, 1);

        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");

        View::render('social/index', [
            'year'             => $year,
            'metrics'          => $metrics,
            'summary'          => $summary,
            'totalEmp'         => $totalEmp,
            'femalePct'        => $femalePct,
            'ltifr'            => $ltifr,
            'sr'               => $sr,
            'avgTrainingHours' => $avgTrainingHours,
            'sites'            => $sites
        ]);
    }

    public function create(): void
    {
        Auth::requireRoles('ROLE_ESG_MGR', 'ROLE_DEPT_MGR', 'ROLE_OPERATOR');
        $sites = Database::query("SELECT * FROM org_sites ORDER BY id ASC");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteId = (int)($_POST['site_id'] ?? 0);
            $reportYear = (int)($_POST['report_year'] ?? date('Y'));
            $totalEmp = (int)($_POST['total_employees'] ?? 0);
            $femaleEmp = (int)($_POST['female_employees'] ?? 0);
            $femaleMgr = (int)($_POST['female_managers'] ?? 0);
            $disabledEmp = (int)($_POST['disabled_employees'] ?? 0);
            $workHours = (int)($_POST['total_work_hours'] ?? 0);
            $injuries = (int)($_POST['occupational_injuries'] ?? 0);
            $lostDays = (int)($_POST['lost_days'] ?? 0);
            $trainingHours = (float)($_POST['training_hours_total'] ?? 0.0);
            $communityNtd = (float)($_POST['community_investment_ntd'] ?? 0.0);
            Auth::requireScope($siteId, (int)Auth::user()['dept_id']);
            if ($reportYear < 2000 || $reportYear > 2100 || min($totalEmp, $femaleEmp, $femaleMgr, $disabledEmp, $workHours, $injuries, $lostDays, $trainingHours, $communityNtd) < 0 || $femaleEmp > $totalEmp || $femaleMgr > $totalEmp || $disabledEmp > $totalEmp) {
                http_response_code(422);
                die('422 Unprocessable Entity: 社會指標資料不正確。');
            }

            Database::execute(
                "INSERT INTO esg_social_metrics
                 (site_id, report_year, total_employees, female_employees, female_managers, disabled_employees,
                  total_work_hours, occupational_injuries, lost_days, training_hours_total, community_investment_ntd)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                 total_employees = VALUES(total_employees), female_employees = VALUES(female_employees),
                 female_managers = VALUES(female_managers), disabled_employees = VALUES(disabled_employees),
                 total_work_hours = VALUES(total_work_hours), occupational_injuries = VALUES(occupational_injuries),
                 lost_days = VALUES(lost_days), training_hours_total = VALUES(training_hours_total),
                 community_investment_ntd = VALUES(community_investment_ntd)",
                [$siteId, $reportYear, $totalEmp, $femaleEmp, $femaleMgr, $disabledEmp, $workHours, $injuries, $lostDays, $trainingHours, $communityNtd]
            );

            AuditLogger::log('SOCIAL_METRICS', 'INSERT', null, null, ['site_id' => $siteId, 'year' => $reportYear]);

            header('Location: ' . Auth::baseUrl() . '/social?year=' . $reportYear);
            exit;
        }

        View::render('social/create', ['sites' => $sites]);
    }
}
