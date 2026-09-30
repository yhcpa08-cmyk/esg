<?php
namespace App\Core;

/** Centralized, database-backed settings with safe defaults. */
class SystemSettings
{
    private static array $cache = [];
    private const DEFAULTS = [
        'session_lifetime_minutes' => 30,
        'ghg_anomaly_threshold_pct' => 20,
        'evidence_max_size_mb' => 50,
    ];

    public static function get(string $key): int
    {
        if (!array_key_exists($key, self::DEFAULTS)) {
            throw new \InvalidArgumentException('Unknown system setting');
        }
        if (!array_key_exists($key, self::$cache)) {
            try {
                $row = Database::fetch('SELECT setting_value FROM sys_settings WHERE setting_key = ?', [$key]);
                self::$cache[$key] = $row ? (int)$row['setting_value'] : self::DEFAULTS[$key];
            } catch (\Throwable $e) {
                self::$cache[$key] = self::DEFAULTS[$key];
            }
        }
        return self::$cache[$key];
    }

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public static function replaceCache(array $values): void
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) self::$cache[$key] = (int)$value;
        }
    }
}
