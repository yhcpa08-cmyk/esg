<?php
/**
 * 系統核心常數與全域環境配置
 * SPEC-ESG-2026-V1.0
 */
return [
    'name'           => 'ESG 企業永續智慧管理系統',
    'short_name'     => 'ESG 智慧管理系統',
    'version'        => 'SPEC-ESG-2026-V1.0',
    'base_url'       => '/ESG',
    'timezone'       => 'Asia/Taipei',
    'session_name'   => 'ESG_SESSION_ID',
    'session_lifetime' => 1800, // 30 分鐘閒置逾時 (規格書 M01-04)
    // 測試身分切換只能在明確啟用的非正式環境使用；正式環境務必維持 false。
    'allow_quick_login' => false,
    'max_login_fails'=> 5,    // 5 次失敗自動鎖定
    'lockout_minutes'=> 15,   // 鎖定 15 分鐘
    'upload_path'    => dirname(__DIR__) . '/storage/uploads/',
    // 不接受舊式 XLS，避免 Office 巨集與已知格式攻擊面。
    'allowed_extensions' => ['pdf', 'xlsx', 'docx', 'png', 'jpg', 'jpeg'],
    'max_upload_size'=> 52428800, // 50MB
    'sbti' => [
        'baseline_year'    => 2024,
        'baseline_emissions'=> 12500.0, // 公噸 CO2e
        'target_year'      => 2030,
        'target_reduction_pct' => 42.0, // 42% 減碳目標 (符合 1.5°C 路徑)
    ]
];
