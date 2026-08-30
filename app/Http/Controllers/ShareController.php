<?php

namespace App\Http\Controllers;

use App\Helpers\FileEncryptor;
use App\Models\FileModal;
use App\Models\FileShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShareController extends Controller
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
     * Create private share with a specific registered user by email.
     */
    public function createPrivateShare(Request $request)
    {
        $request->validate([
            'file_id' => 'required',
            'email' => 'required|email',
        ]);

        $fileId = $this->resolveId($request->file_id);
        $file = FileModal::where('user_id', Auth::id())->findOrFail($fileId);

        $recipientEmail = trim(strtolower($request->email));
        $recipient = User::where('email', $recipientEmail)->first();

        if (!$recipient) {
            return response()->json([
                'ok' => 0,
                'info' => "No registered user found with email {$recipientEmail}."
            ], 404);
        }

        if ($recipient->id === Auth::id()) {
            return response()->json([
                'ok' => 0,
                'info' => 'You cannot share a file privately with yourself.'
            ], 400);
        }

        // Check if already shared
        $existing = FileShare::where('file_id', $file->id)
            ->where('recipient_user_id', $recipient->id)
            ->where('share_type', 'private_user')
            ->first();

        if ($existing) {
            return response()->json([
                'ok' => 0,
                'info' => "This file is already shared with {$recipient->name} ({$recipient->email})."
            ], 400);
        }

        $share = FileShare::create([
            'file_id' => $file->id,
            'user_id' => Auth::id(),
            'share_token' => Str::random(40),
            'share_type' => 'private_user',
            'recipient_user_id' => $recipient->id,
            'recipient_email' => $recipient->email,
            'is_anonymous' => false,
        ]);

        // Dispatch email notification to recipient
        \App\Helpers\MailHelper::sendNotification($recipient->email, 'file_shared', [
            'recipient_name' => $recipient->name,
            'owner_name' => Auth::user()->name ?? 'A FileFusion User',
            'file_name' => $file->name,
            'download_link' => route('panel.downloadFile', ['fileid' => encrypt($file->id)]),
        ]);


        return response()->json([
            'ok' => 1,
            'info' => "File shared privately with {$recipient->name} ({$recipient->email}).",
            'share' => [
                'id' => $share->id,
                'recipient_name' => $recipient->name,
                'recipient_email' => $recipient->email,
                'shared_at' => $share->created_at->format('M d, Y H:i'),
            ]
        ]);
    }

    /**
     * Create or update a Public or Anonymous QR share configuration.
     */
    public function createOrUpdatePublicShare(Request $request)
    {
        $request->validate([
            'file_id' => 'required',
            'share_type' => 'required|in:public_link,anonymous_qr',
            'is_enabled' => 'required|boolean',
            'password' => 'nullable|string|min:4|max:64',
            'expiry_hours' => 'nullable|integer',
            'max_downloads' => 'nullable|integer|min:1',
        ]);

        $fileId = $this->resolveId($request->file_id);
        $file = FileModal::where('user_id', Auth::id())->findOrFail($fileId);
        $shareType = $request->share_type;
        $isEnabled = $request->boolean('is_enabled');

        $share = FileShare::where('file_id', $file->id)
            ->where('share_type', $shareType)
            ->first();

        if (!$isEnabled) {
            if ($share) {
                $share->delete();
            }
            return response()->json([
                'ok' => 1,
                'is_active' => false,
                'info' => 'Public access disabled.'
            ]);
        }

        $expiresAt = null;
        if ($request->filled('expiry_hours') && (int) $request->expiry_hours > 0) {
            $expiresAt = now()->addHours((int) $request->expiry_hours);
        }

        $passwordHash = null;
        if ($request->filled('password')) {
            $passwordHash = Hash::make($request->password);
        } elseif ($share && $share->password) {
            // Keep existing password if not replacing
            $passwordHash = $share->password;
        }

        if (!$share) {
            $share = FileShare::create([
                'file_id' => $file->id,
                'user_id' => Auth::id(),
                'share_token' => Str::random(40),
                'share_type' => $shareType,
                'password' => $passwordHash,
                'expires_at' => $expiresAt,
                'max_downloads' => $request->max_downloads ?: null,
                'is_anonymous' => ($shareType === 'anonymous_qr'),
            ]);
        } else {
            $share->update([
                'password' => $passwordHash,
                'expires_at' => $expiresAt,
                'max_downloads' => $request->max_downloads ?: null,
                'is_anonymous' => ($shareType === 'anonymous_qr'),
            ]);
        }

        $shareUrl = appShareUrl('/s/' . $share->share_token);

        return response()->json([
            'ok' => 1,
            'is_active' => true,
            'share_url' => $shareUrl,
            'token' => $share->share_token,
            'is_password_protected' => $share->isPasswordProtected(),
            'expires_at' => $share->expires_at ? $share->expires_at->format('M d, Y H:i') : null,
            'max_downloads' => $share->max_downloads,
            'download_count' => $share->download_count,
            'info' => 'Share link updated successfully.'
        ]);
    }

    /**
     * Revoke any active share (Private, Public, or Anonymous).
     */
    public function revokeShare(Request $request, $id)
    {
        $share = FileShare::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $share->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => 1,
                'info' => 'Share access revoked successfully.'
            ]);
        }

        return redirect()->route('panel.sharedWithMe')->with('success', 'Share access revoked successfully.');
    }

    /**
     * Update existing share settings (expiration, download limits, passcode, anonymity, recipients).
     */
    public function updateShare(Request $request, $id)
    {
        $share = FileShare::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('file')
            ->firstOrFail();

        $validated = $request->validate([
            'share_type' => 'nullable|in:public_link,private_user,anonymous_qr',
            'expires_in_minutes' => 'nullable|integer|min:0',
            'expires_at' => 'nullable|date',
            'max_downloads' => 'nullable|integer|min:1',
            'reset_download_count' => 'nullable|boolean',
            'password' => 'nullable|string|min:1',
            'clear_password' => 'nullable|boolean',
            'is_anonymous' => 'nullable|boolean',
            'recipient_email' => 'nullable|email',
        ]);

        if (isset($validated['share_type'])) {
            $share->share_type = $validated['share_type'];
        }

        if (isset($validated['is_anonymous'])) {
            $share->is_anonymous = (bool) $validated['is_anonymous'];
        }

        if (!empty($validated['recipient_email']) && $share->share_type === 'private_user') {
            $recipient = User::where('email', trim(strtolower($validated['recipient_email'])))->first();
            if ($recipient && $recipient->id !== Auth::id()) {
                $share->recipient_user_id = $recipient->id;
                $share->recipient_email = $recipient->email;
            }
        }

        // Handle Expiration
        if (isset($validated['expires_in_minutes'])) {
            $mins = (int) $validated['expires_in_minutes'];
            $share->expires_at = $mins > 0 ? now()->addMinutes($mins) : null;
        } elseif (isset($validated['expires_in_hours']) && $validated['expires_in_hours'] !== 'keep') {
            $hours = (int) $validated['expires_in_hours'];
            $share->expires_at = $hours > 0 ? now()->addHours($hours) : null;
        } elseif (array_key_exists('expires_at', $validated)) {
            $share->expires_at = $validated['expires_at'] ? \Carbon\Carbon::parse($validated['expires_at']) : null;
        }

        // Handle Passcode
        if (!empty($validated['clear_password'])) {
            $share->password = null;
        } elseif (!empty($validated['password'])) {
            $share->password = Hash::make($validated['password']);
        }

        $share->save();

        return response()->json([
            'ok' => 1,
            'message' => 'Share settings updated successfully.',
            'share' => [
                'id' => $share->id,
                'share_token' => $share->share_token,
                'share_type' => $share->share_type,
                'public_url' => appShareUrl('/s/' . $share->share_token),
                'is_anonymous' => $share->is_anonymous,
                'expires_at' => $share->expires_at ? $share->expires_at->toIso8601String() : null,
                'expires_at_formatted' => $share->expires_at ? $share->expires_at->format('M d, Y H:i') : 'Never',
                'max_downloads' => $share->max_downloads,
                'download_count' => $share->download_count,
                'is_password_protected' => $share->isPasswordProtected(),
                'recipient_email' => $share->recipient_email,
            ]
        ]);
    }

    /**
     * Panel view: List files shared with the current authenticated user AND files shared by current user.
     */
    public function sharedWithMe(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $searchTerm = $request->get('q');

        // Check if vault is actively unlocked
        $sessionTimeout = $user ? $user->getVaultSessionLifetime() : 1800;
        $lastActivity = session('vault_group_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_links_last_activity') ?: session('hidden_passwords_last_activity');
        $isVaultAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
        $isVaultUnlocked = (bool) ($isVaultAuth && $lastActivity && (now()->timestamp - $lastActivity) <= $sessionTimeout);

        // 1. Files shared WITH current user
        $queryWithMe = FileShare::where('recipient_user_id', $userId)
            ->where('share_type', 'private_user')
            ->with(['file', 'owner'])
            ->whereHas('file', function($q) {
                $q->where('is_trashed', 0);
            });

        if ($searchTerm) {
            $queryWithMe->whereHas('file', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }
        $sharesWithMe = $queryWithMe->latest()->get();

        // 2. Files shared BY current user (Filter hidden files if vault is locked)
        $queryByMe = FileShare::where('user_id', $userId)
            ->with(['file', 'recipient'])
            ->whereHas('file', function($q) use ($isVaultUnlocked) {
                $q->where('is_trashed', 0);
                if (!$isVaultUnlocked) {
                    $q->where(function($sub) {
                        $sub->where('is_hidden', 0)->orWhereNull('is_hidden');
                    });
                }
            });

        if ($searchTerm) {
            $queryByMe->whereHas('file', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }
        $sharesByMe = $queryByMe->latest()->get();

        // 3. Bookmark links shared BY user (Filter hidden bookmarks if vault is locked)
        $linkQuery = \App\Models\LinkShare::where('user_id', $userId)
            ->with(['link', 'recipient'])
            ->whereHas('link', function($q) use ($isVaultUnlocked) {
                $q->where('is_trashed', 0);
                if (!$isVaultUnlocked) {
                    $q->where(function($sub) {
                        $sub->where('is_hidden', 0)->orWhereNull('is_hidden');
                    });
                }
            });

        if ($searchTerm) {
            $linkQuery->whereHas('link', function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%");
            });
        }
        $linkSharesByMe = $linkQuery->latest()->get();

        // 4. Password vault secrets shared BY user (Filter hidden secrets if vault is locked)
        $pwQuery = \App\Models\PasswordShare::where('user_id', $userId)
            ->with(['credential', 'recipient'])
            ->whereHas('credential', function($q) use ($isVaultUnlocked) {
                if (!$isVaultUnlocked) {
                    $q->where('is_hidden', false);
                }
            });

        if ($searchTerm) {
            $pwQuery->whereHas('credential', function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%");
            });
        }
        $passwordSharesByMe = $pwQuery->latest()->get();

        // 5. Category bundles shared BY user (Filter hidden categories if vault is locked)
        $catQuery = \App\Models\CategoryShare::where('user_id', $userId)
            ->with(['category', 'recipient'])
            ->whereHas('category', function($q) use ($isVaultUnlocked) {
                if (!$isVaultUnlocked) {
                    $q->where(function($sub) {
                        $sub->where('is_hidden', 0)->orWhereNull('is_hidden');
                    });
                }
            });

        if ($searchTerm) {
            $catQuery->whereHas('category', function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%");
            });
        }
        $categorySharesByMe = $catQuery->latest()->get();

        return view('panel.shared-with-me', compact(
            'sharesWithMe',
            'sharesByMe',
            'linkSharesByMe',
            'passwordSharesByMe',
            'categorySharesByMe',
            'searchTerm',
            'isVaultUnlocked'
        ));
    }


    /**
     * Public Guest Landing Page via token (scanned via QR code or clicked).
     */
    public function publicShareView($token)
    {
        $share = FileShare::where('share_token', $token)
            ->with(['file', 'owner'])
            ->first();

        if (!$share || !$share->file) {
            abort(404, 'Shared file not found or has been deleted.');
        }

        $isExpired = $share->isExpired();
        $isLimitReached = $share->hasReachedDownloadLimit();
        $isProtected = $share->isPasswordProtected();
        $file = $share->file;
        $shareUrl = appShareUrl('/s/' . $token);

        // Scrub owner metadata if anonymous mode
        $ownerName = $share->is_anonymous ? null : ($share->owner->name ?? 'File Fusion User');
        $ownerEmail = $share->is_anonymous ? null : ($share->owner->email ?? null);

        return view('public.share_download', compact(
            'share',
            'file',
            'token',
            'shareUrl',
            'isExpired',
            'isLimitReached',
            'isProtected',
            'ownerName',
            'ownerEmail'
        ));
    }

    /**
     * Public Guest File Download handler.
     */
    public function publicShareDownload($token, Request $request)
    {
        $share = FileShare::where('share_token', $token)
            ->with('file')
            ->first();

        if (!$share || !$share->file) {
            abort(404, 'Shared file not found.');
        }

        if ($share->isExpired()) {
            return response()->json([
                'ok' => 0,
                'info' => 'This share link has expired.'
            ], 410);
        }

        if ($share->hasReachedDownloadLimit()) {
            return response()->json([
                'ok' => 0,
                'info' => 'This share link has reached its maximum download limit.'
            ], 410);
        }

        // Check password if protected
        if ($share->isPasswordProtected()) {
            $passcode = $request->input('passcode');
            if (!$passcode || !$share->verifyPasscode($passcode)) {
                sleep(1);
                if ($request->ajax()) {
                    return response()->json([
                        'ok' => 0,
                        'info' => 'Incorrect passcode. Please try again.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Incorrect passcode.');
            }
        }

        $disk = 'local';
        $filePath = $share->file->path;

        if (!Storage::disk($disk)->exists($filePath)) {
            abort(404, 'Physical file not found in storage.');
        }

        // Increment download count
        $share->recordDownload();

        return FileEncryptor::streamDecrypted(
            $filePath,
            $share->file->name,
            $share->file->type ?? 'application/octet-stream',
            $disk
        );
    }
}
