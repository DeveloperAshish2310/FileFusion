<?php

namespace App\Http\Controllers;

use App\Helpers\MailHelper;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->input('email')));
        $user = User::where('email', $email)->first();

        if ($user) {
            $plainToken = Str::random(64);
            $tokenHash = hash('sha256', $plainToken);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $tokenHash,
                    'created_at' => Carbon::now(),
                ]
            );

            $resetLink = route('password.reset', ['token' => $plainToken, 'email' => $user->email]);

            try {
                MailHelper::sendPasswordReset($user, $resetLink, 1);
            } catch (\Exception $e) {
                Log::error('Failed sending password reset email: ' . $e->getMessage());
            }

            AuditLogger::auth('password.reset_requested', "Password reset link requested for {$user->email}", 'info', [
                'email' => $user->email,
            ], $user);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'If an account exists with that email, we have sent a password reset link.'
            ]);
        }

        return back()->with('status', 'If an account exists with that email, we have sent a password reset link.');
    }
}
