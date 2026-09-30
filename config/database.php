<?php
/**
 * 資料庫連線配置
 * SPEC-ESG-2026-V1.0
 */
return [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'esg_management_db',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
    // 正式主機可填入資料表前綴（例如 xenia6912_）；留白則使用原始資料表名稱。
    'table_prefix' => '',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ]
];
