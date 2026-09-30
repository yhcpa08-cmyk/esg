<?php
namespace App\Core;

/**
 * 系統全域稽核日誌記錄器
 * 依據規格書 3.1 (M01-05) 及 4.2.12 sys_audit_logs 規範實作
 */
class AuditLogger
{
    public static function log(
        string $module,
        string $actionType,
        ?int $recordId = null,
        $oldValues = null,
        $newValues = null,
        ?int $userId = null
    ): void {
        try {
            if ($userId === null && isset($_SESSION['user']['id'])) {
                $userId = (int)$_SESSION['user']['id'];
            }

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $oldJson = $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
            $newJson = $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

            Database::execute(
                "INSERT INTO `sys_audit_logs` (`user_id`, `ip_address`, `action_module`, `action_type`, `record_id`, `old_values`, `new_values`, `created_at`)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [$userId, $ip, $module, $actionType, $recordId, $oldJson, $newJson]
            );
        } catch (\Throwable $e) {
            // 稽核日誌不中斷主要業務邏輯，但寫入錯誤檔
            error_log('AuditLog Error: ' . $e->getMessage());
        }
    }
}
