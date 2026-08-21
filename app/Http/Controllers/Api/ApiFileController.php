<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileEncryptor;
use App\Http\Controllers\Controller;
use App\Models\FileModal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiFileController extends Controller
{
    /**
     * List user's files with search, filtering, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = FileModal::where('user_id', $user->id)
            ->where('is_trashed', 0);

        if ($request->boolean('only_hidden')) {
            $query->where('is_hidden', 1);
        } elseif (!$request->boolean('include_hidden')) {
            $query->where('is_hidden', 0);
        }

        if ($request->filled('type')) {
            $type = strtolower($request->query('type'));
            $query->where('type', 'like', "%{$type}%");
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));
        $files = $query->orderBy('id', 'desc')->paginate($perPage);

        $search = trim($request->query('search', $request->query('q', '')));
        $items = collect($files->items())->map(function ($file) {
            $encId = encrypt($file->id);
            return [
                'id' => $encId,
                'name' => $file->name,
                'size_bytes' => (int) $file->size,
                'size_formatted' => $this->formatBytes($file->size),
                'type' => $file->type,
                'is_hidden' => (bool) $file->is_hidden,
                'is_starred' => (bool) $file->is_starred,
                'is_encrypted' => (bool) $file->is_encrypted,
                'download_url' => url("/api/v1/files/{$encId}/download"),
                'created_at' => $file->created_at?->toIso8601String(),
                'updated_at' => $file->updated_at?->toIso8601String(),
            ];
        });

        if (!empty($search)) {
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['name']), strtolower($search));
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'per_page' => $files->perPage(),
                'total' => $files->total(),
            ],
        ]);
    }

    /**
     * Upload and encrypt a file into storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'file' => 'required|file|max:512000', // 500MB max per file
            'is_hidden' => 'nullable|boolean',
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType() ?: 'application/octet-stream';

        // Check storage quota
        $quota = (int) $user->storage_quota;
        $used = (int) $user->storage_used;
        if ($quota > 0 && ($used + $fileSize) > $quota) {
            return response()->json([
                'success' => false,
                'message' => 'Storage quota exceeded. Please upgrade your plan or delete existing files.',
            ], 413);
        }

        $userDir = $user->directory ?: 'user_' . $user->id;
        $storedFilename = time() . '_' . Str::random(16) . '.enc';
        $relativeStoragePath = "uploads/{$userDir}/{$storedFilename}";
        $disk = 'local';

        $fullTargetDir = Storage::disk($disk)->path("uploads/{$userDir}");
        if (!file_exists($fullTargetDir)) {
            mkdir($fullTargetDir, 0755, true);
        }
        $fullTargetPath = Storage::disk($disk)->path($relativeStoragePath);

        // Perform AES-256-GCM envelope encryption
        try {
            FileEncryptor::encryptFile($uploadedFile->getRealPath(), $fullTargetPath);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File encryption failed: ' . $e->getMessage(),
            ], 500);
        }

        $fileRecord = FileModal::create([
            'user_id' => $user->id,
            'name' => $originalName,
            'path' => $relativeStoragePath,
            'size' => $fileSize,
            'type' => $mimeType,
            'is_hidden' => $request->boolean('is_hidden', false),
            'is_encrypted' => 1,
            'status' => 'active',
            'is_trashed' => 0,
        ]);

        // Update user's used storage counter
        $user->increment('storage_used', $fileSize);

        $encId = encrypt($fileRecord->id);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded and encrypted successfully.',
            'file' => [
                'id' => $encId,
                'name' => $fileRecord->name,
                'size_bytes' => (int) $fileRecord->size,
                'size_formatted' => $this->formatBytes($fileRecord->size),
                'type' => $fileRecord->type,
                'is_hidden' => (bool) $fileRecord->is_hidden,
                'download_url' => url("/api/v1/files/{$encId}/download"),
                'created_at' => $fileRecord->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get single file metadata.
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $file = FileModal::where('user_id', $user->id)->findOrFail($realId);
        $encId = encrypt($file->id);

        return response()->json([
            'success' => true,
            'file' => [
                'id' => $encId,
                'name' => $file->name,
                'size_bytes' => (int) $file->size,
                'size_formatted' => $this->formatBytes($file->size),
                'type' => $file->type,
                'is_hidden' => (bool) $file->is_hidden,
                'is_starred' => (bool) $file->is_starred,
                'is_trashed' => (bool) $file->is_trashed,
                'is_encrypted' => (bool) $file->is_encrypted,
                'download_url' => url("/api/v1/files/{$encId}/download"),
                'created_at' => $file->created_at?->toIso8601String(),
                'updated_at' => $file->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Generate a public time-bound or download-limited share link (e.g. 1-time valid or expiring).
     */
    public function createShareLink($id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $file = FileModal::where('user_id', $user->id)->findOrFail($realId);

        $validated = $request->validate([
            'expires_in_minutes' => 'nullable|integer|min:1|max:525600', // 1 min up to 1 year
            'max_downloads' => 'nullable|integer|min:1|max:100000', // e.g. 1 for single use
            'password' => 'nullable|string|min:4|max:50',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $shareToken = Str::random(32);
        $expiresAt = !empty($validated['expires_in_minutes'])
            ? now()->addMinutes((int) $validated['expires_in_minutes'])
            : null;

        $share = \App\Models\FileShare::create([
            'file_id' => $file->id,
            'user_id' => $user->id,
            'share_token' => $shareToken,
            'share_type' => 'public_link',
            'password' => !empty($validated['password']) ? \Illuminate\Support\Facades\Hash::make($validated['password']) : null,
            'expires_at' => $expiresAt,
            'max_downloads' => $validated['max_downloads'] ?? null,
            'download_count' => 0,
            'is_anonymous' => $request->boolean('is_anonymous', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Share link generated successfully.',
            'share' => [
                'id' => encrypt($share->id),
                'file_id' => encrypt($file->id),
                'share_token' => $shareToken,
                'public_url' => url('/s/' . $shareToken),
                'direct_download_url' => url('/s/' . $shareToken . '/download'),
                'max_downloads' => $share->max_downloads,
                'is_one_time' => ($share->max_downloads === 1),
                'download_count' => 0,
                'expires_at' => $share->expires_at?->toIso8601String(),
                'is_password_protected' => !empty($validated['password']),
            ],
        ], 201);
    }

    /**
     * Stream or download decrypted file.
     */
    public function download($id): StreamedResponse|JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $file = FileModal::where('user_id', $user->id)->findOrFail($realId);

        $disk = 'local';
        if (!Storage::disk($disk)->exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File storage payload not found.',
            ], 404);
        }

        $mimeType = $file->type ?: 'application/octet-stream';
        return FileEncryptor::streamDecrypted($file->path, $file->name, $mimeType, $disk);
    }

    /**
     * Delete file (soft trash or permanent purge).
     */
    public function destroy($id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $file = FileModal::where('user_id', $user->id)->findOrFail($realId);

        if ($request->boolean('force', false)) {
            // Permanent purge
            $disk = 'local';
            if (Storage::disk($disk)->exists($file->path)) {
                Storage::disk($disk)->delete($file->path);
            }
            $user->decrement('storage_used', min((int)$user->storage_used, (int)$file->size));
            $file->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'File permanently deleted.',
            ]);
        }

        // Soft trash
        $file->update(['is_trashed' => 1]);
        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File moved to trash.',
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
                    abort(404, 'Invalid file identifier.');
                }
            }
        }
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $bytes = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), $precision) . ' ' . ($units[$power] ?? 'B');
    }
}
