<?php
namespace App\Core;

use PDO;
use Exception;

/**
 * PDO Singleton 資料庫連接管理器
 * 遵循 OWASP 規範，100% 採用 Prepared Statements 參數化查詢
 */
class Database
{
    private static ?PDO $instance = null;
    private static ?string $tablePrefix = null;
    private const TABLES = [
        'sys_ai_assistant_settings', 'sys_settings', 'sys_roles', 'sys_users', 'sys_audit_logs',
        'org_sites', 'org_departments', 'esg_ghg_factors', 'esg_ghg_records', 'esg_energy_water_data',
        'esg_social_metrics', 'esg_gov_metrics', 'esg_tasks', 'esg_supplier_assessments'
    ];

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';
            self::$tablePrefix = self::validatePrefix((string)($config['table_prefix'] ?? ''));
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (Exception $e) {
                die('資料庫連線失敗: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getInstance()->prepare(self::withTablePrefix($sql));
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::getInstance()->prepare(self::withTablePrefix($sql));
        $stmt->execute($params);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::getInstance()->prepare(self::withTablePrefix($sql));
        return $stmt->execute($params);
    }

    public static function insert(string $sql, array $params = []): int
    {
        $db = self::getInstance();
        $stmt = $db->prepare(self::withTablePrefix($sql));
        $stmt->execute($params);
        return (int)$db->lastInsertId();
    }

    private static function withTablePrefix(string $sql): string
    {
        if (self::$tablePrefix === null) self::getInstance();
        if (self::$tablePrefix === '') return $sql;
        $map = [];
        foreach (self::TABLES as $table) {
            $map['`' . $table . '`'] = '`' . self::$tablePrefix . $table . '`';
            $map[$table] = self::$tablePrefix . $table;
        }
        return strtr($sql, $map);
    }

    private static function validatePrefix(string $prefix): string
    {
        if ($prefix !== '' && !preg_match('/^[A-Za-z0-9_]+$/', $prefix)) {
            throw new \InvalidArgumentException('資料表前綴格式無效。');
        }
        return $prefix;
    }
}
