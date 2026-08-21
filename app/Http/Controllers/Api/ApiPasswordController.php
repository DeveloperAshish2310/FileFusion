<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Password;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiPasswordController extends Controller
{
    /**
     * List user's stored credentials (passwords masked by default).
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $token = $request->attributes->get('api_token');
        $canReveal = $token && $token->can('vault:reveal');
        $includePasswords = $canReveal && $request->boolean('include_passwords', false);

        $query = Password::where('user_id', $user->id);

        if ($request->boolean('only_hidden')) {
            $query->where('is_hidden', 1);
        } elseif (!$request->boolean('include_hidden')) {
            $query->where('is_hidden', 0);
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $passwords = $query->orderBy('id', 'desc')->paginate($perPage);

        $search = strtolower(trim($request->query('search', $request->query('q', ''))));

        $items = collect($passwords->items())->map(function ($item) use ($includePasswords) {
            $decryptedPass = \App\Helpers\Encryptor::decrypt($item->password) ?: $item->password;
            return [
                'id' => encrypt($item->id),
                'title' => \App\Helpers\Encryptor::decrypt($item->title) ?: $item->title,
                'username' => \App\Helpers\Encryptor::decrypt($item->username) ?: $item->username,
                'url' => \App\Helpers\Encryptor::decrypt($item->url) ?: $item->url,
                'password' => $includePasswords ? $decryptedPass : '••••••••',
                'notes' => \App\Helpers\Encryptor::decrypt($item->notes) ?: $item->notes,
                'is_hidden' => (bool) $item->is_hidden,
                'has_custom_fields' => !empty($item->auth_fields),
                'created_at' => $item->created_at?->toIso8601String(),
                'updated_at' => $item->updated_at?->toIso8601String(),
            ];
        });

        if (!empty($search)) {
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['title'] ?? ''), $search)
                    || str_contains(strtolower($item['username'] ?? ''), $search)
                    || str_contains(strtolower($item['url'] ?? ''), $search);
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $passwords->currentPage(),
                'last_page' => $passwords->lastPage(),
                'per_page' => $passwords->perPage(),
                'total' => $passwords->total(),
                'passwords_unmasked' => $includePasswords,
            ],
        ]);
    }

    /**
     * JIT Reveal a single credential password (requires vault:reveal ability).
     */
    public function reveal($id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $token = $request->attributes->get('api_token');

        if ($token && $token->cant('vault:reveal')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Token lacks vault:reveal scope required for password decryption.',
            ], 403);
        }

        $realId = $this->resolveId($id);
        $item = Password::where('user_id', $user->id)->findOrFail($realId);

        $customFields = null;
        $rawAuth = \App\Helpers\Encryptor::decrypt($item->auth_fields) ?: $item->auth_fields;
        if (!empty($rawAuth)) {
            try {
                $customFields = is_array($rawAuth) ? $rawAuth : json_decode($rawAuth, true);
            } catch (\Exception $e) {
                $customFields = null;
            }
        }

        $decryptedPassword = \App\Helpers\Encryptor::decrypt($item->password) ?: $item->password;

        return response()->json([
            'success' => true,
            'credential' => [
                'id' => encrypt($item->id),
                'title' => \App\Helpers\Encryptor::decrypt($item->title) ?: $item->title,
                'username' => \App\Helpers\Encryptor::decrypt($item->username) ?: $item->username,
                'url' => \App\Helpers\Encryptor::decrypt($item->url) ?: $item->url,
                'password' => $decryptedPassword,
                'notes' => \App\Helpers\Encryptor::decrypt($item->notes) ?: $item->notes,
                'custom_fields' => $customFields,
                'is_hidden' => (bool) $item->is_hidden,
                'created_at' => $item->created_at?->toIso8601String(),
                'updated_at' => $item->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Store new encrypted credential.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'required|string|max:1000',
            'url' => 'nullable|string|max:1000',
            'notes' => 'nullable|string',
            'auth_fields' => 'nullable|array',
            'is_hidden' => 'nullable|boolean',
        ]);

        $customFieldsJson = null;
        if (!empty($validated['auth_fields']) && is_array($validated['auth_fields'])) {
            $customFieldsJson = json_encode($validated['auth_fields']);
        }

        $item = Password::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'username' => $validated['username'] ?? null,
            'password' => $validated['password'],
            'url' => $validated['url'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'auth_fields' => $customFieldsJson,
            'is_hidden' => $request->boolean('is_hidden', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Credential stored and encrypted successfully.',
            'credential' => [
                'id' => encrypt($item->id),
                'title' => \App\Helpers\Encryptor::decrypt($item->title) ?: $item->title,
                'username' => \App\Helpers\Encryptor::decrypt($item->username) ?: $item->username,
                'url' => \App\Helpers\Encryptor::decrypt($item->url) ?: $item->url,
                'password' => '••••••••',
                'is_hidden' => (bool) $item->is_hidden,
                'created_at' => $item->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Update an existing credential.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $item = Password::where('user_id', $user->id)->findOrFail($realId);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:1000',
            'url' => 'nullable|string|max:1000',
            'notes' => 'nullable|string',
            'auth_fields' => 'nullable|array',
            'is_hidden' => 'nullable|boolean',
        ]);

        if (array_key_exists('auth_fields', $validated)) {
            $validated['auth_fields'] = is_array($validated['auth_fields']) ? json_encode($validated['auth_fields']) : null;
        }

        $item->update(array_filter($validated, fn ($val) => $val !== null));

        return response()->json([
            'success' => true,
            'message' => 'Credential updated successfully.',
            'credential' => [
                'id' => encrypt($item->id),
                'title' => \App\Helpers\Encryptor::decrypt($item->title) ?: $item->title,
                'username' => \App\Helpers\Encryptor::decrypt($item->username) ?: $item->username,
                'url' => \App\Helpers\Encryptor::decrypt($item->url) ?: $item->url,
                'password' => '••••••••',
                'is_hidden' => (bool) $item->is_hidden,
                'updated_at' => $item->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Delete a credential.
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $item = Password::where('user_id', $user->id)->findOrFail($realId);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Credential deleted successfully.',
        ]);
    }

    /**
     * Resolve ID if passed as numeric or encrypted string.
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
                    abort(404, 'Invalid credential identifier.');
                }
            }
        }
    }
}
