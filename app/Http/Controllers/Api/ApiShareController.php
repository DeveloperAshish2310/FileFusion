<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileModal;
use App\Models\FileShare;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiShareController extends Controller
{
    /**
     * List all shares created by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $status = $request->query('status'); // 'all', 'active', 'expired', 'locked'

        $query = FileShare::where('user_id', $user->id)
            ->with(['file', 'recipient'])
            ->latest();

        if ($status === 'active') {
            $query->valid();
        }

        $shares = $query->paginate($request->integer('per_page', 20));

        $data = $shares->getCollection()->map(function ($share) {
            $isLocked = $share->hasReachedDownloadLimit() || $share->isExpired();
            return [
                'id' => encrypt($share->id),
                'file' => $share->file ? [
                    'id' => encrypt($share->file->id),
                    'name' => $share->file->name,
                    'size_bytes' => (int) $share->file->size,
                    'type' => $share->file->type,
                ] : null,
                'share_token' => $share->share_token,
                'share_type' => $share->share_type,
                'public_url' => url('/s/' . $share->share_token),
                'is_anonymous' => (bool) $share->is_anonymous,
                'is_password_protected' => $share->isPasswordProtected(),
                'max_downloads' => $share->max_downloads,
                'download_count' => (int) $share->download_count,
                'is_one_time' => ($share->max_downloads === 1),
                'is_locked' => $isLocked,
                'expires_at' => $share->expires_at?->toIso8601String(),
                'expires_at_formatted' => $share->expires_at ? $share->expires_at->format('M d, Y H:i') : 'Never',
                'recipient_email' => $share->recipient_email ?: ($share->recipient->email ?? null),
                'created_at' => $share->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $shares->currentPage(),
                'per_page' => $shares->perPage(),
                'total' => $shares->total(),
                'last_page' => $shares->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new share (Public Link, Private User, or Anonymous QR).
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'file_id' => 'required',
            'share_type' => 'nullable|in:public_link,private_user,anonymous_qr',
            'expires_in_minutes' => 'nullable|integer|min:1|max:525600', // 1 min up to 1 year
            'max_downloads' => 'nullable|integer|min:1|max:100000', // 1 for 1-time single use
            'password' => 'nullable|string|min:4|max:50',
            'is_anonymous' => 'nullable|boolean',
            'recipient_email' => 'nullable|email',
        ]);

        $realFileId = $this->resolveId($validated['file_id']);
        $file = FileModal::where('user_id', $user->id)->findOrFail($realFileId);

        $shareType = $validated['share_type'] ?? 'public_link';
        $recipientUserId = null;
        $recipientEmail = null;

        if ($shareType === 'private_user') {
            if (empty($validated['recipient_email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'recipient_email is required for private_user shares.',
                ], 422);
            }
            $recipientEmail = trim(strtolower($validated['recipient_email']));
            $recipient = User::where('email', $recipientEmail)->first();
            if ($recipient) {
                if ($recipient->id === $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot share a file privately with yourself.',
                    ], 422);
                }
                $recipientUserId = $recipient->id;
            }
        }

        $shareToken = Str::random(32);
        $expiresAt = !empty($validated['expires_in_minutes'])
            ? now()->addMinutes((int) $validated['expires_in_minutes'])
            : null;

        $share = FileShare::create([
            'file_id' => $file->id,
            'user_id' => $user->id,
            'share_token' => $shareToken,
            'share_type' => $shareType,
            'recipient_user_id' => $recipientUserId,
            'recipient_email' => $recipientEmail,
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : null,
            'expires_at' => $expiresAt,
            'max_downloads' => $validated['max_downloads'] ?? null,
            'download_count' => 0,
            'is_anonymous' => $request->boolean('is_anonymous', ($shareType === 'anonymous_qr')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Share link created successfully.',
            'share' => [
                'id' => encrypt($share->id),
                'file_id' => encrypt($file->id),
                'file_name' => $file->name,
                'share_token' => $shareToken,
                'share_type' => $share->share_type,
                'public_url' => url('/s/' . $shareToken),
                'direct_download_url' => url('/s/' . $shareToken . '/download'),
                'max_downloads' => $share->max_downloads,
                'is_one_time' => ($share->max_downloads === 1),
                'download_count' => 0,
                'expires_at' => $share->expires_at?->toIso8601String(),
                'expires_at_formatted' => $share->expires_at ? $share->expires_at->format('M d, Y H:i') : 'Never',
                'is_anonymous' => (bool) $share->is_anonymous,
                'is_password_protected' => !empty($validated['password']),
                'recipient_email' => $share->recipient_email,
            ],
        ], 201);
    }

    /**
     * Get single share details.
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $share = FileShare::where('user_id', $user->id)->with(['file', 'recipient'])->findOrFail($realId);

        return response()->json([
            'success' => true,
            'share' => [
                'id' => encrypt($share->id),
                'file' => $share->file ? [
                    'id' => encrypt($share->file->id),
                    'name' => $share->file->name,
                    'size_bytes' => (int) $share->file->size,
                    'type' => $share->file->type,
                ] : null,
                'share_token' => $share->share_token,
                'share_type' => $share->share_type,
                'public_url' => url('/s/' . $share->share_token),
                'direct_download_url' => url('/s/' . $share->share_token . '/download'),
                'max_downloads' => $share->max_downloads,
                'is_one_time' => ($share->max_downloads === 1),
                'download_count' => (int) $share->download_count,
                'is_locked' => ($share->hasReachedDownloadLimit() || $share->isExpired()),
                'expires_at' => $share->expires_at?->toIso8601String(),
                'expires_at_formatted' => $share->expires_at ? $share->expires_at->format('M d, Y H:i') : 'Never',
                'is_anonymous' => (bool) $share->is_anonymous,
                'is_password_protected' => $share->isPasswordProtected(),
                'recipient_email' => $share->recipient_email ?: ($share->recipient->email ?? null),
            ],
        ]);
    }

    /**
     * Update an active share's limits, duration, passcode, and permissions.
     */
    public function update($id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $share = FileShare::where('user_id', $user->id)->with('file')->findOrFail($realId);

        $validated = $request->validate([
            'share_type' => 'nullable|in:public_link,private_user,anonymous_qr',
            'expires_in_minutes' => 'nullable|integer|min:0|max:525600',
            'max_downloads' => 'nullable|integer|min:1|max:100000',
            'reset_download_count' => 'nullable|boolean',
            'password' => 'nullable|string|min:4|max:50',
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
            if ($recipient && $recipient->id !== $user->id) {
                $share->recipient_user_id = $recipient->id;
                $share->recipient_email = $recipient->email;
            }
        }

        if (isset($validated['expires_in_minutes'])) {
            $mins = (int) $validated['expires_in_minutes'];
            $share->expires_at = $mins > 0 ? now()->addMinutes($mins) : null;
        }

        if (array_key_exists('max_downloads', $validated)) {
            $share->max_downloads = $validated['max_downloads'] ?: null;
        }

        if (!empty($validated['reset_download_count'])) {
            $share->download_count = 0;
        }

        if (!empty($validated['clear_password'])) {
            $share->password = null;
        } elseif (!empty($validated['password'])) {
            $share->password = Hash::make($validated['password']);
        }

        $share->save();

        return response()->json([
            'success' => true,
            'message' => 'Share settings updated successfully.',
            'share' => [
                'id' => encrypt($share->id),
                'share_token' => $share->share_token,
                'share_type' => $share->share_type,
                'public_url' => url('/s/' . $share->share_token),
                'max_downloads' => $share->max_downloads,
                'download_count' => (int) $share->download_count,
                'is_one_time' => ($share->max_downloads === 1),
                'expires_at' => $share->expires_at?->toIso8601String(),
                'expires_at_formatted' => $share->expires_at ? $share->expires_at->format('M d, Y H:i') : 'Never',
                'is_anonymous' => (bool) $share->is_anonymous,
                'is_password_protected' => $share->isPasswordProtected(),
                'recipient_email' => $share->recipient_email,
            ],
        ]);
    }

    /**
     * Revoke / delete a share link.
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $share = FileShare::where('user_id', $user->id)->findOrFail($realId);

        $share->delete();

        return response()->json([
            'success' => true,
            'message' => 'Share access revoked successfully.',
        ]);
    }

    /**
     * Resolve encrypted or plain integer IDs.
     */
    protected function resolveId($id): int
    {
        if (is_numeric($id)) {
            return (int) $id;
        }

        try {
            return (int) decrypt($id);
        } catch (\Throwable $e) {
            try {
                return (int) \Illuminate\Support\Facades\Crypt::decrypt($id);
            } catch (\Throwable $ex) {
                try {
                    return (int) \App\Helpers\Encryptor::decrypt($id);
                } catch (\Throwable $ex2) {
                    abort(404, 'Invalid resource identifier.');
                }
            }
        }
    }
}
