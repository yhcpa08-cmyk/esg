-- Apply once to an existing esg_management_db database after backing it up.
USE `esg_management_db`;

ALTER TABLE `sys_users`
  ADD COLUMN `locked_until` DATETIME NULL COMMENT '帳號暫時鎖定截止時間' AFTER `failed_login_count`,
  ADD INDEX `idx_user_locked_until` (`locked_until`);

ALTER TABLE `esg_ghg_records`
  ADD INDEX `idx_ghg_task` (`task_id`),
  ADD CONSTRAINT `fk_ghg_task` FOREIGN KEY (`task_id`) REFERENCES `esg_tasks` (`task_id`) ON DELETE SET NULL;
