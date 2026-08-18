<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwoFactorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    /**
     * Start 2FA Setup: Generate provisional secret key & QR code URI.
     */
    public function setup(Request $request)
    {
        $user = Auth::user();

        // Generate a new 16-character Base32 secret for setup
        $secret = TwoFactorService::generateSecretKey(16);

        // Store provisional secret in session
        session(['2fa_provisional_secret' => $secret]);

        $appName = config('app.name', 'FileFusion');
        $qrUri = TwoFactorService::getQrCodeUri($appName, $user->email, $secret);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => 1,
                'secret' => $secret,
                'qr_uri' => $qrUri,
                'email' => $user->email,
            ]);
        }

        return redirect()->route('panel.settings', ['setup_2fa' => 1])->with('setup_2fa_active', true);
    }

    /**
     * Confirm provisional code and activate Two-Factor Authentication.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();
        $secret = session('2fa_provisional_secret');

        if (empty($secret)) {
            return response()->json([
                'ok' => 0,
                'message' => '2FA setup session expired. Please open the setup wizard again.',
            ], 422);
        }

        // Verify the code against the provisional secret
        if (!TwoFactorService::verifyTotp($secret, $request->code)) {
            return response()->json([
                'ok' => 0,
                'message' => 'Invalid 6-digit verification code. Please check your Authenticator app and try again.',
            ], 422);
        }

        // Generate 8 recovery backup codes
        $recoveryCodes = TwoFactorService::generateRecoveryCodes(8);

        // Save to user
        $user->two_factor_enabled = true;
        $user->two_factor_secret = $secret;
        $user->two_factor_type = $request->input('type', 'authenticator');
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_enforce_login = true; // default enforce on login
        $user->save();

        session()->forget('2fa_provisional_secret');

        \App\Services\AuditLogger::twoFactor('enabled', "Two-Factor Authentication activated ({$user->two_factor_type})", 'success', [
            'type' => $user->two_factor_type,
            'recovery_codes_count' => count($recoveryCodes),
        ], $user);

        $formatted = TwoFactorService::getFormattedRecoveryCodes($user);

        return response()->json([
            'ok' => 1,
            'message' => 'Two-Factor Authentication has been successfully enabled on your account!',
            'recovery_codes' => $formatted['codes'],
            'active_count' => $formatted['active_count'],
            'used_count' => $formatted['used_count'],
            'total' => $formatted['total'],
        ]);
    }

    /**
     * Disable Two-Factor Authentication.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            \App\Services\AuditLogger::twoFactor('disable_failed', "Failed attempt to disable 2FA: incorrect password.", 'warning', [], $user);

            return response()->json([
                'ok' => 0,
                'message' => 'Incorrect account password. 2FA was not disabled.',
            ], 422);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_enforce_login = false;
        $user->two_factor_enforce_vault = false;
        $user->two_factor_enforce_password_reveal = false;
        $user->save();

        \App\Services\AuditLogger::twoFactor('disabled', "Two-Factor Authentication disabled.", 'danger', [], $user);

        return response()->json([
            'ok' => 1,
            'message' => 'Two-Factor Authentication has been disabled.',
        ]);
    }

    /**
     * Update 2FA Method & Enforcement preferences from User Settings.
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'two_factor_type' => 'nullable|string|in:authenticator,email,both',
            'enforce_login' => 'nullable|boolean',
            'enforce_vault' => 'nullable|boolean',
            'enforce_password_reveal' => 'nullable|boolean',
            'vault_session_lifetime' => 'nullable|integer|min:300|max:86400',
            'password_reveal_lifetime' => 'nullable|integer|min:0|max:86400',
        ]);

        $user = Auth::user();

        if ($request->has('two_factor_type')) {
            $user->two_factor_type = $request->input('two_factor_type', 'authenticator');
        }
        if ($request->has('enforce_login')) {
            $user->two_factor_enforce_login = $request->boolean('enforce_login', true);
        }
        if ($request->has('enforce_vault')) {
            $user->two_factor_enforce_vault = $request->boolean('enforce_vault', false);
        }
        if ($request->has('enforce_password_reveal')) {
            $user->two_factor_enforce_password_reveal = $request->boolean('enforce_password_reveal', false);
        }
        if ($request->has('vault_session_lifetime')) {
            $user->vault_session_lifetime = (int) $request->input('vault_session_lifetime', 1800);
        }
        if ($request->has('password_reveal_lifetime')) {
            $user->password_reveal_lifetime = (int) $request->input('password_reveal_lifetime', 900);
        }

        $user->save();

        \App\Services\AuditLogger::twoFactor('preferences_updated', "Updated security preferences: Type={$user->two_factor_type}, Vault Lifetime=" . round($user->getVaultSessionLifetime() / 60) . "m, Reveal Lifetime=" . round($user->getPasswordRevealLifetime() / 60) . "m", 'info', [
            'type' => $user->two_factor_type,
            'enforce_login' => $user->two_factor_enforce_login,
            'enforce_vault' => $user->two_factor_enforce_vault,
            'enforce_password_reveal' => $user->two_factor_enforce_password_reveal,
            'vault_session_lifetime' => $user->vault_session_lifetime,
            'password_reveal_lifetime' => $user->password_reveal_lifetime,
        ], $user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Security preferences and session durations updated successfully.',
                'vault_session_lifetime' => $user->getVaultSessionLifetime(),
                'password_reveal_lifetime' => $user->getPasswordRevealLifetime(),
            ]);
        }

        return redirect()->back()->with('success', 'Security preferences updated successfully.');
    }

    /**
     * Get formatted list of recovery codes with their active/used status.
     */
    public function getRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'ok' => 0,
                'message' => '2FA is not enabled on your account.',
            ], 400);
        }

        $formatted = TwoFactorService::getFormattedRecoveryCodes($user);

        \App\Services\AuditLogger::twoFactor('recovery_codes_viewed', "Viewed backup recovery codes ({$formatted['active_count']} active, {$formatted['used_count']} used)", 'info', [], $user);

        return response()->json([
            'ok' => 1,
            'data' => $formatted,
            'recovery_codes' => $formatted['codes'],
            'active_count' => $formatted['active_count'],
            'used_count' => $formatted['used_count'],
            'total' => $formatted['total'],
        ]);
    }

    /**
     * Regenerate new set of 8 recovery backup codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'ok' => 0,
                'message' => '2FA is not enabled on your account.',
            ], 400);
        }

        $codes = TwoFactorService::generateRecoveryCodes(8);
        $user->two_factor_recovery_codes = $codes;
        $user->save();

        $formatted = TwoFactorService::getFormattedRecoveryCodes($user);

        \App\Services\AuditLogger::twoFactor('recovery_codes_regenerated', "Regenerated a fresh set of 8 backup recovery codes.", 'warning', [
            'total' => 8,
        ], $user);

        return response()->json([
            'ok' => 1,
            'data' => $formatted,
            'recovery_codes' => $formatted['codes'],
            'active_count' => $formatted['active_count'],
            'used_count' => $formatted['used_count'],
            'total' => $formatted['total'],
            'message' => 'New recovery codes generated successfully. Please save them in a secure place.',
        ]);
    }

    /**
     * Send an Email OTP verification code to the authenticated or challenging user.
     */
    public function sendEmailOtp(Request $request)
    {
        $userId = session('2fa_user_id') ?: Auth::id();

        if (!$userId) {
            return response()->json([
                'ok' => 0,
                'message' => 'No active authentication session found.',
            ], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'ok' => 0,
                'message' => 'User not found.',
            ], 404);
        }

        $action = $request->input('action', 'Account Security Verification');

        $cooldownKey = "2fa_email_cooldown_{$user->id}";
        $cooldownExpiresAt = \Illuminate\Support\Facades\Cache::get($cooldownKey);
        if ($cooldownExpiresAt && time() < $cooldownExpiresAt) {
            $remaining = $cooldownExpiresAt - time();
            return response()->json([
                'ok' => 0,
                'message' => "Please wait {$remaining} seconds before requesting a new verification code.",
                'cooldown' => $remaining,
            ], 429);
        }

        $sent = TwoFactorService::sendEmailOtp($user, $action);

        if ($sent) {
            \Illuminate\Support\Facades\Cache::put($cooldownKey, time() + 60, 60);

            \App\Services\AuditLogger::twoFactor('email_otp_sent', "Email OTP sent to {$user->email} for '{$action}'", 'info', [
                'action' => $action,
            ], $user);

            return response()->json([
                'ok' => 1,
                'message' => "A new verification code has been sent to {$user->email}.",
                'cooldown' => 60,
            ]);
        }

        \App\Services\AuditLogger::twoFactor('email_otp_failed', "Failed to dispatch email OTP to {$user->email}", 'danger', [
            'action' => $action,
        ], $user);

        return response()->json([
            'ok' => 0,
            'message' => 'Failed to send verification email. Please check your SMTP configuration or use your Authenticator app.',
        ], 500);
    }

    /**
     * Render the 2FA Challenge screen during login.
     */
    public function challengeView()
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $cooldownKey = "2fa_email_cooldown_{$user->id}";
        $cooldownExpiresAt = \Illuminate\Support\Facades\Cache::get($cooldownKey);
        $cooldownRemaining = ($cooldownExpiresAt && time() < $cooldownExpiresAt) ? ($cooldownExpiresAt - time()) : 0;

        return view('auth.two-factor-challenge', [
            'user' => $user,
            'twoFactorType' => $user->two_factor_type ?? 'authenticator',
            'cooldownRemaining' => $cooldownRemaining,
        ]);
    }

    /**
     * Verify the 2FA Challenge code during login.
     */
    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => '2FA session expired. Please log in again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $code = trim($request->code);
        $result = TwoFactorService::verifyAny($user, $code);

        if ($result['success']) {
            $remember = session('2fa_remember', false);

            \App\Services\AuditLogger::twoFactor('login_challenge_success', "Login 2FA challenge verified successfully via {$result['method']}", 'success', [
                'method' => $result['method'],
            ], $user);

            // Log the user in
            Auth::login($user, $remember);

            // Clear 2FA session keys and cooldown cache
            session()->forget(['2fa_user_id', '2fa_remember']);
            \Illuminate\Support\Facades\Cache::forget("2fa_email_cooldown_{$user->id}");

            return redirect()->route('panel.dashboard')->with('success', 'Two-Factor Authentication verified. Welcome back!');
        }

        \App\Services\AuditLogger::twoFactor('login_challenge_failed', "Invalid 2FA challenge code submitted during sign in.", 'warning', [
            'email' => $user->email,
        ], $user);

        return redirect()->back()
            ->withErrors(['code' => 'Invalid verification code or recovery code. Please try again.'])
            ->withInput();
    }
}
