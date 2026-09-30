<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Core\AuditLogger;

class GovernanceController
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

        $gov = Database::fetch("SELECT * FROM esg_gov_metrics WHERE report_year = ?", [$year]);
        if (!$gov) {
            $gov = [
                'report_year'              => $year,
                'board_seats_total'        => 9,
                'independent_directors'    => 4,
                'female_directors'         => 3,
                'board_attendance_rate'    => 98.8,
                'anti_corruption_trained'  => 100.0,
                'whistleblower_cases'      => 0,
                'cyber_security_incidents' => 0,
                'iso27001_certified'       => 1
            ];
        }

        // TCFD 氣候風險鑑別資料 (M04-03 規格書定義)
        $tcfdRisks = [
            [
                'type'        => '轉型風險 (政策與法規)',
                'name'        => '台灣碳費徵收與歐盟 CBAM 碳邊境關稅實施',
                'probability' => 5, // 1-5
                'impact'      => 4, // 1-5
                'score'       => 20, // 機率 * 衝擊
                'level'       => '重大風險 (高)',
                'strategy'    => '加速自願減量專案、購買台電綠電與導入廠區屋頂型太陽能發電'
            ],
            [
                'type'        => '實體風險 (慢性極端氣候)',
                'name'        => '夏季強降雨與局部淹水風險可能衝擊物流發貨',
                'probability' => 3,
                'impact'      => 3,
                'score'       => 9,
                'level'       => '中度風險',
                'strategy'    => '強化各廠區防汛防水閘門與多元備援倉儲中心'
            ],
            [
                'type'        => '轉型風險 (市場與技術)',
                'name'        => '國際一線品牌客戶要求 100% 使用低碳封裝材',
                'probability' => 4,
                'impact'      => 4,
                'score'       => 16,
                'level'       => '重大風險 (高)',
                'strategy'    => '與供應商共同研發生物可分解包材並擴大回收料採購比重'
            ],
            [
                'type'        => '實體風險 (急劇氣候)',
                'name'        => '乾旱缺水風險導致工業用水限制',
                'probability' => 3,
                'impact'      => 4,
                'score'       => 12,
                'level'       => '中度風險',
                'strategy'    => '投資超純水回收系統，將全廠水回收率提升至 85% 以上'
            ]
        ];

        View::render('governance/index', [
            'year'      => $year,
            'gov'       => $gov,
            'tcfdRisks' => $tcfdRisks
        ]);
    }

    public function save(): void
    {
        Auth::requireRoles('ROLE_CSO', 'ROLE_ESG_MGR');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $year = (int)($_POST['report_year'] ?? date('Y'));
            $seats = (int)($_POST['board_seats_total'] ?? 9);
            $indep = (int)($_POST['independent_directors'] ?? 4);
            $female = (int)($_POST['female_directors'] ?? 3);
            $attRate = (float)($_POST['board_attendance_rate'] ?? 98.0);
            $antiCorr = (float)($_POST['anti_corruption_trained'] ?? 100.0);
            $whistle = (int)($_POST['whistleblower_cases'] ?? 0);
            $cyber = (int)($_POST['cyber_security_incidents'] ?? 0);
            $iso = isset($_POST['iso27001_certified']) ? 1 : 0;
            if ($year < 2000 || $year > 2100 || min($seats, $indep, $female, $attRate, $antiCorr, $whistle, $cyber) < 0 || $indep > $seats || $female > $seats || $attRate > 100 || $antiCorr > 100) {
                http_response_code(422);
                die('422 Unprocessable Entity: 治理指標資料不正確。');
            }

            Database::execute(
                "INSERT INTO esg_gov_metrics
                 (report_year, board_seats_total, independent_directors, female_directors, board_attendance_rate,
                  anti_corruption_trained, whistleblower_cases, cyber_security_incidents, iso27001_certified)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                 board_seats_total = VALUES(board_seats_total), independent_directors = VALUES(independent_directors),
                 female_directors = VALUES(female_directors), board_attendance_rate = VALUES(board_attendance_rate),
                 anti_corruption_trained = VALUES(anti_corruption_trained), whistleblower_cases = VALUES(whistleblower_cases),
                 cyber_security_incidents = VALUES(cyber_security_incidents), iso27001_certified = VALUES(iso27001_certified)",
                [$year, $seats, $indep, $female, $attRate, $antiCorr, $whistle, $cyber, $iso]
            );

            AuditLogger::log('GOV_METRICS', 'UPDATE', null, null, ['year' => $year]);

            header('Location: ' . Auth::baseUrl() . '/governance?year=' . $year . '&msg=saved');
            exit;
        }
    }
}
