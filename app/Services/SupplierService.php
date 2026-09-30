<?php
namespace App\Services;

use App\Core\Database;
use App\Core\AuditLogger;

/**
 * 供應鏈 ESG 評鑑服務
 * 規格書 3.8 (M08-01 ~ M08-03) 實作
 */
class SupplierService
{
    /**
     * 計算加權總分與風險等級
     * 權重：E: 40%、S: 30%、G: 30%
     * A級 (>=80分) 綠燈
     * B級 (60-79.9分) 黃燈
     * C級 (<60分) 紅燈 (自動觸發 CAPA)
     */
    public static function evaluate(float $envScore, float $socScore, float $govScore): array
    {
        $totalScore = round(($envScore * 0.40) + ($socScore * 0.30) + ($govScore * 0.30), 2);

        if ($totalScore >= 80.0) {
            $riskLevel = 'LOW_A';
            $riskLabel = 'A 級 (優良 - 綠燈)';
            $badgeClass = 'success';
            $capaRequired = false;
        } elseif ($totalScore >= 60.0) {
            $riskLevel = 'MED_B';
            $riskLabel = 'B 級 (觀察 - 黃燈)';
            $badgeClass = 'warning text-dark';
            $capaRequired = false;
        } else {
            $riskLevel = 'HIGH_C';
            $riskLabel = 'C 級 (高風險 - 紅燈)';
            $badgeClass = 'danger';
            $capaRequired = true;
        }

        return [
            'total_score'   => $totalScore,
            'risk_level'    => $riskLevel,
            'risk_label'    => $riskLabel,
            'badge_class'   => $badgeClass,
            'capa_required' => $capaRequired
        ];
    }

    /**
     * 產生免登入問卷安全 Token
     */
    public static function generateToken(string $supplierCode): string
    {
        return 'tok_' . strtolower(substr(preg_replace('/[^a-zA-Z0-9]/', '', $supplierCode), 0, 8)) . '_' . bin2hex(random_bytes(8));
    }

    /**
     * 提交問卷填答
     */
    public static function submitSurvey(string $token, float $envScore, float $socScore, float $govScore, ?string $comments = null): array
    {
        $supplier = Database::fetch("SELECT * FROM esg_supplier_assessments WHERE survey_token = ?", [$token]);
        if (!$supplier) {
            return ['success' => false, 'message' => '無效的問卷填答連結'];
        }

        $eval = self::evaluate($envScore, $socScore, $govScore);

        $capaStatus = $eval['capa_required'] ? 'REQUIRED' : 'NONE';
        $capaComment = $eval['capa_required']
            ? ($comments ?: '評鑑總分未達 60 分 (C級)，系統自動開立 CAPA 限期改善工單。')
            : ($comments ?: '評鑑通過，維持常態供應商評級。');

        Database::execute(
            "UPDATE esg_supplier_assessments 
             SET env_score = ?, soc_score = ?, gov_score = ?, total_score = ?, 
                 risk_level = ?, survey_status = 'SUBMITTED', capa_status = ?, 
                 capa_comment = ?, submitted_at = NOW()
             WHERE id = ?",
            [$envScore, $socScore, $govScore, $eval['total_score'], $eval['risk_level'], $capaStatus, $capaComment, $supplier['id']]
        );

        AuditLogger::log('SUPPLIER', 'UPDATE', (int)$supplier['id'], $supplier, [
            'total_score' => $eval['total_score'],
            'risk_level'  => $eval['risk_level'],
            'capa_status' => $capaStatus
        ], null);

        return [
            'success'     => true,
            'total_score' => $eval['total_score'],
            'risk_level'  => $eval['risk_level'],
            'risk_label'  => $eval['risk_label']
        ];
    }
}
