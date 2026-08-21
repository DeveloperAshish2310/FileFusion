<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $email = strtolower(trim($request->input('email')));
        $token = $request->input('token');
        $tokenHash = hash('sha256', $token);

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || $record->token !== $tokenHash) {
            return back()->withErrors(['email' => 'This password reset link is invalid or has already been used.'])->withInput($request->only('email'));
        }

        if (Carbon::parse($record->created_at)->addHours(1)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withErrors(['email' => 'This password reset link has expired. Please request a new one.'])->withInput($request->only('email'));
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Unable to find a user with this email address.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        AuditLogger::auth('password.reset_success', "Password successfully reset for {$user->email}", 'success', [
            'email' => $user->email,
        ], $user);

        return redirect()->route('login')->with('success', 'Your password has been reset successfully! You can now log in.');
    }
}
