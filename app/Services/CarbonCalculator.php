<?php
namespace App\Services;

use App\Core\Database;
use App\Core\SystemSettings;

/**
 * 碳盤查運算核心引擎
 * 遵循 ISO 14064-1:2018 與 GHG Protocol 標準
 */
class CarbonCalculator
{
    /**
     * 計算單筆活動之碳排當量 (公噸 CO2e)
     * 公式：(活動量 * 係數) / 1000 (若係數單位為 kgCO2e)
     */
    public static function calculateEmission(float $activityAmount, float $factorValue, string $unit): float
    {
        $rawKg = $activityAmount * $factorValue;
        // 若單位為公噸 (tCO2e) 則不除以 1000，一般環境部係數為 kgCO2e/*
        if (str_contains(strtolower($unit), 'tco2e') || str_contains($unit, '公噸')) {
            return round($rawKg, 4);
        }
        return round($rawKg / 1000.0, 4);
    }

    /**
     * 範疇二雙軌計算
     * 1. 地點基礎 (Location-based): 總度數 * 係數
     * 2. 市場基礎 (Market-based): (總度數 - 綠電) * 係數
     */
    public static function calculateScope2(float $totalKwh, float $renewableKwh, float $gridFactor): array
    {
        $locationKg = $totalKwh * $gridFactor;
        $netKwh = max(0.0, $totalKwh - $renewableKwh);
        $marketKg = $netKwh * $gridFactor;

        return [
            'location_based_co2e' => round($locationKg / 1000.0, 4),
            'market_based_co2e'   => round($marketKg / 1000.0, 4),
            'green_power_offset_co2e' => round(($totalKwh - $netKwh) * $gridFactor / 1000.0, 4),
            'renewable_ratio_pct' => $totalKwh > 0 ? round(($renewableKwh / $totalKwh) * 100, 2) : 0.0
        ];
    }

    /**
     * 智能防錯與偏差比對 (±20% 門檻檢驗 - 規格書 M05-04)
     */
    public static function checkAnomaly(int $siteId, int $factorId, float $currentAmount): array
    {
        $history = Database::fetch(
            "SELECT AVG(activity_amount) as avg_amount, COUNT(*) as count
             FROM esg_ghg_records
             WHERE site_id = ? AND factor_id = ? AND data_status = 'APPROVED'",
            [$siteId, $factorId]
        );

        if (!$history || (int)$history['count'] < 1 || (float)$history['avg_amount'] <= 0) {
            return ['is_anomaly' => false, 'deviation_pct' => 0.0, 'avg_amount' => 0.0];
        }

        $avg = (float)$history['avg_amount'];
        $deviationPct = round((($currentAmount - $avg) / $avg) * 100, 2);

        $isAnomaly = abs($deviationPct) >= SystemSettings::get('ghg_anomaly_threshold_pct');

        return [
            'is_anomaly'    => $isAnomaly,
            'deviation_pct' => $deviationPct,
            'avg_amount'    => round($avg, 2)
        ];
    }

    /**
     * 集團總碳盤查匯總 (按範疇與年份)
     */
    public static function getSummaryByYear(?int $year = null, ?int $siteId = null): array
    {
        $year = $year ?: (int)date('Y');
        
        $params = [$year];
        $siteSql = "";
        if ($siteId) {
            $siteSql = " AND r.site_id = ?";
            $params[] = $siteId;
        }

        $sql = "
            SELECT 
                f.scope,
                COUNT(r.id) as record_count,
                COALESCE(SUM(r.calculated_co2e), 0) as total_co2e
            FROM esg_ghg_records r
            JOIN esg_ghg_factors f ON r.factor_id = f.id
            WHERE YEAR(r.record_date) = ?
              AND r.data_status = 'APPROVED'
              $siteSql
            GROUP BY f.scope
        ";

        $rows = Database::query($sql, $params);
        $summary = [
            'SCOPE1' => 0.0,
            'SCOPE2' => 0.0,
            'SCOPE3' => 0.0,
            'TOTAL'  => 0.0
        ];

        foreach ($rows as $row) {
            $summary[$row['scope']] = (float)$row['total_co2e'];
            $summary['TOTAL'] += (float)$row['total_co2e'];
        }

        return $summary;
    }

    /**
     * SBTi 減碳路徑目標比對 (規格書 3.2 M02-07)
     */
    public static function getSbtiProgress(?int $currentYear = null): array
    {
        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        $sbti = $appConfig['sbti'];

        $currentYear = $currentYear ?: (int)date('Y');
        $baseYear = (int)$sbti['baseline_year'];
        $targetYear = (int)$sbti['target_year'];
        $baseEmission = (float)$sbti['baseline_emissions'];
        $targetReductionPct = (float)$sbti['target_reduction_pct'];

        // 年均目標降幅
        $totalYears = max(1, $targetYear - $baseYear);
        $annualTargetRate = ($targetReductionPct / 100.0) / $totalYears;

        // 當年度理論目標排放上限
        $elapsedYears = max(0, $currentYear - $baseYear);
        $expectedReduction = $baseEmission * ($annualTargetRate * $elapsedYears);
        $targetEmissionThisYear = max(0.0, $baseEmission - $expectedReduction);

        // 取得當年度實際總碳排 (已審批)
        $actualRow = Database::fetch(
            "SELECT COALESCE(SUM(calculated_co2e), 0) as total 
             FROM esg_ghg_records 
             WHERE YEAR(record_date) = ? AND data_status = 'APPROVED'",
            [$currentYear]
        );
        $actualEmission = (float)($actualRow['total'] ?? 0.0);

        // 差距 Gap
        $gap = $actualEmission - $targetEmissionThisYear;
        $gapPct = $targetEmissionThisYear > 0 ? round(($gap / $targetEmissionThisYear) * 100, 2) : 0.0;
        $isLagging = $gapPct > 5.0; // 落後 5% 觸發紅燈 (M02-07)

        return [
            'baseline_year'           => $baseYear,
            'baseline_emissions'      => $baseEmission,
            'target_year'             => $targetYear,
            'current_year'            => $currentYear,
            'target_emission_current' => round($targetEmissionThisYear, 2),
            'actual_emission_current' => round($actualEmission, 2),
            'gap_co2e'                => round($gap, 2),
            'gap_pct'                 => $gapPct,
            'is_lagging'              => $isLagging,
            'status_label'            => $isLagging ? '落後警示 (落後>5%)' : '在達標軌跡內'
        ];
    }
}
