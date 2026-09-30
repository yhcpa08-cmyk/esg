USE `esg_management_db`;

CREATE TABLE IF NOT EXISTS `sys_settings` (
  `setting_key` VARCHAR(80) NOT NULL PRIMARY KEY,
  `setting_value` VARCHAR(255) NOT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Super Admin 管理之可調整系統參數';

INSERT INTO `sys_settings` (`setting_key`, `setting_value`) VALUES
('session_lifetime_minutes', '30'),
('ghg_anomaly_threshold_pct', '20'),
('evidence_max_size_mb', '50')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);
