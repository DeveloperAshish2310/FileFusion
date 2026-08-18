<?php

namespace App\Services;

use App\Helpers\MailHelper;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TwoFactorService
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a new Base32 secret key (16 or 32 characters).
     */
    public static function generateSecretKey(int $length = 16): string
    {
        $secret = '';
        $charsCount = strlen(self::BASE32_CHARS);
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[random_int(0, $charsCount - 1)];
        }
        return $secret;
    }

    /**
     * Generate the standard TOTP URI for QR Code scanning.
     */
    public static function getQrCodeUri(string $company, string $holder, string $secret): string
    {
        $issuer = rawurlencode($company);
        $account = rawurlencode($holder);
        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Calculate 6-digit TOTP code for a secret and timestamp.
     */
    public static function getOtp(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = floor($timestamp / 30);

        $secretBinary = self::base32Decode($secret);
        $timeBinary = pack('N*', 0) . pack('N*', $timeSlice);

        $hmac = hash_hmac('sha1', $timeBinary, $secretBinary, true);
        $offset = ord($hmac[19]) & 0x0f;

        $hashPart = substr($hmac, $offset, 4);
        $value = unpack('N', $hashPart)[1] & 0x7fffffff;

        $modulo = $value % 1000000;
        return str_pad((string) $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit TOTP code with time-drift tolerance (±1 step = ±30s).
     */
    public static function verifyTotp(string $secret, string $code, int $discrepancy = 1): bool
    {
        $cleanCode = preg_replace('/\s+/', '', trim($code));
        if (strlen($cleanCode) !== 6 || !ctype_digit($cleanCode)) {
            return false;
        }

        $currentTime = time();
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $timestamp = $currentTime + ($i * 30);
            if (hash_equals(self::getOtp($secret, $timestamp), $cleanCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate 8 secure one-time recovery codes (format: xxxx-xxxx).
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codeStr = Str::lower(Str::random(4)) . '-' . Str::lower(Str::random(4));
            $codes[] = [
                'code' => $codeStr,
                'used_at' => null,
            ];
        }
        return $codes;
    }

    /**
     * Get all recovery codes for a user with their used status and timestamps.
     */
    public static function getFormattedRecoveryCodes(User $user): array
    {
        $rawCodes = $user->two_factor_recovery_codes ?? [];
        if (!is_array($rawCodes)) {
            $rawCodes = is_string($rawCodes) ? json_decode($rawCodes, true) : [];
        }

        $formatted = [];
        $activeCount = 0;
        $usedCount = 0;

        foreach ($rawCodes as $item) {
            if (is_string($item)) {
                // Legacy plain string code -> active
                $formatted[] = [
                    'code' => $item,
                    'is_used' => false,
                    'used_at' => null,
                    'used_at_formatted' => null,
                ];
                $activeCount++;
            } elseif (is_array($item) && isset($item['code'])) {
                $isUsed = !empty($item['used_at']);
                $formatted[] = [
                    'code' => $item['code'],
                    'is_used' => $isUsed,
                    'used_at' => $item['used_at'] ?? null,
                    'used_at_formatted' => $isUsed ? date('d M Y, h:i A', strtotime($item['used_at'])) : null,
                ];
                if ($isUsed) {
                    $usedCount++;
                } else {
                    $activeCount++;
                }
            }
        }

        return [
            'total' => count($formatted),
            'active_count' => $activeCount,
            'used_count' => $usedCount,
            'codes' => $formatted,
        ];
    }

    /**
     * Verify and mark a one-time recovery code as used.
     */
    public static function verifyRecoveryCode(User $user, string $code): bool
    {
        $cleanCode = Str::lower(trim($code));
        $rawCodes = $user->two_factor_recovery_codes ?? [];

        if (!is_array($rawCodes)) {
            $rawCodes = is_string($rawCodes) ? json_decode($rawCodes, true) : [];
        }

        if (empty($rawCodes)) {
            return false;
        }

        $modified = false;

        foreach ($rawCodes as $index => $item) {
            if (is_string($item)) {
                if (hash_equals(Str::lower(trim($item)), $cleanCode)) {
                    $rawCodes[$index] = [
                        'code' => $item,
                        'used_at' => now()->toDateTimeString(),
                    ];
                    $modified = true;
                    break;
                }
            } elseif (is_array($item) && isset($item['code'])) {
                // Only allow if not yet used
                if (empty($item['used_at']) && hash_equals(Str::lower(trim($item['code'])), $cleanCode)) {
                    $rawCodes[$index]['used_at'] = now()->toDateTimeString();
                    $modified = true;
                    break;
                }
            }
        }

        if ($modified) {
            $user->two_factor_recovery_codes = $rawCodes;
            $user->save();
            return true;
        }

        return false;
    }

    /**
     * Generate and dispatch a dynamic Email OTP to the user.
     */
    public static function sendEmailOtp(User $user, string $action = 'Security Verification'): bool
    {
        $otp = (string) random_int(100000, 999999);

        $user->email_otp_code = Hash::make($otp);
        $user->email_otp_expires_at = now()->addMinutes(10);
        $user->save();

        return MailHelper::sendNotification($user->email, 'totp', [
            'totp_code' => $otp,
            'user_name' => $user->name,
            'expires_minutes' => 10,
            'action' => $action,
        ]);
    }

    /**
     * Verify and consume an Email OTP.
     */
    public static function verifyEmailOtp(User $user, string $code): bool
    {
        $cleanCode = preg_replace('/\s+/', '', trim($code));
        if (empty($user->email_otp_code) || empty($user->email_otp_expires_at)) {
            return false;
        }

        if (now()->isAfter($user->email_otp_expires_at)) {
            $user->email_otp_code = null;
            $user->email_otp_expires_at = null;
            $user->save();
            return false;
        }

        if (Hash::check($cleanCode, $user->email_otp_code)) {
            // Invalidate after use
            $user->email_otp_code = null;
            $user->email_otp_expires_at = null;
            $user->save();
            return true;
        }

        return false;
    }

    /**
     * Verify any allowed 2FA code (TOTP, Email OTP, or Recovery Code).
     */
    public static function verifyAny(User $user, string $code): array
    {
        $clean = trim($code);

        // 1. Check TOTP Authenticator code if secret exists
        if (!empty($user->two_factor_secret) && self::verifyTotp($user->two_factor_secret, $clean)) {
            return ['success' => true, 'method' => 'totp'];
        }

        // 2. Check Email OTP
        if (self::verifyEmailOtp($user, $clean)) {
            return ['success' => true, 'method' => 'email'];
        }

        // 3. Check Recovery Codes
        if (self::verifyRecoveryCode($user, $clean)) {
            return ['success' => true, 'method' => 'recovery'];
        }

        return ['success' => false, 'method' => null];
    }

    /**
     * Decode standard Base32 string to binary.
     */
    private static function base32Decode(string $b32): string
    {
        $b32 = strtoupper($b32);
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0, $len = strlen($b32); $i < $len; $i++) {
            $val = strpos(self::BASE32_CHARS, $b32[$i]);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }

        return $output;
    }
}
