<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenWebController extends Controller
{
    /**
     * Generate a new API token for the logged-in user.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->canUseApi()) {
            return response()->json([
                'success' => false,
                'message' => 'API access is not enabled for your account. Please request approval from an administrator.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string|max:50',
            'expires_in' => 'nullable|string|in:7,30,90,365,never',
        ]);

        $abilities = $validated['abilities'] ?? ['*'];
        if (empty($abilities) || in_array('*', $abilities, true)) {
            $abilities = ['*'];
        }

        // Restrict admin abilities if user is not an admin
        if (!$user->isAdmin()) {
            $abilities = array_values(array_filter($abilities, fn ($a) => !str_starts_with($a, 'admin:')));
        }

        $expiresAt = null;
        $expiryOption = $validated['expires_in'] ?? 'never';
        if ($expiryOption !== 'never') {
            $expiresAt = Carbon::now()->addDays((int) $expiryOption);
        }

        $result = ApiToken::generate($user, $validated['name'], $abilities, $expiresAt);

        return response()->json([
            'success' => true,
            'message' => 'API Token generated successfully.',
            'plain_token' => $result['plainToken'],
            'token' => [
                'id' => $result['token']->id,
                'name' => $result['token']->name,
                'token_id' => $result['token']->token_id,
                'abilities' => $result['token']->abilities,
                'expires_at' => $result['token']->expires_at?->format('M d, Y'),
                'created_at' => $result['token']->created_at?->format('M d, Y'),
            ],
        ], 201);
    }

    /**
     * Revoke (delete) an API token.
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $token = ApiToken::where('user_id', $user->id)->findOrFail($id);
        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'API token revoked successfully.',
        ]);
    }
}
