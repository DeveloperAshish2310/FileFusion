<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileModal;
use App\Models\Links;
use App\Models\Password;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiAdminController extends Controller
{
    /**
     * List all users (Admin / Super Admin only).
     */
    public function users(Request $request): JsonResponse
    {
        $admin = Auth::user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin privileges required.',
            ], 403);
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $query = User::query();

        $search = trim($request->query('search', $request->query('q', '')));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($users->items())->map(function ($u) {
            return [
                'id' => encrypt($u->id),
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role ?? 'user',
                'role_display' => $u->getRoleDisplayName(),
                'status' => $u->status ?? 'active',
                'storage' => [
                    'used_bytes' => (int) $u->storage_used,
                    'quota_bytes' => (int) $u->storage_quota,
                    'used_formatted' => $u->getStorageUsedFormatted(),
                    'quota_formatted' => $u->getStorageQuotaFormatted(),
                    'percentage' => $u->getStorageUsagePercentage(),
                ],
                'created_at' => $u->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Create a new user (Admin / Super Admin only).
     */
    public function storeUser(Request $request): JsonResponse
    {
        $admin = Auth::user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin privileges required.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|in:super_admin,admin,manager,pro_user,user',
            'storage_quota_gb' => 'nullable|numeric|min:0.1',
        ]);

        $role = $validated['role'] ?? User::ROLE_USER;
        $quotaGb = (float) ($validated['storage_quota_gb'] ?? User::getDefaultQuotaGbForRole($role));
        $quotaBytes = (int) ($quotaGb * 1024 * 1024 * 1024);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'account_type' => ($role === User::ROLE_SUPER_ADMIN) ? '1' : '2',
            'storage_quota' => $quotaBytes,
            'storage_used' => 0,
            'directory' => 'user_' . time() . '_' . Str::lower(Str::random(6)),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'user' => [
                'id' => encrypt($user->id),
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'storage_quota_formatted' => $user->getStorageQuotaFormatted(),
            ],
        ], 201);
    }

    /**
     * System metrics and health status (Admin / Super Admin only).
     */
    public function health(): JsonResponse
    {
        $admin = Auth::user();
        if (!$admin || !$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin privileges required.',
            ], 403);
        }

        $totalUsers = User::count();
        $totalFiles = FileModal::where('is_trashed', 0)->count();
        $totalLinks = Links::where('is_trashed', 0)->count();
        $totalPasswords = Password::count();
        $totalStorageBytes = (int) User::sum('storage_used');

        return response()->json([
            'success' => true,
            'system' => [
                'app_name' => config('app.name'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_time' => now()->toIso8601String(),
                'status' => 'healthy',
            ],
            'metrics' => [
                'total_users' => $totalUsers,
                'total_files' => $totalFiles,
                'total_links' => $totalLinks,
                'total_passwords' => $totalPasswords,
                'total_storage_used_bytes' => $totalStorageBytes,
                'total_storage_used_formatted' => $this->formatBytes($totalStorageBytes),
            ],
        ]);
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $bytes = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), $precision) . ' ' . ($units[$power] ?? 'B');
    }
}
