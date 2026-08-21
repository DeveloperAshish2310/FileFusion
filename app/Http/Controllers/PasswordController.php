<?php

namespace App\Http\Controllers;

use App\Helpers\Encryptor;
use App\Models\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    /**
     * Resolve ID if passed as numeric or encrypted string.
     */
    private function resolveId($id)
    {
        if (is_numeric($id)) {
            return (int) $id;
        }

        try {
            return (int) decrypt($id);
        } catch (\Exception $e) {
            try {
                return (int) Crypt::decrypt($id);
            } catch (\Exception $ex) {
                return (int) $id;
            }
        }
    }

    /**
     * Generate an ephemeral, salted morph token bound to user ID for JIT reveals.
     */
    public static function createRevealToken(int $passwordId): string
    {
        $salt = Str::random(16);
        $userId = Auth::id() ?: 0;
        $payload = "v1:{$salt}:{$passwordId}:{$userId}";
        return Encryptor::encrypt($payload);
    }

    /**
     * Resolve and validate an ephemeral salted morph reveal token or encrypted ID.
     */
    public static function resolveRevealToken($token): ?int
    {
        if (empty($token) || $token === 'undefined' || $token === 'null') {
            return null;
        }

        try {
            $decrypted = Encryptor::decrypt($token);
            if ($decrypted && str_starts_with($decrypted, 'v1:')) {
                $parts = explode(':', $decrypted);
                if (count($parts) >= 4) {
                    [$version, $salt, $passwordId, $userId] = $parts;
                    if ((int)$userId === (int)Auth::id()) {
                        return (int)$passwordId;
                    }
                }
                return null;
            }
        } catch (\Exception $e) {}

        // Fallback for numeric ID (for backward-compat or direct ID)
        if (is_numeric($token)) {
            return (int)$token;
        }

        try {
            return (int) decrypt($token);
        } catch (\Exception $e) {
            try {
                return (int) Crypt::decrypt($token);
            } catch (\Exception $ex) {
                return null;
            }
        }
    }

    /**
     * Format full model attributes for edit form views with decrypted fields.
     */
    private function formatPassword(Password $password): Password
    {
        $password->title = is_string($password->title) ? (Encryptor::decrypt($password->title) ?? '') : ($password->title ?? '');
        $password->username = is_string($password->username) ? (Encryptor::decrypt($password->username) ?? '') : ($password->username ?? '');
        $password->url = is_string($password->url) ? (Encryptor::decrypt($password->url) ?? '') : ($password->url ?? '');
        $password->password = is_string($password->password) ? (Encryptor::decrypt($password->password) ?? '') : ($password->password ?? '');
        $password->notes = is_string($password->notes) ? (Encryptor::decrypt($password->notes) ?? '') : ($password->notes ?? '');

        $rawAuthFields = $password->auth_fields;
        if (is_string($rawAuthFields)) {
            $rawAuthFields = Encryptor::decrypt($rawAuthFields);
        }

        $decoded = is_array($rawAuthFields)
            ? $rawAuthFields
            : (is_string($rawAuthFields) ? json_decode($rawAuthFields, true) : []);

        $password->auth_fields = is_array($decoded) ? $decoded : [];

        return $password;
    }

    /**
     * Format model attributes for list views without exposing plaintext secrets in the DOM.
     */
    private function formatPasswordForList(Password $password): Password
    {
        $password->title = is_string($password->title) ? (Encryptor::decrypt($password->title) ?? '') : ($password->title ?? '');
        $password->username = is_string($password->username) ? (Encryptor::decrypt($password->username) ?? '') : ($password->username ?? '');
        $password->url = is_string($password->url) ? (Encryptor::decrypt($password->url) ?? '') : ($password->url ?? '');
        $password->notes = is_string($password->notes) ? (Encryptor::decrypt($password->notes) ?? '') : ($password->notes ?? '');
        $password->reveal_token = self::createRevealToken($password->id);

        $rawAuthFields = $password->auth_fields;
        if (is_string($rawAuthFields)) {
            $rawAuthFields = Encryptor::decrypt($rawAuthFields);
        }

        $decoded = is_array($rawAuthFields)
            ? $rawAuthFields
            : (is_string($rawAuthFields) ? json_decode($rawAuthFields, true) : []);

        $maskedFields = [];
        if (is_array($decoded)) {
            foreach ($decoded as $idx => $f) {
                $maskedFields[] = [
                    'index' => $idx,
                    'label' => $f['label'] ?? '',
                    'type' => $f['type'] ?? 'text',
                    'is_secret' => in_array(strtolower($f['type'] ?? ''), ['password', 'secret', 'pin', '2fa', 'token']),
                ];
            }
        }
        $password->auth_fields = $maskedFields;

        return $password;
    }

    // ---------------------------------------------------------------- screens

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $query = Password::where('user_id', Auth::id())
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $passwords = $query->map(fn($p) => $this->formatPasswordForList($p));

        if ($search !== '') {
            $searchLower = strtolower($search);
            $passwords = $passwords->filter(function ($p) use ($searchLower) {
                if (str_contains(strtolower($p->title), $searchLower)) return true;
                if (str_contains(strtolower($p->username), $searchLower)) return true;
                if (str_contains(strtolower($p->url), $searchLower)) return true;
                if (str_contains(strtolower($p->notes), $searchLower)) return true;

                foreach ($p->auth_fields as $f) {
                    if (str_contains(strtolower($f['label'] ?? ''), $searchLower)) return true;
                }

                return false;
            })->values();
        }

        return view('panel.passwords', compact('passwords', 'search'));
    }

    public function create()
    {
        return view('panel.addpassword');
    }

    public function edit($id)
    {
        $id = $this->resolveId($id);
        $raw = Password::where('user_id', Auth::id())->where('id', $id)->first();

        if (!$raw) {
            return redirect()->route('panel.passwords')->with('error', 'Credential not found.');
        }

        $password = $this->formatPassword($raw);

        return view('panel.addpassword', compact('password'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'password' => 'nullable|string',
            'notes' => 'nullable|string',
            'isHidden' => 'nullable',
            'auth_labels' => 'nullable|array',
            'auth_values' => 'nullable|array',
        ]);

        $authFields = [];
        if (!empty($validated['auth_labels']) && !empty($validated['auth_values'])) {
            foreach ($validated['auth_labels'] as $index => $label) {
                $value = $validated['auth_values'][$index] ?? '';
                $trimmedLabel = trim((string) $label);
                $trimmedVal = trim((string) $value);
                if ($trimmedLabel !== '' || $trimmedVal !== '') {
                    $authFields[] = [
                        'label' => $trimmedLabel,
                        'value' => $trimmedVal,
                    ];
                }
            }
        }

        Password::create([
            'user_id' => Auth::id(),
            'title' => Encryptor::encrypt($validated['title']),
            'username' => Encryptor::encrypt($validated['username'] ?? ''),
            'url' => Encryptor::encrypt($validated['url'] ?? ''),
            'password' => Encryptor::encrypt($validated['password'] ?? ''),
            'notes' => Encryptor::encrypt($validated['notes'] ?? ''),
            'auth_fields' => !empty($authFields) ? Encryptor::encrypt(json_encode($authFields)) : null,
            'is_hidden' => $request->has('isHidden') && $request->isHidden == '1',
        ]);

        return redirect()->route('panel.passwords')->with('success', 'Credential saved successfully!');
    }

    public function update(Request $request, $id)
    {
        $id = $this->resolveId($id);
        $password = Password::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'password' => 'nullable|string',
            'notes' => 'nullable|string',
            'isHidden' => 'nullable',
            'auth_labels' => 'nullable|array',
            'auth_values' => 'nullable|array',
        ]);

        $authFields = [];
        if (!empty($validated['auth_labels']) && !empty($validated['auth_values'])) {
            foreach ($validated['auth_labels'] as $index => $label) {
                $value = $validated['auth_values'][$index] ?? '';
                $trimmedLabel = trim((string) $label);
                $trimmedVal = trim((string) $value);
                if ($trimmedLabel !== '' || $trimmedVal !== '') {
                    $authFields[] = [
                        'label' => $trimmedLabel,
                        'value' => $trimmedVal,
                    ];
                }
            }
        }

        $password->update([
            'title' => Encryptor::encrypt($validated['title']),
            'username' => Encryptor::encrypt($validated['username'] ?? ''),
            'url' => Encryptor::encrypt($validated['url'] ?? ''),
            'password' => Encryptor::encrypt($validated['password'] ?? ''),
            'notes' => Encryptor::encrypt($validated['notes'] ?? ''),
            'auth_fields' => !empty($authFields) ? Encryptor::encrypt(json_encode($authFields)) : null,
            'is_hidden' => $request->has('isHidden') && $request->isHidden == '1',
        ]);

        return redirect()->route('panel.passwords')->with('success', 'Credential updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $id = $this->resolveId($id);
        $password = Password::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $password->delete();

        if ($request->ajax()) {
            return response()->json(['ok' => 1, 'info' => 'Credential moved to trash']);
        }

        return redirect()->back()->with('success', 'Credential moved to trash');
    }

    public function toggleHide(Request $request)
    {
        $id = $this->resolveId($request->get('password_id'));
        $password = Password::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

        $password->is_hidden = !$password->is_hidden;
        $password->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'is_hidden' => $password->is_hidden,
                'message' => $password->is_hidden ? 'Credential hidden' : 'Credential unhidden'
            ]);
        }

        return redirect()->back()->with('success', $password->is_hidden ? 'Credential marked as hidden' : 'Credential unhidden');
    }

    // ------------------------------------------------------------ hidden vault

    public function hiddenLogin()
    {
        $user = Auth::user();
        if ($user) {
            $sessionTimeout = $user->getVaultSessionLifetime();
            $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
            $lastActivity = session('vault_group_last_activity') ?: session('hidden_passwords_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_links_last_activity');
            if ($isAuth && $lastActivity && (now()->timestamp - $lastActivity) <= $sessionTimeout) {
                return redirect()->route('panel.hiddenPasswords');
            }
        }

        return view('panel.hidden-passwords-login');
    }

    public function hiddenAuth(Request $request)
    {
        $request->validate([
            'vault_pass' => 'nullable|string',
            'code' => 'nullable|string',
        ]);

        $user = Auth::user();
        $input = trim((string)($request->input('code') ?: $request->input('vault_pass')));

        if (empty($input)) {
            return redirect()->back()->with('error', 'Please enter your verification code or vault password.');
        }

        $isValid = false;

        // 1. Check TOTP / 2FA / Recovery Code
        if ($user->hasTwoFactorEnabled()) {
            $totpCheck = \App\Services\TwoFactorService::verifyAny($user, $input);
            if ($totpCheck['success']) {
                $isValid = true;
            }
        }

        // 2. Check Vault Password
        if (!$isValid && !empty($user->vault_pass)) {
            if (password_verify($input, $user->vault_pass)) {
                $isValid = true;
            }
        }

        // 3. Fallback: check account password
        if (!$isValid && empty($user->vault_pass)) {
            if (\Illuminate\Support\Facades\Hash::check($input, $user->password)) {
                $isValid = true;
            }
        }

        if ($isValid) {
            \App\Services\AuditLogger::vault('passwords.unlock_success', "Unlocked Hidden Passwords vault successfully.", 'success', [], $user);

            // Establish unified vault group authentication across all vaults (Files, Links, Passwords)
            $now = now()->timestamp;
            session([
                'vault_group_authenticated' => true,
                'vault_group_last_activity' => $now,
                'hidden_files_authenticated' => true,
                'hidden_files_last_activity' => $now,
                'hidden_links_authenticated' => true,
                'hidden_links_last_activity' => $now,
                'hidden_passwords_authenticated' => true,
                'hidden_passwords_last_activity' => $now,
                'password_reveal_authenticated' => time(),
            ]);

            return redirect()->route('panel.hiddenPasswords');
        }

        \App\Services\AuditLogger::vault('passwords.unlock_failed', "Failed attempt to unlock Hidden Passwords vault.", 'warning', [], $user);

        sleep(1);

        return redirect()->back()->with('error', 'Invalid verification code or vault password. Please try again.');
    }

    public function hidden(Request $request = null)
    {
        $user = Auth::user();
        $sessionTimeout = $user ? $user->getHiddenPasswordsSessionLifetime() : 1800;

        // Check if session is authenticated across vault group
        $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
        if (!$isAuth) {
            return redirect()->route('panel.hiddenPasswordsLogin');
        }

        // Check if session has expired according to user's configured lifetime
        $lastActivity = session('vault_group_last_activity') ?: session('hidden_passwords_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_links_last_activity');
        if (!$lastActivity || (now()->timestamp - $lastActivity) > $sessionTimeout) {
            session()->forget([
                'vault_group_authenticated', 'vault_group_last_activity',
                'hidden_files_authenticated', 'hidden_files_last_activity',
                'hidden_links_authenticated', 'hidden_links_last_activity',
                'hidden_passwords_authenticated', 'hidden_passwords_last_activity'
            ]);
            return redirect()->route('panel.hiddenPasswordsLogin')
                ->with('error', 'Vault session expired due to inactivity. Please enter your passcode again.');
        }

        // Update last activity timestamp across vault group
        $now = now()->timestamp;
        session([
            'vault_group_authenticated' => true,
            'vault_group_last_activity' => $now,
            'hidden_files_authenticated' => true,
            'hidden_files_last_activity' => $now,
            'hidden_links_authenticated' => true,
            'hidden_links_last_activity' => $now,
            'hidden_passwords_authenticated' => true,
            'hidden_passwords_last_activity' => $now,
        ]);

        $passwords = Password::where('user_id', Auth::id())
            ->where('is_hidden', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => $this->formatPasswordForList($p));

        $remainingTime = max(0, $sessionTimeout - (now()->timestamp - $now));

        return view('panel.hidden-passwords', compact('passwords', 'remainingTime'));
    }

    public function extendHiddenPasswordsSession()
    {
        $isAuth = session('vault_group_authenticated') || session('hidden_passwords_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated');
        if (!$isAuth) {
            return response()->json(['ok' => 0, 'code' => 401, 'info' => 'Not authenticated', 'remaining_time' => 0]);
        }

        $user = Auth::user();
        $sessionTimeout = $user ? $user->getHiddenPasswordsSessionLifetime() : 1800;

        $now = now()->timestamp;
        session([
            'hidden_passwords_authenticated' => true,
            'hidden_passwords_last_activity' => $now,
            'vault_group_last_activity' => $now,
        ]);

        return response()->json([
            'ok' => 1,
            'code' => 200,
            'success' => true,
            'remaining_time' => $sessionTimeout
        ]);
    }

    public function logoutHidden()
    {
        session()->forget([
            'vault_group_authenticated', 'vault_group_last_activity',
            'hidden_files_authenticated', 'hidden_files_last_activity',
            'hidden_links_authenticated', 'hidden_links_last_activity',
            'hidden_passwords_authenticated', 'hidden_passwords_last_activity',
            'password_reveal_authenticated'
        ]);
        session()->save();
        return redirect()->route('panel.hiddenPasswordsLogin')
            ->with('success', 'Vault session closed and locked.');
    }

    /**
     * Verify 2FA or Password for JIT Secret Reveal.
     */
    public function verifyRevealAuth(Request $request)
    {
        $user = Auth::user();
        $input = trim((string)($request->input('code') ?: $request->input('password') ?: $request->input('vault_pass')));

        if (empty($input)) {
            return response()->json([
                'ok' => 0,
                'info' => 'Please enter your verification code or password.'
            ], 422);
        }

        $isValid = false;

        // 1. Check TOTP / 2FA / Recovery Code
        if ($user->hasTwoFactorEnabled()) {
            $totpCheck = \App\Services\TwoFactorService::verifyAny($user, $input);
            if ($totpCheck['success']) {
                $isValid = true;
            }
        }

        // 2. Check Vault Password
        if (!$isValid && !empty($user->vault_pass)) {
            if (password_verify($input, $user->vault_pass)) {
                $isValid = true;
            }
        }

        // 3. Check Account Password
        if (!$isValid) {
            if (\Illuminate\Support\Facades\Hash::check($input, $user->password)) {
                $isValid = true;
            }
        }

        if ($isValid) {
            \App\Services\AuditLogger::password('reveal_auth.verified', "Authenticated credential reveal session.", 'success', [], $user);

            $now = time();
            $revealTimeout = $user->getPasswordRevealLifetime();
            if ($revealTimeout === 0) {
                // If immediate / ask always, grant a 60-second single use window to decrypt the immediate action
                session(['password_reveal_single_use' => $now]);
            } else {
                session(['password_reveal_authenticated' => $now]);
            }

            return response()->json([
                'ok' => 1,
                'info' => 'Authentication verified. Credentials unlocked.',
                'reveal_lifetime' => $revealTimeout,
            ]);
        }

        \App\Services\AuditLogger::password('reveal_auth.failed', "Failed credential reveal authentication attempt.", 'warning', [], $user);

        return response()->json([
            'ok' => 0,
            'info' => 'Invalid verification code or password. Please try again.'
        ], 401);
    }

    /**
     * Secure JIT On-Demand Reveal API for passwords and secret fields.
     */
    public function revealSecret(Request $request, $token)
    {
        $resolvedId = self::resolveRevealToken($token);
        if (!$resolvedId) {
            return response()->json([
                'ok' => 0,
                'info' => 'Invalid or expired reveal token.'
            ], 403);
        }

        $user = Auth::user();
        $revealTimeout = $user->getPasswordRevealLifetime();
        $vaultTimeout = $user->getVaultSessionLifetime();

        $isVaultUnlocked = (bool) (
            (session('vault_group_authenticated') || session('hidden_passwords_authenticated')) &&
            session('vault_group_last_activity') &&
            ($vaultTimeout > 0) &&
            (time() - session('vault_group_last_activity') <= $vaultTimeout)
        );

        $isRevealAuthenticated = (bool) (
            (($revealTimeout > 0) && session('password_reveal_authenticated') && (time() - session('password_reveal_authenticated') <= $revealTimeout)) ||
            ($isVaultUnlocked && $revealTimeout > 0)
        );

        $password = Password::where('user_id', Auth::id())->findOrFail($resolvedId);

        // If password is in the hidden vault and vault is not active, require vault passcode
        if ($password->is_hidden && !$isVaultUnlocked) {
            return response()->json([
                'ok' => 0,
                'code' => 'REQUIRES_2FA',
                'info' => 'Hidden vault is locked. Please enter your vault passcode to view this credential.',
                'has_totp' => $user->hasTwoFactorEnabled(),
                'has_vault_pass' => !empty($user->vault_pass),
                'reveal_lifetime' => $revealTimeout,
            ], 403);
        }

        // If reveal setting is Immediate (0 / Prompt Every Time) or session expired, challenge for authentication
        if (!$isRevealAuthenticated) {
            // Check if there was a fresh one-time verification token in session
            $singleUseAuth = session('password_reveal_single_use');
            if ($revealTimeout === 0 && $singleUseAuth && (time() - $singleUseAuth <= 60)) {
                // Consume single use token
                session()->forget('password_reveal_single_use');
            } else {
                \App\Services\AuditLogger::password('reveal_denied', "Password reveal blocked: authentication required.", 'info', [
                    'password_id' => $resolvedId,
                ], $user);

                return response()->json([
                    'ok' => 0,
                    'code' => 'REQUIRES_2FA',
                    'info' => !empty($user->vault_pass)
                        ? 'Please enter your vault passcode to view this password.'
                        : 'Please enter your password to view this credential.',
                    'has_totp' => $user->hasTwoFactorEnabled(),
                    'has_vault_pass' => !empty($user->vault_pass),
                    'reveal_lifetime' => $revealTimeout,
                ], 403);
            }
        }

        // If reveal timeout is 0 (Immediate / Ask Always), forget any persistent auth
        if ($revealTimeout === 0) {
            session()->forget(['password_reveal_authenticated', 'password_reveal_single_use']);
        }

        if ($password->is_hidden) {
            session([
                'vault_group_last_activity' => time(),
                'hidden_passwords_last_activity' => time(),
            ]);
        }

        $fieldIndex = $request->input('field_index');

        $cleanTitle = Encryptor::decrypt($password->title) ?: ('Credential #' . $password->id);
        $cleanUrl = Encryptor::decrypt($password->url) ?: $password->url;

        if ($fieldIndex !== null && is_numeric($fieldIndex)) {
            $rawAuthFields = Encryptor::decrypt($password->auth_fields);
            $fields = $rawAuthFields ? json_decode($rawAuthFields, true) : [];
            $secret = $fields[(int)$fieldIndex]['value'] ?? '';
            $fieldName = $fields[(int)$fieldIndex]['label'] ?? 'Field #' . $fieldIndex;

            \App\Services\AuditLogger::password('revealed', "Decrypted secret field '{$fieldName}' for credential: {$cleanTitle}", 'success', [
                'password_id' => $password->id,
                'title' => $cleanTitle,
                'field_label' => $fieldName,
            ], $user);
        } else {
            $secret = Encryptor::decrypt($password->password) ?? '';

            \App\Services\AuditLogger::password('revealed', "Decrypted confidential password for: {$cleanTitle}", 'success', [
                'password_id' => $password->id,
                'title' => $cleanTitle,
                'url' => $cleanUrl,
            ], $user);
        }

        return response()->json([
            'ok' => 1,
            'secret' => $secret
        ]);
    }

    // -------------------------------------------------------- trash operations

    public function restore(Request $request, $id)
    {
        try {
            $id = $this->resolveId($id);
            $password = Password::where('user_id', Auth::id())
                ->onlyTrashed()
                ->where('id', $id)
                ->firstOrFail();

            $password->restore();

            if ($request->ajax()) {
                return response()->json([
                    'ok' => 1,
                    'code' => 200,
                    'info' => 'Credential restored successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Credential restored successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'ok' => 0,
                    'code' => 400,
                    'info' => 'Failed to restore credential: ' . $e->getMessage()
                ], 400);
            }
            return redirect()->back()->with('error', 'Failed to restore credential');
        }
    }

    public function permanentDelete(Request $request, $id)
    {
        try {
            $id = $this->resolveId($id);
            $password = Password::where('user_id', Auth::id())
                ->onlyTrashed()
                ->where('id', $id)
                ->firstOrFail();

            $password->forceDelete();

            if ($request->ajax()) {
                return response()->json([
                    'ok' => 1,
                    'code' => 200,
                    'info' => 'Credential permanently deleted'
                ]);
            }

            return redirect()->back()->with('success', 'Credential permanently deleted');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'ok' => 0,
                    'code' => 400,
                    'info' => 'Failed to delete credential: ' . $e->getMessage()
                ], 400);
            }
            return redirect()->back()->with('error', 'Failed to delete credential');
        }
    }

    public function emptyTrash(Request $request)
    {
        try {
            $trashed = Password::where('user_id', Auth::id())
                ->onlyTrashed()
                ->get();

            $count = $trashed->count();

            foreach ($trashed as $item) {
                $item->forceDelete();
            }

            if ($request->ajax()) {
                return response()->json([
                    'ok' => 1,
                    'code' => 200,
                    'deleted_count' => $count,
                    'info' => "All {$count} credentials permanently deleted from trash"
                ]);
            }

            return redirect()->back()->with('success', "All {$count} credentials permanently deleted from trash");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'ok' => 0,
                    'code' => 400,
                    'info' => 'Failed to empty trash: ' . $e->getMessage()
                ], 400);
            }
            return redirect()->back()->with('error', 'Failed to empty trash');
        }
    }

    /**
     * Bulk import mapped credentials from Excel / CSV
     */
    public function importBatch(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.username' => 'nullable|string|max:255',
            'items.*.password' => 'nullable|string',
            'items.*.url' => 'nullable|string|max:2048',
            'items.*.notes' => 'nullable|string',
        ]);

        $items = $request->input('items', []);
        $importedCount = 0;
        $userId = Auth::id();

        foreach ($items as $item) {
            $title = trim($item['title'] ?? '');
            if (empty($title)) continue;

            $url = trim($item['url'] ?? '');
            if (!empty($url) && !preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }

            Password::create([
                'user_id' => $userId,
                'title' => $title,
                'username' => trim($item['username'] ?? ''),
                'url' => $url,
                'password' => Encryptor::encrypt(trim($item['password'] ?? '')),
                'notes' => trim($item['notes'] ?? ''),
                'auth_fields' => null,
                'is_hidden' => false,
            ]);

            $importedCount++;
        }

        return response()->json([
            'success' => true,
            'imported_count' => $importedCount,
            'message' => "Successfully imported {$importedCount} credentials into your vault!"
        ]);
    }

    /**
     * Export credentials with selected custom fields
     */
    public function exportData(Request $request)
    {
        $request->validate([
            'fields' => 'required|array|min:1',
            'format' => 'required|in:csv,json,xlsx'
        ]);

        $selectedFields = $request->input('fields', []);
        $format = $request->input('format', 'csv');

        $passwords = Password::where('user_id', Auth::id())->get();

        $rows = [];
        foreach ($passwords as $item) {
            $row = [];
            if (in_array('title', $selectedFields)) {
                $row['Title'] = $item->title ?: '';
            }
            if (in_array('username', $selectedFields)) {
                $row['Username/Email'] = $item->username ?: '';
            }
            if (in_array('password', $selectedFields)) {
                $row['Password'] = Encryptor::decrypt($item->password) ?: '';
            }
            if (in_array('url', $selectedFields)) {
                $row['URL'] = $item->url ?: '';
            }
            if (in_array('notes', $selectedFields)) {
                $row['Notes'] = $item->notes ?: '';
            }
            if (in_array('created_at', $selectedFields)) {
                $row['Created Date'] = $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '';
            }
            $rows[] = $row;
        }

        if ($format === 'json' || $format === 'xlsx') {
            return response()->json([
                'success' => true,
                'headers' => array_keys($rows[0] ?? []),
                'rows' => $rows,
                'filename' => 'Passwords_Vault_Export_' . date('Y-m-d_His')
            ]);
        }

        // CSV Direct Stream
        $headers = array_keys($rows[0] ?? ['Title', 'Username/Email', 'Password', 'URL']);
        $callback = function() use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM for Excel
            fputcsv($file, $headers);
            foreach ($rows as $r) {
                fputcsv($file, array_values($r));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Passwords_Vault_Export_' . date('Y-m-d_His') . '.csv"',
        ]);
    }
}
