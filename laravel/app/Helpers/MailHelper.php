<?php

namespace App\Helpers;

use App\Models\LandingPageSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    /**
     * Dynamically configure Laravel Mailer at runtime using Admin CMS settings.
     */
    public static function configureMail(array $overrides = []): void
    {
        LandingPageSetting::seedDefaults();

        $mailer = $overrides['mail_mailer'] ?? LandingPageSetting::get('mail_mailer', 'log');
        $host = $overrides['mail_host'] ?? LandingPageSetting::get('mail_host', 'smtp.gmail.com');
        $port = $overrides['mail_port'] ?? LandingPageSetting::get('mail_port', '587');
        $username = $overrides['mail_username'] ?? LandingPageSetting::get('mail_username', '');
        $password = $overrides['mail_password'] ?? LandingPageSetting::get('mail_password', '');
        $encryption = $overrides['mail_encryption'] ?? LandingPageSetting::get('mail_encryption', 'tls');
        $fromAddress = $overrides['mail_from_address'] ?? LandingPageSetting::get('mail_from_address', 'noreply@filefusion.io');
        $fromName = $overrides['mail_from_name'] ?? LandingPageSetting::get('mail_from_name', 'FileFusion Workspace');

        Config::set('mail.default', $mailer);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);

        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $host,
                'port' => (int) $port,
                'encryption' => ($encryption === 'none' || empty($encryption)) ? null : $encryption,
                'username' => $username,
                'password' => $password,
                'timeout' => 15,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ]);
        }

        Mail::purge();
    }


    /**
     * Send a notification email using a template event type and variables.
     */
    public static function sendNotification(string $toEmail, string $eventType, array $variables = []): bool
    {
        try {
            self::configureMail();

            $subjectKey = "email_tpl_{$eventType}_subject";
            $bodyKey = "email_tpl_{$eventType}_body";

            $rawSubject = LandingPageSetting::get($subjectKey, 'FileFusion Notification');
            $rawBody = LandingPageSetting::get($bodyKey, '<p>You have a new notification from FileFusion.</p>');

            $subject = self::replacePlaceholders($rawSubject, $variables);
            $bodyContent = self::replacePlaceholders($rawBody, $variables);

            $fullHtml = self::wrapHtmlTemplate($subject, $bodyContent);

            Mail::html($fullHtml, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)->subject($subject);
            });

            Log::info("MailHelper: Sent '{$eventType}' email to {$toEmail}");
            return true;
        } catch (\Throwable $e) {
            Log::error("MailHelper Exception sending '{$eventType}' email to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replace template placeholders `{var_name}` with array values.
     */
    public static function replacePlaceholders(string $content, array $variables): string
    {
        foreach ($variables as $key => $val) {
            $content = str_replace('{' . $key . '}', (string) $val, $content);
        }
        return $content;
    }

    /**
     * Wrap email content in a clean, responsive HTML email layout.
     */
    public static function wrapHtmlTemplate(string $title, string $bodyContent): string
    {
        $appName = LandingPageSetting::get('sys_app_name', 'FileFusion');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{$title}</title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }
        @media (prefers-color-scheme: dark) {
            .email-bg { background-color: #0f172a !important; }
            .email-card { background-color: #1e293b !important; border-color: #334155 !important; }
            .email-title { color: #ffffff !important; }
            .email-text { color: #cbd5e1 !important; }
            .email-footer { background-color: #0f172a !important; border-color: #334155 !important; color: #64748b !important; }
        }
    </style>
</head>
<body class="email-bg" style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" class="email-bg" style="background-color: #f8fafc; padding: 40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" class="email-card" style="max-width: 580px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 24px 32px; border-bottom: 1px solid #f1f5f9; background-color: #ffffff;">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td>
                                        <span class="email-title" style="font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px;">{$appName}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="email-text" style="padding: 32px; font-size: 15px; line-height: 1.6; color: #334155;">
                            {$bodyContent}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer" style="padding: 20px 32px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5;">
                            © {$appName} Workspace · All rights reserved.<br>
                            This is an automated transactional notification.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }


    /**
     * Send a test SMTP verification email with optional runtime overrides.
     */
    public static function testSmtpConnection(string $targetEmail, array $overrides = []): array
    {
        try {
            self::configureMail($overrides);

            $mailer = Config::get('mail.default');
            $host = Config::get('mail.mailers.smtp.host');
            $port = Config::get('mail.mailers.smtp.port');

            $appName = LandingPageSetting::get('sys_app_name', 'FileFusion');
            $subject = "🧪 SMTP Connection Test - {$appName}";
            $body = "<p>Hello!</p><p>This is a test notification confirming that your SMTP email configuration for <strong>{$appName}</strong> is working cleanly.</p><p><strong>Active Mailer:</strong> {$mailer}<br><strong>SMTP Endpoint:</strong> {$host}:{$port}</p><p><strong>Sent at:</strong> " . now()->format('Y-m-d H:i:s T') . "</p>";

            $fullHtml = self::wrapHtmlTemplate($subject, $body);

            Mail::html($fullHtml, function ($message) use ($targetEmail, $subject) {
                $message->to($targetEmail)->subject($subject);
            });

            return [
                'success' => true,
                'message' => "Test email successfully sent to {$targetEmail} via {$mailer} ({$host}:{$port}).",
            ];
        } catch (\Throwable $e) {
            $errDetail = $e->getMessage();
            $hint = "";
            $lowMsg = strtolower($errDetail);

            if (str_contains($errDetail, '535') || str_contains($lowMsg, 'authentication') || str_contains($lowMsg, 'credentials')) {
                $hint = "\n💡 Diagnostic Hint: Authentication failed. Please check your SMTP Username and Password. If using Gmail / Google Workspace, generate a 16-character App Password at https://myaccount.google.com/apppasswords (do NOT use your normal account password).";
            } elseif (str_contains($lowMsg, 'connection could not be established') || str_contains($errDetail, '110') || str_contains($lowMsg, 'timeout') || str_contains($lowMsg, 'refused')) {
                $hint = "\n💡 Diagnostic Hint: Unable to connect to host. Verify your SMTP Hostname, Port (587 for TLS, 465 for SSL), and ensure your hosting server firewall allows outbound mail connections.";
            } elseif (str_contains($lowMsg, 'certificate') || str_contains($lowMsg, 'ssl') || str_contains($lowMsg, 'stream_socket_enable_crypto')) {
                $hint = "\n💡 Diagnostic Hint: SSL/TLS handshake failed. Try toggling Encryption between TLS and SSL, or check if your SMTP server requires Port 587 (TLS) or Port 465 (SSL).";
            }

            Log::error("MailHelper SMTP Test Failed: {$errDetail}");

            return [
                'success' => false,
                'message' => "SMTP Dispatch Error: {$errDetail}{$hint}",
            ];
        }
    }


    /**
     * Send TOTP 2FA Security Code.
     */
    public static function sendTotpCode(\App\Models\User $user, string $code, int $expiresMinutes = 10): bool
    {
        return self::sendNotification($user->email, 'totp', [
            'user_name' => $user->name,
            'totp_code' => $code,
            'expires_minutes' => $expiresMinutes,
        ]);
    }

    /**
     * Send Password Reset Link.
     */
    public static function sendPasswordReset(\App\Models\User $user, string $resetLink, int $expiresHours = 1): bool
    {
        return self::sendNotification($user->email, 'forgot_pass', [
            'user_name' => $user->name,
            'reset_link' => $resetLink,
            'expires_hours' => $expiresHours,
        ]);
    }

    /**
     * Send Storage Usage Alert (75%, 90%, 100%).
     */
    public static function sendStorageAlert(\App\Models\User $user, float $percentage): bool
    {
        return self::sendNotification($user->email, 'storage_alert', [
            'user_name' => $user->name,
            'used_storage' => $user->getStorageUsedFormatted(),
            'quota_storage' => $user->getStorageQuotaFormatted(),
            'percentage' => round($percentage, 1),
        ]);
    }

    /**
     * Send Trash Retention Warning Email.
     */
    public static function sendTrashExpiringAlert(\App\Models\User $user, int $fileCount, string $deletionDate): bool
    {
        return self::sendNotification($user->email, 'trash_expiring', [
            'user_name' => $user->name,
            'file_count' => $fileCount,
            'deletion_date' => $deletionDate,
        ]);
    }
}

