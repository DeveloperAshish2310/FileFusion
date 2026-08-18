<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function settings()
    {
        return view('panel.settings');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'items_per_page' => 'nullable|integer|min:1|max:200',
            'vault_password' => 'nullable|string|min:4|confirmed',
            'vault_session_lifetime' => 'nullable|integer|min:0|max:86400',
            'hidden_files_session_lifetime' => 'nullable|integer|min:0|max:86400',
            'hidden_links_session_lifetime' => 'nullable|integer|min:0|max:86400',
            'hidden_passwords_session_lifetime' => 'nullable|integer|min:0|max:86400',
            'password_reveal_lifetime' => 'nullable|integer|min:0|max:7200',
        ]);

        try {
            // Update name if present
            if ($request->filled('name')) {
                $user->name = $validatedData['name'];
            }

            // Update items_per_page
            if ($request->has('items_per_page')) {
                $user->items_per_page = !empty($validatedData['items_per_page']) ? (int) $validatedData['items_per_page'] : null;
            }

            // Update dedicated vault lifetimes
            if ($request->has('hidden_files_session_lifetime')) {
                $user->hidden_files_session_lifetime = (int) $validatedData['hidden_files_session_lifetime'];
            }

            if ($request->has('hidden_links_session_lifetime')) {
                $user->hidden_links_session_lifetime = (int) $validatedData['hidden_links_session_lifetime'];
            }

            if ($request->has('hidden_passwords_session_lifetime')) {
                $user->hidden_passwords_session_lifetime = (int) $validatedData['hidden_passwords_session_lifetime'];
            }

            // Legacy / master vault session lifetime fallback
            if ($request->has('vault_session_lifetime')) {
                $user->vault_session_lifetime = (int) $validatedData['vault_session_lifetime'];
            }

            // Update password reveal lifetime
            if ($request->has('password_reveal_lifetime')) {
                $user->password_reveal_lifetime = (int) $validatedData['password_reveal_lifetime'];
            }

            // Update vault password if provided
            if (!empty($validatedData['vault_password'])) {
                $user->vault_pass = Hash::make($validatedData['vault_password']);
            }

            $user->save();

            return redirect()->route('panel.settings')->with('success', 'Settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Validate the password change request
        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            // Check if current password matches
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'The current password is incorrect.'])
                    ->withInput();
            }

            // Update the password
            $user->password = Hash::make($validatedData['new_password']);
            $user->save();

            return redirect()->route('panel.settings')->with('success', 'Password changed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to change password: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function updatePerPage(Request $request)
    {
        $request->validate([
            'per_page' => 'required|integer|min:1|max:200'
        ]);

        $perPage = (int) $request->per_page;
        \App\Helpers\SettingHelper::setItemsPerPage($perPage, true);

        return response()->json([
            'ok' => 1,
            'per_page' => $perPage,
            'message' => "Displaying {$perPage} items per page"
        ]);
    }

    /**
     * Upload and Encrypt Profile Picture with AES-256-GCM.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120', // Max 5MB
        ]);

        $user = Auth::user();
        $file = $request->file('avatar');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->withErrors(['avatar' => 'Please provide a valid image file.']);
        }

        // Check storage quota
        $oldAvatarBytes = 0;
        if (!empty($user->avatar)) {
            $oldPath = storage_path('app/' . $user->avatar);
            if (file_exists($oldPath)) {
                $oldAvatarBytes = filesize($oldPath);
            }
        }

        $estimatedNewBytes = $file->getSize() + 102; // Account for 102-byte encryption envelope header
        $netAdditionalBytes = max(0, $estimatedNewBytes - $oldAvatarBytes);

        if (!$user->hasEnoughStorage($netAdditionalBytes)) {
            $errorMsg = 'Insufficient storage quota available (' . $user->getStorageQuotaFormatted() . ' quota limit reached).';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => 0,
                    'message' => $errorMsg,
                ], 422);
            }
            return redirect()->back()->withErrors(['avatar' => $errorMsg]);
        }

        try {
            // Ensure storage/app/avatars directory exists
            $avatarsDir = storage_path('app/avatars');
            if (!file_exists($avatarsDir)) {
                mkdir($avatarsDir, 0755, true);
            }

            // Remove previous avatar file if exists
            if (!empty($user->avatar)) {
                $oldPath = storage_path('app/' . $user->avatar);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                if ($oldAvatarBytes > 0) {
                    $user->reduceStorageUsage($oldAvatarBytes);
                }
            }

            // Generate unique encrypted filename
            $tempPath = $file->getRealPath();
            $targetRelative = 'avatars/avatar_user_' . $user->id . '_' . bin2hex(random_bytes(8)) . '.enc';
            $targetFull = storage_path('app/' . $targetRelative);

            // Encrypt image with AES-256-GCM Envelope Encryption
            \App\Helpers\FileEncryptor::encryptFile($tempPath, $targetFull);

            // Add encrypted file size to user's storage quota
            $actualEncryptedSize = file_exists($targetFull) ? filesize($targetFull) : 0;
            if ($actualEncryptedSize > 0) {
                $user->addStorageUsage($actualEncryptedSize);
            }

            // Save encrypted path to user
            $user->avatar = $targetRelative;
            $user->save();

            \App\Services\AuditLogger::log('profile.avatar_updated', "Updated profile picture (Encrypted with AES-256-GCM, size: {$actualEncryptedSize} bytes)", 'success', [], $user);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => 1,
                    'message' => 'Profile picture encrypted and uploaded successfully!',
                    'avatar_url' => route('panel.user.avatar', $user->id) . '?v=' . time(),
                    'storage_used_formatted' => $user->getStorageUsedFormatted(),
                    'storage_percentage' => $user->getStorageUsagePercentage(),
                ]);
            }

            return redirect()->route('panel.profile')->with('success', 'Profile picture encrypted and updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['avatar' => 'Failed to encrypt and save avatar: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove Profile Picture.
     */
    public function removeAvatar(Request $request)
    {
        $user = Auth::user();

        if (!empty($user->avatar)) {
            $path = storage_path('app/' . $user->avatar);
            if (file_exists($path)) {
                $fileSize = filesize($path);
                @unlink($path);
                $user->reduceStorageUsage($fileSize);
            }
            $user->avatar = null;
            $user->save();
        }

        \App\Services\AuditLogger::log('profile.avatar_removed', "Removed profile picture", 'info', [], $user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Profile picture removed successfully.',
                'storage_used_formatted' => $user->getStorageUsedFormatted(),
                'storage_percentage' => $user->getStorageUsagePercentage(),
            ]);
        }

        return redirect()->route('panel.profile')->with('success', 'Profile picture removed.');
    }

    /**
     * Serve and Decrypt Profile Picture on the fly.
     */
    public function getAvatar($id = null)
    {
        $targetId = $id ? (int) $id : Auth::id();
        $user = \App\Models\User::find($targetId);

        if (!$user) {
            return $this->renderInitialsSvg('?');
        }

        $displayName = $user->nickname ?? $user->name ?? $user->username ?? 'Account';
        $initials = function_exists('getInitials') ? getInitials($displayName) : strtoupper(substr($displayName, 0, 2));

        if (empty($user->avatar)) {
            return $this->renderInitialsSvg($initials);
        }

        $fullPath = storage_path('app/' . $user->avatar);
        if (!file_exists($fullPath)) {
            return $this->renderInitialsSvg($initials);
        }

        try {
            // Decrypt image from AES-256-GCM envelope
            $decryptedBytes = \App\Helpers\FileEncryptor::decryptFileToString($fullPath);

            // Determine mime type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $decryptedBytes) ?: 'image/jpeg';
            finfo_close($finfo);

            return response($decryptedBytes, 200, [
                'Content-Type' => $mime,
                'Content-Length' => strlen($decryptedBytes),
                'Cache-Control' => 'private, max-age=86400, stale-while-revalidate=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Exception $e) {
            return $this->renderInitialsSvg($initials);
        }
    }

    /**
     * Render high-res SVG avatar with smooth gradient background and user initials.
     */
    private function renderInitialsSvg(string $initials)
    {
        $colors = ['#6366f1', '#4f46e5', '#3b82f6', '#10b981', '#8b5cf6', '#f59e0b'];
        $bg = $colors[crc32($initials) % count($colors)];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">
            <defs>
                <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="' . $bg . '" stop-opacity="0.9"/>
                    <stop offset="100%" stop-color="' . $bg . '"/>
                </linearGradient>
            </defs>
            <rect width="128" height="128" rx="64" fill="url(#g)"/>
            <text x="50%" y="54%" font-family="Inter, -apple-system, sans-serif" font-size="46" font-weight="700" fill="#ffffff" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars($initials) . '</text>
        </svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
