-- =========================================================================
-- ESG 企業永續智慧管理系統 - MySQL 資料庫結構定義
-- SPEC-ESG-2026-V1.0
-- =========================================================================

CREATE DATABASE IF NOT EXISTS `esg_management_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `esg_management_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- 0a. AI 客服小編設定（API 金鑰以 AES-256-GCM 加密保存）
DROP TABLE IF EXISTS `sys_ai_assistant_settings`;
CREATE TABLE `sys_ai_assistant_settings` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  `model` VARCHAR(64) NOT NULL DEFAULT 'gpt-6-luna',
  `api_key_encrypted` TEXT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `sys_ai_assistant_settings` (`id`, `model`, `is_enabled`) VALUES (1, 'gpt-6-luna', 0);

-- 0. 可調整系統參數 (僅 Super Admin 維護)
DROP TABLE IF EXISTS `sys_settings`;
CREATE TABLE `sys_settings` (
  `setting_key` VARCHAR(80) NOT NULL PRIMARY KEY,
  `setting_value` VARCHAR(255) NOT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Super Admin 管理之可調整系統參數';

-- 1. 角色表 (sys_roles)
DROP TABLE IF EXISTS `sys_roles`;
CREATE TABLE `sys_roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_code` VARCHAR(30) NOT NULL UNIQUE COMMENT '角色代碼 (如 ROLE_ADMIN)',
  `role_name` VARCHAR(100) NOT NULL COMMENT '角色名稱 (如 永續專責小組)',
  `data_scope` ENUM('ALL','SITE','DEPT','SELF') NOT NULL DEFAULT 'DEPT' COMMENT '資料管轄範圍',
  `description` VARCHAR(255) NULL COMMENT '角色職責說明'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系統角色定義表';

-- 2. 營運據點與廠區表 (org_sites)
DROP TABLE IF EXISTS `org_sites`;
CREATE TABLE `org_sites` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `site_code` VARCHAR(20) NOT NULL UNIQUE COMMENT '廠區代號 (如 SITE-01)',
  `site_name` VARCHAR(100) NOT NULL COMMENT '廠區名稱 (如 新竹一廠)',
  `country` VARCHAR(50) NOT NULL DEFAULT 'Taiwan' COMMENT '所在國家',
  `is_in_boundary` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否納入碳盤查邊界 (1:是, 0:否)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='營運據點與廠區表';

-- 3. 組織部門表 (org_departments)
DROP TABLE IF EXISTS `org_departments`;
CREATE TABLE `org_departments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `site_id` INT UNSIGNED NOT NULL COMMENT '所屬廠區 ID',
  `dept_name` VARCHAR(100) NOT NULL COMMENT '部門名稱',
  `parent_dept_id` INT UNSIGNED NULL COMMENT '上級部門 ID',
  `manager_user_id` BIGINT UNSIGNED NULL COMMENT '部門主管 User ID',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_dept_site` (`site_id`),
  CONSTRAINT `fk_dept_site` FOREIGN KEY (`site_id`) REFERENCES `org_sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='組織部門表';

-- 4. 使用者帳號表 (sys_users)
DROP TABLE IF EXISTS `sys_users`;
CREATE TABLE `sys_users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '登入帳號 / 員工編號',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'BCrypt 雜湊加密密碼',
  `real_name` VARCHAR(100) NOT NULL COMMENT '使用者真實姓名',
  `email` VARCHAR(150) NOT NULL UNIQUE COMMENT '公務電子郵件',
  `role_id` INT UNSIGNED NOT NULL DEFAULT 5 COMMENT '角色 ID (關聯 sys_roles)',
  `dept_id` INT UNSIGNED NOT NULL COMMENT '所屬部門 ID',
  `site_id` INT UNSIGNED NOT NULL COMMENT '所屬廠區 ID',
  `status` ENUM('ACTIVE','LOCKED','DISABLED') NOT NULL DEFAULT 'ACTIVE' COMMENT '帳號狀態',
  `failed_login_count` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '登入失敗累計次數',
  `locked_until` DATETIME NULL COMMENT '帳號暫時鎖定截止時間',
  `last_login_at` DATETIME NULL COMMENT '最後登入時間',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_site` (`site_id`),
  INDEX `idx_user_dept` (`dept_id`),
  INDEX `idx_user_role` (`role_id`),
  INDEX `idx_user_locked_until` (`locked_until`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `sys_roles` (`id`),
  CONSTRAINT `fk_user_site` FOREIGN KEY (`site_id`) REFERENCES `org_sites` (`id`),
  CONSTRAINT `fk_user_dept` FOREIGN KEY (`dept_id`) REFERENCES `org_departments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='使用者帳號表';

-- 5. 溫室氣體排放係數庫表 (esg_ghg_factors)
DROP TABLE IF EXISTS `esg_ghg_factors`;
CREATE TABLE `esg_ghg_factors` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `scope` ENUM('SCOPE1','SCOPE2','SCOPE3') NOT NULL COMMENT '盤查範疇分類',
  `category` VARCHAR(50) NOT NULL COMMENT '細項類別 (如 固定燃燒/電力/差旅)',
  `fuel_name` VARCHAR(100) NOT NULL COMMENT '能源燃料名稱 (如 柴油/天然氣/電力)',
  `factor_value` DECIMAL(12,6) NOT NULL COMMENT '排放係數數值',
  `unit` VARCHAR(30) NOT NULL COMMENT '係數單位 (如 kgCO2e/度, kgCO2e/L)',
  `source_org` VARCHAR(100) NOT NULL COMMENT '發布機構 (如 環境部, 台電, IPCC)',
  `version_year` SMALLINT NOT NULL COMMENT '適用年度',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否啟用中',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='溫室氣體排放係數庫表';

-- 6. 填報任務與審批歷程表 (esg_tasks)
DROP TABLE IF EXISTS `esg_tasks`;
CREATE TABLE `esg_tasks` (
  `task_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `task_title` VARCHAR(150) NOT NULL COMMENT '任務標題',
  `module_type` ENUM('ENV','SOC','GOV','SUPPLIER') NOT NULL COMMENT '所屬 ESG 構面模組',
  `assigned_to` BIGINT UNSIGNED NOT NULL COMMENT '指派填報人員 User ID',
  `approver_id` BIGINT UNSIGNED NOT NULL COMMENT '當前待簽核主管 User ID',
  `due_date` DATE NOT NULL COMMENT '填報截止日期',
  `current_status` ENUM('PENDING','UNDER_REVIEW','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING' COMMENT '工單狀態',
  `approval_comment` TEXT NULL COMMENT '主管審核/駁回意見',
  `signed_at` DATETIME NULL COMMENT '簽核完成時間',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_task_assigned` (`assigned_to`),
  INDEX `idx_task_approver` (`approver_id`),
  CONSTRAINT `fk_task_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `sys_users` (`id`),
  CONSTRAINT `fk_task_approver` FOREIGN KEY (`approver_id`) REFERENCES `sys_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='填報任務與審批工單表';

-- 7. 溫室氣體活動數據紀錄表 (esg_ghg_records)
DROP TABLE IF EXISTS `esg_ghg_records`;
CREATE TABLE `esg_ghg_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `task_id` BIGINT UNSIGNED NULL COMMENT '關聯填報任務工單 ID',
  `site_id` INT UNSIGNED NOT NULL COMMENT '所屬廠區 ID',
  `dept_id` INT UNSIGNED NOT NULL COMMENT '責任部門 ID',
  `factor_id` INT UNSIGNED NOT NULL COMMENT '套用之排放係數 ID',
  `record_date` DATE NOT NULL COMMENT '數據歸屬日期',
  `activity_amount` DECIMAL(14,4) NOT NULL DEFAULT 0.0000 COMMENT '原始活動量',
  `calculated_co2e` DECIMAL(14,4) NOT NULL DEFAULT 0.0000 COMMENT '計算後碳排放量 (公噸 CO2e)',
  `evidence_file_url` VARCHAR(255) NULL COMMENT '佐證單據憑證路徑',
  `data_status` ENUM('DRAFT','SUBMITTED','APPROVED','REJECTED') NOT NULL DEFAULT 'DRAFT' COMMENT '審批狀態',
  `anomaly_reason` TEXT NULL COMMENT '±20% 偏差異常原因說明',
  `created_by` BIGINT UNSIGNED NOT NULL COMMENT '填報人員 User ID',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ghg_site_date` (`site_id`, `record_date`),
  INDEX `idx_ghg_factor` (`factor_id`),
  INDEX `idx_ghg_task` (`task_id`),
  CONSTRAINT `fk_ghg_site` FOREIGN KEY (`site_id`) REFERENCES `org_sites` (`id`),
  CONSTRAINT `fk_ghg_dept` FOREIGN KEY (`dept_id`) REFERENCES `org_departments` (`id`),
  CONSTRAINT `fk_ghg_factor` FOREIGN KEY (`factor_id`) REFERENCES `esg_ghg_factors` (`id`),
  CONSTRAINT `fk_ghg_task` FOREIGN KEY (`task_id`) REFERENCES `esg_tasks` (`task_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ghg_creator` FOREIGN KEY (`created_by`) REFERENCES `sys_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='溫室氣體活動數據紀錄表';

-- 8. 能源與水資源消耗紀錄表 (esg_energy_water_data)
DROP TABLE IF EXISTS `esg_energy_water_data`;
CREATE TABLE `esg_energy_water_data` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `site_id` INT UNSIGNED NOT NULL COMMENT '所屬廠區 ID',
  `period_year_month` CHAR(7) NOT NULL COMMENT '填報年月 (YYYY-MM)',
  `electricity_kwh` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT '總用電度數 (kWh)',
  `renewable_kwh` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT '綠電使用度數 (kWh)',
  `water_m3` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '總用水量 (m3)',
  `recycled_water_m3` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '水資源回收量 (m3)',
  `waste_general_kg` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '一般生活垃圾重量 (kg)',
  `waste_hazardous_kg` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '有害事業廢棄物重量 (kg)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_site_period` (`site_id`, `period_year_month`),
  CONSTRAINT `fk_ew_site` FOREIGN KEY (`site_id`) REFERENCES `org_sites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能源與水資源消耗紀錄表';

-- 9. 社會責任指標表 (esg_social_metrics)
DROP TABLE IF EXISTS `esg_social_metrics`;
CREATE TABLE `esg_social_metrics` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `site_id` INT UNSIGNED NOT NULL COMMENT '廠區 ID',
  `report_year` SMALLINT NOT NULL COMMENT '申報年度',
  `total_employees` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '期末員工總人數',
  `female_employees` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '女性員工人數',
  `female_managers` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '女性主管人數',
  `disabled_employees` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '身心障礙員工人數',
  `total_work_hours` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '總工作工時',
  `occupational_injuries` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '工傷事故件數',
  `lost_days` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '因工傷損失工作日數',
  `training_hours_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '員工總受訓時數',
  `community_investment_ntd` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT '社區公益投入總金額 (NTD)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_site_social_year` (`site_id`, `report_year`),
  CONSTRAINT `fk_social_site` FOREIGN KEY (`site_id`) REFERENCES `org_sites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='社會責任指標表';

-- 10. 公司治理指標表 (esg_gov_metrics)
DROP TABLE IF EXISTS `esg_gov_metrics`;
CREATE TABLE `esg_gov_metrics` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_year` SMALLINT NOT NULL UNIQUE COMMENT '申報年度',
  `board_seats_total` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '董事會總席次',
  `independent_directors` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '獨立董事席次',
  `female_directors` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '女性董事席次',
  `board_attendance_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '董事會平均出席率 (%)',
  `anti_corruption_trained` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '員工反貪腐培訓受訓率 (%)',
  `whistleblower_cases` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '檢舉案件受理件數',
  `cyber_security_incidents` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '重大資安事件件數',
  `iso27001_certified` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否通過 ISO 27001 驗證',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公司治理指標表';

-- 11. 供應商 ESG 評鑑表 (esg_supplier_assessments)
DROP TABLE IF EXISTS `esg_supplier_assessments`;
CREATE TABLE `esg_supplier_assessments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `supplier_code` VARCHAR(50) NOT NULL COMMENT '供應商統一編號 / 代碼',
  `supplier_name` VARCHAR(150) NOT NULL COMMENT '供應商公司全名',
  `eval_year` SMALLINT NOT NULL COMMENT '評鑑年度',
  `env_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '環境構面得分 (40%)',
  `soc_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '社會構面得分 (30%)',
  `gov_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '治理構面得分 (30%)',
  `total_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '加權總分 (滿分100分)',
  `risk_level` ENUM('LOW_A','MED_B','HIGH_C') NOT NULL DEFAULT 'LOW_A' COMMENT '風險等級 (綠燈/黃燈/紅燈)',
  `survey_token` VARCHAR(64) NOT NULL UNIQUE COMMENT '問卷填答專屬安全憑證 Token',
  `survey_status` ENUM('PENDING','SUBMITTED') NOT NULL DEFAULT 'PENDING' COMMENT '問卷狀態',
  `capa_status` ENUM('NONE','REQUIRED','RESOLVED') NOT NULL DEFAULT 'NONE' COMMENT '缺失改善工單狀態',
  `capa_comment` TEXT NULL COMMENT 'CAPA 改善對策與審查意見',
  `submitted_at` DATETIME NULL COMMENT '供應商填答提交時間',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='供應商 ESG 評鑑表';

-- 12. 系統全域稽核日誌表 (sys_audit_logs)
DROP TABLE IF EXISTS `sys_audit_logs`;
CREATE TABLE `sys_audit_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL COMMENT '操作使用者 ID (未登入為 NULL)',
  `ip_address` VARCHAR(45) NOT NULL COMMENT '使用者 IP 位址',
  `action_module` VARCHAR(50) NOT NULL COMMENT '操作功能模組',
  `action_type` ENUM('INSERT','UPDATE','DELETE','LOGIN','EXPORT') NOT NULL COMMENT '動作類型',
  `record_id` BIGINT UNSIGNED NULL COMMENT '異動資料列 ID',
  `old_values` JSON NULL COMMENT '修改前原始數值 (JSON)',
  `new_values` JSON NULL COMMENT '修改後新數值 (JSON)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_user` (`user_id`),
  INDEX `idx_audit_module` (`action_module`),
  INDEX `idx_audit_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系統全域稽核日誌表';

SET FOREIGN_KEY_CHECKS = 1;
