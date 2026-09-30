<?php
namespace App\Core;

/** Encrypts the provider API key at rest; the key material is kept outside the database. */
class AiConfigCrypto
{
    private static function key(): string
    {
        $path = dirname(__DIR__, 2) . '/storage/ai_config.key';
        if (is_file($path)) {
            $encoded = trim((string) file_get_contents($path));
            $key = base64_decode($encoded, true);
            if ($key !== false && strlen($key) === 32) return $key;
        }
        $key = random_bytes(32);
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        if (file_put_contents($path, base64_encode($key), LOCK_EX) === false) {
            throw new \RuntimeException('無法建立 AI 設定加密金鑰檔案。');
        }
        @chmod($path, 0600);
        return $key;
    }

    public static function encrypt(string $plainText): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipherText = openssl_encrypt($plainText, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipherText === false) throw new \RuntimeException('API 金鑰加密失敗。');
        return base64_encode($iv . $tag . $cipherText);
    }

    public static function decrypt(string $payload): string
    {
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < 29) throw new \RuntimeException('已儲存的 API 金鑰格式無效。');
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipherText = substr($raw, 28);
        $plainText = openssl_decrypt($cipherText, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($plainText === false) throw new \RuntimeException('無法解密 API 金鑰。');
        return $plainText;
    }
}
