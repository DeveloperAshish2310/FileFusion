<?php

namespace App\Helpers;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Encryptor
{
    // Legacy fallback parameters for backward compatibility
    private static $legacyKey = 'ASHISHISGREAT';
    private static $legacySalt = 'DEVELOPER';

    /**
     * Create a standalone Laravel Encrypter instance for any specified key.
     */
    public static function createEncrypter(string $key, string $cipher = 'AES-256-CBC'): Encrypter
    {
        $rawKey = self::parseKey($key);
        return new Encrypter($rawKey, $cipher);
    }

    /**
     * Parse raw string key or base64 key into binary key bytes.
     */
    public static function parseKey(string $key): string
    {
        $raw = trim($key);
        if (str_starts_with($raw, 'base64:')) {
            $raw = substr($raw, 7);
        }
        $decoded = base64_decode($raw, true);
        if ($decoded !== false && (strlen($decoded) === 16 || strlen($decoded) === 32)) {
            return $decoded;
        }

        // If string is already 32 bytes or 16 bytes
        if (strlen($raw) === 32 || strlen($raw) === 16) {
            return $raw;
        }

        // Fallback hash
        return hash('sha256', $raw, true);
    }

    /**
     * Generate a new cryptographically secure 256-bit base64 key.
     */
    public static function generateKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * Encrypt data using Laravel's secure Crypt service (AES-256-CBC, random IV, HMAC-SHA256).
     *
     * @param string|null $data
     * @return string|null
     */
    public static function encrypt($data)
    {
        if ($data === null || $data === '') {
            return $data;
        }

        try {
            return Crypt::encryptString((string) $data);
        } catch (\Throwable $e) {
            // If Crypt is unavailable in special environments, fallback
            return self::legacyEncrypt($data);
        }
    }

    /**
     * Encrypt data with a specific key.
     */
    public static function encryptWithKey(string $data, string $key): string
    {
        $encrypter = self::createEncrypter($key);
        return $encrypter->encryptString($data);
    }

    /**
     * Decrypt data, attempting secure Laravel Crypt first, then fallback key chain, then legacy cipher.
     *
     * @param string|null $data
     * @return string|null
     */
    public static function decrypt($data)
    {
        if ($data === null || $data === '') {
            return $data;
        }

        $result = self::singlePassDecrypt($data);

        // Auto-unwrap nested encryption envelopes (e.g. if field was double-encrypted during rotation)
        $depth = 0;
        while (is_string($result) && str_starts_with($result, 'eyJ') && $depth < 4) {
            $decoded = json_decode(base64_decode($result), true);
            if (is_array($decoded) && isset($decoded['iv']) && isset($decoded['value']) && isset($decoded['mac'])) {
                $next = self::singlePassDecrypt($result);
                if ($next !== null && $next !== $result) {
                    $result = $next;
                    $depth++;
                    continue;
                }
            }
            break;
        }

        return $result;
    }

    /**
     * Single pass decryption using Laravel Crypt, previous keys chain, and legacy fallback.
     */
    public static function singlePassDecrypt($data)
    {
        if ($data === null || $data === '') {
            return $data;
        }

        // 1. Try Laravel standard Crypt decryption (checks active key and config app.previous_keys)
        try {
            return Crypt::decryptString((string) $data);
        } catch (\Throwable $e) {
            // Fall through
        }

        // 2. Check previous keys from config / env if configured
        $prevKeys = config('app.previous_keys') ?: env('APP_PREVIOUS_KEYS', '');
        if (is_array($prevKeys)) {
            $keys = $prevKeys;
        } elseif (!empty($prevKeys)) {
            $keys = array_filter(explode(',', $prevKeys));
        } else {
            $keys = [];
        }

        foreach ($keys as $k) {
            try {
                $enc = self::createEncrypter(trim($k));
                return $enc->decryptString((string) $data);
            } catch (\Throwable $e) {
                continue;
            }
        }

        // 3. Fallback to legacy decryption for existing legacy records
        return self::legacyDecrypt($data);
    }

    /**
     * Decrypt data using a specific key.
     */
    public static function decryptWithKey(string $data, string $key): ?string
    {
        try {
            $encrypter = self::createEncrypter($key);
            return $encrypter->decryptString($data);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Legacy encryption for fallback compatibility.
     */
    private static function legacyEncrypt($data)
    {
        $method = 'AES-256-CBC';
        $key = hash('sha256', self::$legacyKey . self::$legacySalt, true);
        $iv = substr(hash('sha256', self::$legacySalt . self::$legacyKey, true), 0, 16);

        return base64_encode(openssl_encrypt($data, $method, $key, 0, $iv));
    }

    /**
     * Legacy decryption for backward compatibility.
     */
    private static function legacyDecrypt($data)
    {
        $method = 'AES-256-CBC';
        $key = hash('sha256', self::$legacyKey . self::$legacySalt, true);
        $iv = substr(hash('sha256', self::$legacySalt . self::$legacyKey, true), 0, 16);

        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return $data;
        }

        $decrypted = openssl_decrypt($decoded, $method, $key, 0, $iv);
        return $decrypted === false ? $data : $decrypted;
    }
}

