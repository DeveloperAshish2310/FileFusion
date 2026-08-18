<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function registerForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $defaultQuotaGb = (float) \App\Models\LandingPageSetting::get('sys_default_quota_gb', '25');
        $defaultQuotaBytes = (int) ($defaultQuotaGb * 1024 * 1024 * 1024);
        $userDirectory = $request->get('username') . '-' . Str::uuid()->toString();

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'username' => $request->get('username'),
            'role' => 'user',
            'account_type' => '2', // Standard User
            'status' => 1,
            'storage_quota' => $defaultQuotaBytes,
            'storage_used' => 0,
            'directory' => $userDirectory,
        ]);

        Auth::login($user, true); // login the user

        \App\Services\AuditLogger::auth('register', "New user registered: {$user->email} ({$user->name})", 'success', [
            'username' => $user->username,
            'role' => $user->role,
        ], $user);

        try {
            // Dispatch Welcome / Account Created Email Notification
            \App\Helpers\MailHelper::sendNotification($user->email, 'account_created', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'login_link' => route('login'),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't stop the registration process
            Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        return redirect(route("verification.notice"))->with('success', 'Registration successful! Please check your email to verify your account.');
    }


    public function loginForm()
    {
        return view('login');
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, true)) {
            $user = Auth::user();
            if (!$user->isActive()) {
                \App\Services\AuditLogger::auth('login.blocked', "Login attempt blocked: account is deactivated or suspended.", 'danger', [
                    'email' => $user->email,
                ], $user);

                Auth::logout();
                return redirect()->back()->withErrors(['email' => 'Your account has been deactivated or suspended. Please contact the Super Admin.'])->withInput($request->only('email'));
            }

            // Check if Two-Factor Authentication is enforced for login
            if ($user->requiresTwoFactorFor('login')) {
                $userId = $user->id;
                Auth::logout(); // Logout until 2FA code is confirmed

                session([
                    '2fa_user_id' => $userId,
                    '2fa_remember' => $request->boolean('remember', true),
                ]);

                \App\Services\AuditLogger::twoFactor('challenge_prompted', "Login credentials verified. Prompting for 2FA challenge code.", 'info', [
                    'type' => $user->two_factor_type,
                ], $user);

                // If user uses email 2FA, dispatch a fresh OTP for this new login session
                if ($user->two_factor_type === 'email' || $user->two_factor_type === 'both') {
                    \App\Services\TwoFactorService::sendEmailOtp($user, 'Account Login');
                    \Illuminate\Support\Facades\Cache::put("2fa_email_cooldown_{$user->id}", time() + 60, 60);
                }

                return redirect()->route('two-factor.challenge');
            }

            \App\Services\AuditLogger::auth('login.success', "User signed in successfully: {$user->email}", 'success', [], $user);

            return redirect(route("panel.dashboard"))->with('success', 'Great! You have Successfully logged in');
        }

        \App\Services\AuditLogger::auth('login.failed', "Failed login attempt for email: {$request->input('email')}", 'warning', [
            'attempted_email' => $request->input('email'),
        ]);

        return redirect()->back()->withErrors(['email' => 'Invalid email or password. Please try again.'])->withInput($request->only('email'));
    }


    public function logout()
    {
        $user = Auth::user();
        if ($user) {
            \App\Services\AuditLogger::auth('logout', "User signed out: {$user->email}", 'info', [], $user);
        }
        Auth::logout();
        return redirect('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Verify user's email address
     */
    public function verifyEmail(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Check if the hash matches
        if (! hash_equals((string) $request->route('hash'), sha1($user->email))) {
            return redirect('login')->withErrors(['email' => 'Invalid verification link.']);
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect(route('panel.dashboard'))->with('success', 'Your email is already verified!');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return redirect(route('panel.dashboard'))->with('success', 'Great! Your email has been successfully verified.');
    }

    /**
     * Show the email verification notice
     */
    public function verificationNotice()
    {
        return view('auth.verify-email');
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(route('panel.dashboard'));
        }

        try {
            Mail::to($request->user()->email)->send(new \App\Mail\WelcomeMail($request->user()));
            return back()->with('success', 'Verification email sent! Please check your inbox.');
        } catch (\Exception $e) {
            Log::error('Failed to resend verification email: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send verification email. Please try again later.']);
        }
    }

}
