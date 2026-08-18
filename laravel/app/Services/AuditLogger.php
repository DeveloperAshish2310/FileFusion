<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log an activity event to the audit database.
     */
    public static function log(
        string $action,
        string $description,
        string $status = 'info',
        array $context = [],
        ?User $user = null
    ): ?ActivityLog {
        try {
            $isExplicitGuest = !empty($context['attempted_email']) || ($action === 'auth.login.failed');
            $currentUser = $isExplicitGuest ? null : ($user ?? Auth::user());
            $userEmail = $currentUser ? $currentUser->email : ($context['attempted_email'] ?? $context['email'] ?? null);

            $request = request();

            $ipAddress = self::resolveClientIp();
            $userAgent = $request ? substr((string) $request->userAgent(), 0, 500) : null;
            $agentInfo = self::parseUserAgent($userAgent);

            return ActivityLog::create([
                'user_id' => $currentUser ? $currentUser->id : null,
                'user_email' => $userEmail,
                'action' => $action,
                'description' => $description,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device' => $agentInfo['device'],
                'browser' => $agentInfo['browser'],
                'os' => $agentInfo['os'],
                'url' => $request ? substr((string) $request->fullUrl(), 0, 500) : null,
                'method' => $request ? $request->method() : null,
                'status' => in_array($status, ['success', 'warning', 'danger', 'info']) ? $status : 'info',
                'context' => !empty($context) ? $context : null,
            ]);
        } catch (Exception $e) {
            // Fault-tolerant: never break user flow on logging error
            Log::warning('AuditLogger failed to record activity: ' . $e->getMessage(), [
                'action' => $action,
                'description' => $description,
            ]);
            return null;
        }
    }

    /**
     * Log authentication events.
     */
    public static function auth(string $action, string $description, string $status = 'info', array $context = []): ?ActivityLog
    {
        return self::log("auth.{$action}", $description, $status, $context);
    }

    /**
     * Log 2FA security events.
     */
    public static function twoFactor(string $action, string $description, string $status = 'info', array $context = []): ?ActivityLog
    {
        return self::log("2fa.{$action}", $description, $status, $context);
    }

    /**
     * Log vault unlock and access events.
     */
    public static function vault(string $vaultType, string $description, string $status = 'info', array $context = []): ?ActivityLog
    {
        return self::log("vault.{$vaultType}", $description, $status, $context);
    }

    /**
     * Log file lifecycle events.
     */
    public static function file(string $action, string $description, string $status = 'info', array $context = []): ?ActivityLog
    {
        return self::log("file.{$action}", $description, $status, $context);
    }

    /**
     * Log link operations.
     */
    public static function link(string $action, string $description, string $status = 'info', array $context = []): ?ActivityLog
    {
        return self::log("link.{$action}", $description, $status, $context);
    }

    /**
     * Log credential vault & JIT reveal events.
     */
    public static function password(string $action, string $description, string $status = 'info', array $context = []): ?ActivityLog
    {
        return self::log("password.{$action}", $description, $status, $context);
    }

    /**
     * Log Super Admin operations.
     */
    public static function admin(string $action, string $description, string $status = 'info', array $context = []): ?ActivityLog
    {
        return self::log("admin.{$action}", $description, $status, $context);
    }

    /**
     * Resolve true client IP taking proxies and Cloudflare into account.
     */
    public static function resolveClientIp(): string
    {
        $request = request();
        if (!$request) {
            return '127.0.0.1';
        }

        // Cloudflare Header
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        // Standard Forwarded Header
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        // Nginx / Reverse Proxy Header
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }

        return $request->ip() ?: '127.0.0.1';
    }

    /**
     * Parse User-Agent string to determine Device, OS, and Browser.
     */
    public static function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return ['device' => 'Unknown', 'os' => 'Unknown', 'browser' => 'Unknown'];
        }

        $ua = $userAgent;

        // Device
        $device = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/(mobile|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop)/i', $ua)) {
            $device = 'Mobile';
        } elseif (preg_match('/(bot|crawl|slurp|spider|curl|wget)/i', $ua)) {
            $device = 'Bot';
        }

        // OS
        $os = 'Unknown OS';
        if (preg_match('/windows nt 10/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6\.2/i', $ua)) {
            $os = 'Windows 8';
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/windows/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        }

        // Browser
        $browser = 'Unknown Browser';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/opera|opr/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/msie|trident/i', $ua)) {
            $browser = 'Internet Explorer';
        }

        return [
            'device' => $device,
            'os' => $os,
            'browser' => $browser,
        ];
    }
}
