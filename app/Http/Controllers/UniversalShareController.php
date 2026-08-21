<?php

namespace App\Http\Controllers;

use App\Helpers\Encryptor;
use App\Models\Category;
use App\Models\CategoryShare;
use App\Models\FileModal;
use App\Models\LinkShare;
use App\Models\Links;
use App\Models\Password;
use App\Models\PasswordShare;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UniversalShareController extends Controller
{
    protected function resolveId($id): int
    {
        if (is_numeric($id)) {
            return (int) $id;
        }

        try {
            return (int) decrypt($id);
        } catch (\Throwable $e) {
            try {
                return (int) Crypt::decrypt($id);
            } catch (\Throwable $ex) {
                try {
                    return (int) Encryptor::decrypt($id);
                } catch (\Throwable $ex2) {
                    abort(404, 'Invalid asset identifier.');
                }
            }
        }
    }

    // =========================================================================
    // 1. LINK / BOOKMARK SHARING
    // =========================================================================

    public function createLinkShare(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'link_id' => 'required',
            'share_type' => 'nullable|in:public_link,private_user,anonymous_qr',
            'expires_in_minutes' => 'nullable|integer|min:1',
            'max_clicks' => 'nullable|integer|min:1',
            'password' => 'nullable|string|min:3|max:50',
            'is_anonymous' => 'nullable|boolean',
            'recipient_email' => 'nullable|email',
        ]);

        $realId = $this->resolveId($validated['link_id']);
        $link = Links::where('user_id', $user->id)->findOrFail($realId);

        $shareToken = Str::random(32);
        $expiresAt = !empty($validated['expires_in_minutes'])
            ? now()->addMinutes((int) $validated['expires_in_minutes'])
            : null;

        $recipientUserId = null;
        $recipientEmail = null;
        if (($validated['share_type'] ?? 'public_link') === 'private_user' && !empty($validated['recipient_email'])) {
            $recipientEmail = trim(strtolower($validated['recipient_email']));
            $rec = User::where('email', $recipientEmail)->first();
            if ($rec) $recipientUserId = $rec->id;
        }

        $share = LinkShare::create([
            'link_id' => $link->id,
            'user_id' => $user->id,
            'share_token' => $shareToken,
            'share_type' => $validated['share_type'] ?? 'public_link',
            'recipient_user_id' => $recipientUserId,
            'recipient_email' => $recipientEmail,
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : null,
            'expires_at' => $expiresAt,
            'max_clicks' => $validated['max_clicks'] ?? null,
            'click_count' => 0,
            'is_anonymous' => (bool) ($validated['is_anonymous'] ?? false),
        ]);

        return response()->json([
            'ok' => 1,
            'message' => 'Bookmark share link created successfully.',
            'share' => [
                'id' => encrypt($share->id),
                'share_token' => $shareToken,
                'public_url' => url('/s/l/' . $shareToken),
                'max_clicks' => $share->max_clicks,
                'expires_at' => $share->expires_at?->format('M d, Y H:i'),
                'is_password_protected' => !empty($validated['password']),
                'is_anonymous' => (bool) $share->is_anonymous,
            ]
        ]);
    }

    public function publicLinkView(Request $request, $token)
    {
        $share = LinkShare::where('share_token', $token)->with(['link', 'owner'])->firstOrFail();

        if ($share->isExpired() || $share->hasReachedClickLimit()) {
            return view('public.share_expired', [
                'title' => 'Link Expired or Limit Reached',
                'message' => 'This shared link is no longer accessible because its time limit or maximum click count has been reached.',
            ]);
        }

        // Check passcode
        if ($share->isPasswordProtected() && !$request->session()->get('link_unlocked_' . $share->id)) {
            if ($request->isMethod('POST')) {
                $passcode = $request->input('passcode');
                if ($share->verifyPassword($passcode)) {
                    $request->session()->put('link_unlocked_' . $share->id, true);
                } else {
                    return back()->with('error', 'Incorrect passcode PIN. Please try again.');
                }
            } else {
                return view('public.share_passcode_gate', [
                    'shareType' => 'Bookmark Link',
                    'actionUrl' => url('/s/l/' . $token),
                    'isAnonymous' => $share->is_anonymous,
                ]);
            }
        }

        // Increment click count
        $share->increment('click_count');

        $link = $share->link;
        $decryptedTitle = Encryptor::decrypt($link->title) ?: 'Saved Bookmark';
        $decryptedUrl = Encryptor::decrypt($link->url) ?: '#';
        $decryptedDesc = Encryptor::decrypt($link->description) ?: '';

        return view('public.share_link_view', compact('share', 'link', 'decryptedTitle', 'decryptedUrl', 'decryptedDesc'));
    }

    // =========================================================================
    // 2. PASSWORD VAULT SHARING (ZERO-KNOWLEDGE / BURN-AFTER-READING)
    // =========================================================================

    public function createPasswordShare(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'password_id' => 'required',
            'account_password' => 'nullable|string',
            'share_type' => 'nullable|in:public_link,private_user,anonymous_qr',
            'expires_in_minutes' => 'nullable|integer|min:1',
            'burn_after_reading' => 'nullable|boolean',
            'password' => 'nullable|string|min:4|max:50',
            'is_anonymous' => 'nullable|boolean',
        ]);

        if (!empty($validated['account_password'])) {
            if (!Hash::check($validated['account_password'], $user->password)) {
                return response()->json([
                    'ok' => 0,
                    'message' => 'Incorrect account password. Please enter your valid login password to authorize sharing.',
                ], 403);
            }
        }

        $realId = $this->resolveId($validated['password_id']);
        $pw = Password::where('user_id', $user->id)->findOrFail($realId);

        // Snapshot current secret payload into encrypted state
        $payload = [
            'title' => Encryptor::decrypt($pw->title),
            'username' => Encryptor::decrypt($pw->username),
            'password' => $pw->password, // access plaintext via model getter
            'url' => Encryptor::decrypt($pw->url),
            'notes' => Encryptor::decrypt($pw->notes),
            'auth_fields' => $pw->auth_fields,
            'shared_at' => now()->toIso8601String(),
        ];

        $shareToken = Str::random(36);
        $expiresAt = !empty($validated['expires_in_minutes'])
            ? now()->addMinutes((int) $validated['expires_in_minutes'])
            : now()->addHours(24);

        $share = PasswordShare::create([
            'password_id' => $pw->id,
            'user_id' => $user->id,
            'share_token' => $shareToken,
            'share_type' => $validated['share_type'] ?? 'public_link',
            'encrypted_payload' => Crypt::encryptString(json_encode($payload)),
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : null,
            'expires_at' => $expiresAt,
            'max_reveals' => 1,
            'reveal_count' => 0,
            'burn_after_reading' => (bool) ($validated['burn_after_reading'] ?? true),
            'is_anonymous' => (bool) ($validated['is_anonymous'] ?? true),
        ]);

        return response()->json([
            'ok' => 1,
            'message' => 'Zero-knowledge secret share generated.',
            'share' => [
                'id' => encrypt($share->id),
                'share_token' => $shareToken,
                'public_url' => url('/s/v/' . $shareToken),
                'burn_after_reading' => (bool) $share->burn_after_reading,
                'expires_at' => $share->expires_at?->format('M d, Y H:i'),
                'is_password_protected' => !empty($validated['password']),
            ]
        ]);
    }

    public function publicVaultView(Request $request, $token)
    {
        $share = PasswordShare::where('share_token', $token)->first();

        if (!$share || $share->isExpired() || $share->hasReachedRevealLimit() || empty($share->encrypted_payload)) {
            return view('public.share_expired', [
                'title' => 'Secret Self-Destructed or Expired',
                'message' => 'This credential was configured to self-destruct upon reading or has expired. The decrypted secret payload has been completely purged and cannot be recovered.',
            ]);
        }

        return view('public.share_vault_view', compact('share'));
    }

    public function publicVaultReveal(Request $request, $token): JsonResponse
    {
        $share = PasswordShare::where('share_token', $token)->firstOrFail();

        if ($share->isExpired() || $share->hasReachedRevealLimit() || empty($share->encrypted_payload)) {
            return response()->json([
                'ok' => 0,
                'info' => 'This secret has expired or has already been burned after reading.',
            ], 410);
        }

        if ($share->isPasswordProtected()) {
            $passcode = $request->input('passcode');
            if (!$share->verifyPassword($passcode)) {
                return response()->json([
                    'ok' => 0,
                    'info' => 'Incorrect passcode PIN. Please try again.',
                ], 403);
            }
        }

        try {
            $decryptedJson = Crypt::decryptString($share->encrypted_payload);
            $payload = json_decode($decryptedJson, true);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => 0,
                'info' => 'Failed to decrypt secret payload: ' . $e->getMessage(),
            ], 500);
        }

        // Increment reveal count
        $share->increment('reveal_count');

        // Burn payload if configured
        if ($share->burn_after_reading) {
            $share->encrypted_payload = null;
            $share->save();
        }

        return response()->json([
            'ok' => 1,
            'payload' => $payload,
            'is_burned' => (bool) $share->burn_after_reading,
            'message' => 'Secret decrypted successfully.',
        ]);
    }

    // =========================================================================
    // 3. CATEGORY BUNDLE SHARING
    // =========================================================================

    public function createCategoryShare(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'category_id' => 'required',
            'share_type' => 'nullable|in:public_link,private_user,anonymous_qr',
            'expires_in_minutes' => 'nullable|integer|min:1',
            'max_views' => 'nullable|integer|min:1',
            'include_files' => 'nullable|boolean',
            'include_links' => 'nullable|boolean',
            'include_passwords' => 'nullable|boolean',
            'password' => 'nullable|string|min:4|max:50',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $realId = $this->resolveId($validated['category_id']);
        $cat = Category::where('user_id', $user->id)->findOrFail($realId);

        $shareToken = Str::random(32);
        $expiresAt = !empty($validated['expires_in_minutes'])
            ? now()->addMinutes((int) $validated['expires_in_minutes'])
            : null;

        $share = CategoryShare::create([
            'category_id' => $cat->id,
            'user_id' => $user->id,
            'share_token' => $shareToken,
            'share_type' => $validated['share_type'] ?? 'public_link',
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : null,
            'expires_at' => $expiresAt,
            'max_views' => $validated['max_views'] ?? null,
            'view_count' => 0,
            'include_files' => (bool) ($validated['include_files'] ?? true),
            'include_links' => (bool) ($validated['include_links'] ?? true),
            'include_passwords' => (bool) ($validated['include_passwords'] ?? false),
            'is_anonymous' => (bool) ($validated['is_anonymous'] ?? false),
        ]);

        return response()->json([
            'ok' => 1,
            'message' => 'Category bundle share portal generated.',
            'share' => [
                'id' => encrypt($share->id),
                'share_token' => $shareToken,
                'public_url' => url('/s/c/' . $shareToken),
                'category_name' => $cat->title,
                'expires_at' => $share->expires_at?->format('M d, Y H:i'),
                'is_password_protected' => !empty($validated['password']),
            ]
        ]);
    }

    public function publicCategoryView(Request $request, $token)
    {
        $share = CategoryShare::where('share_token', $token)->with(['category', 'owner'])->firstOrFail();

        if ($share->isExpired() || $share->hasReachedViewLimit()) {
            return view('public.share_expired', [
                'title' => 'Category Bundle Expired',
                'message' => 'This shared category portal has reached its maximum view limit or has expired.',
            ]);
        }

        // Check passcode
        if ($share->isPasswordProtected() && !$request->session()->get('cat_unlocked_' . $share->id)) {
            if ($request->isMethod('POST')) {
                $passcode = $request->input('passcode');
                if ($share->verifyPassword($passcode)) {
                    $request->session()->put('cat_unlocked_' . $share->id, true);
                } else {
                    return back()->with('error', 'Incorrect passcode PIN.');
                }
            } else {
                return view('public.share_passcode_gate', [
                    'shareType' => 'Category Bundle',
                    'actionUrl' => url('/s/c/' . $token),
                    'isAnonymous' => $share->is_anonymous,
                ]);
            }
        }

        $share->increment('view_count');

        $cat = $share->category;

        $links = $share->include_links
            ? Links::where('user_id', $share->user_id)->where('is_trashed', 0)->where('category_id', $cat->id)->get()->map(function ($l) {
                $l->title = is_string($l->title) ? Encryptor::decrypt($l->title) : $l->title;
                $l->url = is_string($l->url) ? Encryptor::decrypt($l->url) : $l->url;
                $l->description = is_string($l->description) ? Encryptor::decrypt($l->description) : $l->description;
                return $l;
            })
            : collect();

        $files = collect();

        return view('public.share_category_view', compact('share', 'cat', 'files', 'links'));
    }

    public function updateLinkShare(Request $request, $id): JsonResponse
    {
        $realId = $this->resolveId($id);
        $share = LinkShare::where('user_id', Auth::id())->findOrFail($realId);

        $validated = $request->validate([
            'expires_in_minutes' => 'nullable',
            'max_clicks' => 'nullable|integer|min:1',
            'reset_click_count' => 'nullable|boolean',
            'password' => 'nullable|string|min:4|max:50',
            'clear_password' => 'nullable|boolean',
            'is_anonymous' => 'nullable|boolean',
        ]);

        if (isset($validated['is_anonymous'])) {
            $share->is_anonymous = (bool) $validated['is_anonymous'];
        }

        if (isset($validated['expires_in_minutes']) && $validated['expires_in_minutes'] !== 'keep') {
            $mins = (int) $validated['expires_in_minutes'];
            $share->expires_at = $mins > 0 ? now()->addMinutes($mins) : null;
        }

        if (array_key_exists('max_clicks', $validated)) {
            $share->max_clicks = $validated['max_clicks'] ?: null;
        }

        if (!empty($validated['reset_click_count'])) {
            $share->click_count = 0;
        }

        if (!empty($validated['clear_password'])) {
            $share->password = null;
        } elseif (!empty($validated['password'])) {
            $share->password = Hash::make($validated['password']);
        }

        $share->save();

        return response()->json([
            'ok' => 1,
            'message' => 'Bookmark link share settings updated successfully.',
            'share' => [
                'id' => $share->id,
                'share_token' => $share->share_token,
                'public_url' => url('/s/l/' . $share->share_token),
                'max_clicks' => $share->max_clicks,
                'click_count' => $share->click_count,
                'expires_at' => $share->expires_at?->format('M d, Y H:i'),
                'is_password_protected' => !empty($share->password),
                'is_anonymous' => $share->is_anonymous,
            ]
        ]);
    }

    public function updatePasswordShare(Request $request, $id): JsonResponse
    {
        $realId = $this->resolveId($id);
        $share = PasswordShare::where('user_id', Auth::id())->findOrFail($realId);

        $validated = $request->validate([
            'expires_in_minutes' => 'nullable',
            'password' => 'nullable|string|min:4|max:50',
            'clear_password' => 'nullable|boolean',
        ]);

        if (isset($validated['expires_in_minutes']) && $validated['expires_in_minutes'] !== 'keep') {
            $mins = (int) $validated['expires_in_minutes'];
            $share->expires_at = $mins > 0 ? now()->addMinutes($mins) : null;
        }

        if (!empty($validated['clear_password'])) {
            $share->password = null;
        } elseif (!empty($validated['password'])) {
            $share->password = Hash::make($validated['password']);
        }

        $share->save();

        return response()->json([
            'ok' => 1,
            'message' => 'Password secret share settings updated successfully.',
            'share' => [
                'id' => $share->id,
                'share_token' => $share->share_token,
                'public_url' => url('/s/v/' . $share->share_token),
                'expires_at' => $share->expires_at?->format('M d, Y H:i'),
                'is_password_protected' => !empty($share->password),
            ]
        ]);
    }

    public function updateCategoryShare(Request $request, $id): JsonResponse
    {
        $realId = $this->resolveId($id);
        $share = CategoryShare::where('user_id', Auth::id())->findOrFail($realId);

        $validated = $request->validate([
            'expires_in_minutes' => 'nullable',
            'max_views' => 'nullable|integer|min:1',
            'reset_view_count' => 'nullable|boolean',
            'include_files' => 'nullable|boolean',
            'include_links' => 'nullable|boolean',
            'password' => 'nullable|string|min:4|max:50',
            'clear_password' => 'nullable|boolean',
        ]);

        if (isset($validated['expires_in_minutes']) && $validated['expires_in_minutes'] !== 'keep') {
            $mins = (int) $validated['expires_in_minutes'];
            $share->expires_at = $mins > 0 ? now()->addMinutes($mins) : null;
        }

        if (array_key_exists('max_views', $validated)) {
            $share->max_views = $validated['max_views'] ?: null;
        }

        if (isset($validated['include_files'])) {
            $share->include_files = (bool) $validated['include_files'];
        }

        if (isset($validated['include_links'])) {
            $share->include_links = (bool) $validated['include_links'];
        }

        if (!empty($validated['reset_view_count'])) {
            $share->view_count = 0;
        }

        if (!empty($validated['clear_password'])) {
            $share->password = null;
        } elseif (!empty($validated['password'])) {
            $share->password = Hash::make($validated['password']);
        }

        $share->save();

        return response()->json([
            'ok' => 1,
            'message' => 'Category bundle share settings updated successfully.',
            'share' => [
                'id' => $share->id,
                'share_token' => $share->share_token,
                'public_url' => url('/s/c/' . $share->share_token),
                'max_views' => $share->max_views,
                'view_count' => $share->view_count,
                'expires_at' => $share->expires_at?->format('M d, Y H:i'),
                'is_password_protected' => !empty($share->password),
            ]
        ]);
    }

    public function revokeLinkShare($id)
    {
        $realId = $this->resolveId($id);
        $share = LinkShare::where('user_id', Auth::id())->findOrFail($realId);
        $share->delete();

        return response()->json(['ok' => 1, 'message' => 'Link share revoked successfully.']);
    }

    public function revokePasswordShare($id)
    {
        $realId = $this->resolveId($id);
        $share = PasswordShare::where('user_id', Auth::id())->findOrFail($realId);
        $share->delete();

        return response()->json(['ok' => 1, 'message' => 'Password secret share revoked successfully.']);
    }

    public function revokeCategoryShare($id)
    {
        $realId = $this->resolveId($id);
        $share = CategoryShare::where('user_id', Auth::id())->findOrFail($realId);
        $share->delete();

        return response()->json(['ok' => 1, 'message' => 'Category bundle share revoked successfully.']);
    }
}
