<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthController extends Controller
{
    /**
     * Get details of the currently authenticated API token holder.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user() ?: Auth::user();
        $token = $request->attributes->get('api_token');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => encrypt($user->id),
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'role_display' => $user->getRoleDisplayName(),
                'is_super_admin' => $user->isSuperAdmin(),
                'is_admin' => $user->isAdmin(),
                'is_manager' => $user->isManager(),
                'is_pro' => $user->isPro(),
                'storage' => [
                    'used_bytes' => (int) $user->storage_used,
                    'quota_bytes' => (int) $user->storage_quota,
                    'used_formatted' => $user->getStorageUsedFormatted(),
                    'quota_formatted' => $user->getStorageQuotaFormatted(),
                    'percentage' => $user->getStorageUsagePercentage(),
                ],
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'token' => [
                'id' => $token?->token_id,
                'name' => $token?->name,
                'abilities' => $token?->abilities ?? ['*'],
                'expires_at' => $token?->expires_at?->toIso8601String(),
                'last_used_at' => $token?->last_used_at?->toIso8601String(),
            ],
        ]);
    }
}
