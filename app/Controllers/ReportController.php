<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\AuditLogger;
use App\Services\ReportService;
use App\Services\CarbonCalculator;

class ReportController
{
    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: ' . Auth::baseUrl() . '/auth/login');
            exit;
        }
    }

    public function gri(): void
    {
        $year = (int)($_GET['year'] ?? date('Y'));
        $griItems = ReportService::getGriContentIndex($year);

        View::render('reports/gri', [
            'year'     => $year,
            'griItems' => $griItems
        ]);
    }

    public function sasb(): void
    {
        $year = (int)($_GET['year'] ?? date('Y'));
        $ghgSummary = CarbonCalculator::getSummaryByYear($year);

        $sasbMetrics = [
            [
                'topic'       => '溫室氣體排放 (GHG Emissions)',
                'code'        => 'TC-SC-110a.1',
                'description' => '範疇一直接排放量 (Gross global Scope 1 emissions)',
                'value'       => sprintf('%.2f 公噸 CO2e', $ghgSummary['SCOPE1']),
                'unit'        => 'Metric tons (t) CO2e'
            ],
            [
                'topic'       => '溫室氣體排放 (GHG Emissions)',
                'code'        => 'TC-SC-110a.2',
                'description' => '範疇二能源間接排放量 (Location-based Scope 2)',
                'value'       => sprintf('%.2f 公噸 CO2e', $ghgSummary['SCOPE2']),
                'unit'        => 'Metric tons (t) CO2e'
            ],
            [
                'topic'       => '能源管理 (Energy Management)',
                'code'        => 'TC-SC-130a.1',
                'description' => '總耗用能源與綠電比例 (Total energy consumed & % grid/renewable)',
                'value'       => '總電力 4,155,000 kWh，再生能源綠電比率 14.8%',
                'unit'        => 'Gigajoules (GJ) / MWh'
            ],
            [
                'topic'       => '水資源管理 (Water Management)',
                'code'        => 'TC-SC-140a.1',
                'description' => '總取水量與製程回收水比率 (Total water withdrawn & recycled %)',
                'value'       => '總用水量 126,100 m3，製程水回收率 34.6%',
                'unit'        => 'Thousand m3'
            ],
            [
                'topic'       => '員工職業健康與安全 (OHS)',
                'code'        => 'TC-SC-320a.1',
                'description' => '總記錄傷害率 (TRIR) 與失能傷害頻率 (LTIFR)',
                'value'       => 'LTIFR: 0.252，SR: 1.260，死亡事故件數: 0',
                'unit'        => 'Rate'
            ]
        ];

        View::render('reports/sasb', [
            'year'        => $year,
            'sasbMetrics' => $sasbMetrics
        ]);
    }

    /**
     * 匯出報告書與查驗底稿 (M06-03)
     */
    public function export(): void
    {
        $format = $_GET['format'] ?? 'html';
        $year = (int)($_GET['year'] ?? date('Y'));
        $griItems = ReportService::getGriContentIndex($year);
        $ghgSummary = CarbonCalculator::getSummaryByYear($year);

        AuditLogger::log('REPORT', 'EXPORT', null, null, ['format' => $format, 'year' => $year]);

        if ($format === 'excel') {
            // 輸出 CSV/Excel 相容檔案
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="ESG_Audit_Report_' . $year . '.csv"');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            echo "準則名稱,指標編號,揭露主題說明,數據內容 / 揭露狀態\n";
            foreach ($griItems as $item) {
                echo '"' . str_replace('"', '""', $item['standard']) . '",';
                echo '"' . str_replace('"', '""', $item['item_code']) . '",';
                echo '"' . str_replace('"', '""', $item['description']) . '",';
                echo '"' . str_replace('"', '""', $item['value']) . '"' . "\n";
            }
            exit;
        }

        if ($format === 'word') {
            header("Content-Type: application/vnd.ms-word; charset=utf-8");
            header("Content-Disposition: attachment; filename=ESG_Sustainability_Report_{$year}.doc");
            echo "<html><head><meta charset='utf-8'><title>ESG 永續報告書 {$year}</title></head><body>";
            echo "<h1 style='color:#1B4332;'>企業永續報告書 - {$year} 年度查證確信底稿</h1>";
            echo "<p>申報機構：ESG智慧管理集團股份有限公司 | 發布日期：" . date('Y-m-d') . "</p>";
            echo "<hr>";
            echo "<h2>一、溫室氣體盤查統計 (ISO 14064-1 / GHG Protocol)</h2>";
            echo "<ul>";
            echo "<li>範疇一 (直接排放): " . number_format($ghgSummary['SCOPE1'], 2) . " 公噸 CO2e</li>";
            echo "<li>範疇二 (能源間接): " . number_format($ghgSummary['SCOPE2'], 2) . " 公噸 CO2e</li>";
            echo "<li>範疇三 (其他間接): " . number_format($ghgSummary['SCOPE3'], 2) . " 公噸 CO2e</li>";
            echo "<li><strong>總排放量: " . number_format($ghgSummary['TOTAL'], 2) . " 公噸 CO2e</strong></li>";
            echo "</ul>";
            echo "<h2>二、GRI 2021 內容索引表</h2>";
            echo "<table border='1' cellspacing='0' cellpadding='6' style='border-collapse:collapse;width:100%;'>";
            echo "<tr style='background:#1B4332;color:#fff;'><th>標準</th><th>指標編號</th><th>主題說明</th><th>揭露數值與說明</th></tr>";
            foreach ($griItems as $item) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item['standard']) . "</td>";
                echo "<td>" . htmlspecialchars($item['item_code']) . "</td>";
                echo "<td>" . htmlspecialchars($item['description']) . "</td>";
                echo "<td>" . htmlspecialchars($item['value']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</body></html>";
            exit;
        }

        // 預覽與列印版視圖
        View::render('reports/preview', [
            'year'       => $year,
            'griItems'   => $griItems,
            'ghgSummary' => $ghgSummary
        ], null);
    }
}
