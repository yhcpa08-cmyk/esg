USE `esg_management_db`;

CREATE TABLE IF NOT EXISTS `sys_ai_assistant_settings` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  `model` VARCHAR(64) NOT NULL DEFAULT 'gpt-6-luna',
  `api_key_encrypted` TEXT NULL COMMENT 'AES-256-GCM encrypted API key; encryption key is kept in storage/ai_config.key',
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='OpenAI customer-service assistant configuration';

INSERT INTO `sys_ai_assistant_settings` (`id`, `model`, `is_enabled`)
VALUES (1, 'gpt-6-luna', 0)
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`);
