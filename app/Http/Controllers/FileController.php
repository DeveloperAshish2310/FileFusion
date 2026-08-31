<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\FileEncryptor;
use App\Models\FileModal;
use App\Models\FileShare;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class FileController extends Controller
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

    public function uploadaction(Request $request)
    {
        try {
            // Get chunk info FIRST for logging
            $chunk = $request->input('chunk', 0);
            $chunks = $request->input('chunks', 0);

            // Debug log
            Log::info("Upload request received", [
                'chunk' => $chunk,
                'chunks' => $chunks,
                'has_file' => $request->hasFile('file'),
                'file_name' => $request->input('name', 'unknown'),
                'content_type' => $request->header('Content-Type'),
            ]);

            if (!$request->hasFile('file')) {
                $chunkData = $request->getContent();

                if (empty($chunkData) && $chunks > 0) {
                    Log::error("No file data in chunk request", ['chunk' => $chunk]);
                    return response()->json(['ok' => 0, 'info' => 'No file data received in chunk']);
                }

                $file = null;
            } else {
                $file = $request->file('file');
                if (!$file->isValid()) {
                    return response()->json(['ok' => 0, 'info' => 'Invalid file upload.']);
                }
            }

            // Get user
            $user = Auth::user();
            if (!$user instanceof \App\Models\User) {
                $user = \App\Models\User::find($user->id);
            }

            // Set up base upload path and user directory
            $basePath = env('FILE_UPLOAD_PATH', 'private/uploads/files');
            $userDirectory = $user->directory;
            $disk = 'local';

            if (empty($userDirectory)) {
                $userDirectory = $user->username . '-' . Str::uuid()->toString();
                $user->directory = $userDirectory;
                $user->save();
            }

            $filePath = $basePath . '/' . $userDirectory;

            // Ensure directories exist
            if (!Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->makeDirectory($filePath, 0700, true);
            }

            // Create dedicated temporary uploads directory outside public access
            $tempDir = 'private/temp_uploads/' . $userDirectory;
            if (!Storage::disk($disk)->exists($tempDir)) {
                Storage::disk($disk)->makeDirectory($tempDir, 0700, true);
            }

            // Get the original file name and clean it to prevent path traversal
            $ogFileName = $request->input('name', $file ? $file->getClientOriginalName() : 'upload');
            $ogFileName = basename(str_replace(['../', '..\\', '%00'], '', $ogFileName));
            $fileExtension = pathinfo($ogFileName, PATHINFO_EXTENSION) ?: 'bin';

            // Safe chunk identifier
            $chunkId = preg_replace('/[^A-Za-z0-9_\-]/', '', $request->input('id', 'chunk_' . md5($ogFileName . $user->id)));
            $tempFilePath = $tempDir . "/{$chunkId}.part";

            if ($file) {
                $chunkData = file_get_contents($file->getRealPath());
            }

            // Open the file in binary write/append mode (clean start on chunk 0 to purge any stale/cancelled attempts)
            $tempFileFullPath = Storage::disk($disk)->path($tempFilePath);
            $mode = ($chunk == 0) ? 'wb' : 'ab';

            if ($handle = fopen($tempFileFullPath, $mode)) {
                fwrite($handle, $chunkData);
                fclose($handle);
            } else {
                return response()->json(['ok' => 0, 'info' => 'Unable to open temporary file for writing.', 'code' => 400]);
            }

            // If last chunk, finalize and encrypt the file
            if (!$chunks || $chunk == $chunks - 1) {
                $fileName = Str::random(35) . '.' . $fileExtension;
                $finalPath = $filePath . "/{$fileName}.enc";
                $finalFullPath = Storage::disk($disk)->path($finalPath);

                // Get file type
                if ($file) {
                    $fileType = $file->getMimeType();
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $fileType = finfo_file($finfo, $tempFileFullPath);
                    finfo_close($finfo);
                }

                // Encrypt file at rest using AES-256-GCM Envelope Encryption
                try {
                    $encMetadata = FileEncryptor::encryptFile($tempFileFullPath, $finalFullPath);
                } catch (\Exception $e) {
                    @unlink($tempFileFullPath);
                    return response()->json(['ok' => 0, 'info' => 'File encryption failed: ' . $e->getMessage(), 'code' => 500]);
                }

                // Immediately purge plaintext temporary file
                @unlink($tempFileFullPath);

                $fileSize = $encMetadata['original_size'];

                // Double-check quota before final save
                if (!$user->hasEnoughStorage($fileSize)) {
                    FileEncryptor::cryptoShred($finalFullPath);
                    return response()->json([
                        'ok' => 0,
                        'info' => 'Storage quota exceeded during upload. File removed.',
                        'code' => 400
                    ]);
                }

                // Save file data in the database
                $fileData = [
                    'name' => $ogFileName,
                    'path' => $finalPath,
                    'size' => $fileSize,
                    'type' => $fileType ?: 'application/octet-stream',
                    'user_id' => $user->id,
                    'thumbnail' => null,
                    'status' => '1',
                    'is_hidden' => $request->boolean('is_hidden', false) ? 1 : 0,
                ];

                $savedFile = FileModal::create($fileData);
                if (!$savedFile) {
                    FileEncryptor::cryptoShred($finalFullPath);
                    return response()->json(['ok' => 0, 'info' => 'Failed to save file info in the database', 'code' => 400]);
                }

                // ADD STORAGE USAGE TO USER QUOTA
                $user->addStorageUsage($fileSize);

                Log::info("File uploaded and encrypted successfully (AES-256-GCM)", [
                    'user_id' => $user->id,
                    'file_name' => $ogFileName,
                    'file_size' => BytetoSize($fileSize),
                    'encrypted_path' => $finalPath
                ]);
            }
            return response()->json(['ok' => 1, 'info' => 'Upload OK', 'code' => 200]);
        } catch (\Throwable $th) {
            return response()->json(['ok' => 0, 'info' => $th->getMessage(), 'code' => 500]);
        }
    }

    public function download($fileid)
    {
        $id = $this->resolveId($fileid);
        $file = FileModal::where('is_trashed', 0)->findOrFail($id);

        $isOwner = $file->user_id == Auth::id();
        $isSharedRecipient = FileShare::where('file_id', $file->id)
            ->where('recipient_user_id', Auth::id())
            ->where('share_type', 'private_user')
            ->exists();

        if (!$isOwner && !$isSharedRecipient) {
            return response()->json(['ok' => 0, 'info' => 'You are not authorized to Download this file'], 403);
        }

        $disk = 'local';
        $filePath = $file->path;

        if (Storage::disk($disk)->exists($filePath)) {
            // Transparent On-The-Fly Decrypted Stream
            return FileEncryptor::streamDecrypted($filePath, $file->name, $file->type ?? 'application/octet-stream', $disk);
        }

        return redirect()->back()->with('error', 'File not found in storage. Please try again later.');
    }

    /**
     * In-Browser Decrypted Stream Preview Handler.
     */
    public function preview($fileid)
    {
        $id = $this->resolveId($fileid);
        $file = FileModal::where('is_trashed', 0)->findOrFail($id);

        $isOwner = $file->user_id == Auth::id();
        $isSharedRecipient = FileShare::where('file_id', $file->id)
            ->where('recipient_user_id', Auth::id())
            ->where('share_type', 'private_user')
            ->exists();

        $isSuperAdmin = Auth::user() && Auth::user()->isSuperAdmin();

        if (!$isOwner && !$isSharedRecipient && !$isSuperAdmin) {
            abort(403, 'You are not authorized to preview this file.');
        }

        $disk = 'local';
        $filePath = $file->path;

        if (Storage::disk($disk)->exists($filePath)) {
            $mimeType = $file->type ?: 'application/octet-stream';
            return FileEncryptor::streamDecryptedInline($filePath, $file->name, $mimeType, $disk);
        }

        abort(404, 'File not found in storage.');
    }

    /**
     * Parse CSV / Tabular Data into JSON rows for in-browser table viewing.
     */
    public function csvData($fileid)
    {
        $id = $this->resolveId($fileid);
        $file = FileModal::where('is_trashed', 0)->findOrFail($id);

        $isOwner = $file->user_id == Auth::id();
        $isSuperAdmin = Auth::user() && Auth::user()->isSuperAdmin();

        if (!$isOwner && !$isSuperAdmin) {
            return response()->json(['ok' => 0, 'info' => 'Unauthorized'], 403);
        }

        $disk = 'local';
        $fullPath = Storage::disk($disk)->path($file->path);

        if (!file_exists($fullPath)) {
            return response()->json(['ok' => 0, 'info' => 'File not found'], 404);
        }

        try {
            $content = FileEncryptor::decryptFileToString($fullPath);
            $lines = explode("\n", $content);
            $rows = [];
            $headers = [];

            foreach ($lines as $idx => $line) {
                if ($idx > 200) break; // Limit first 200 rows for preview performance
                $line = trim($line);
                if (empty($line)) continue;
                $row = str_getcsv($line);
                if ($idx === 0) {
                    $headers = $row;
                } else {
                    $rows[] = $row;
                }
            }

            return response()->json([
                'ok' => 1,
                'name' => $file->name,
                'headers' => $headers,
                'rows' => $rows,
                'total_rows_sampled' => count($rows)
            ]);
        } catch (\Exception $e) {
            return response()->json(['ok' => 0, 'info' => 'Failed to parse CSV: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if the user has an active, authenticated vault session.
     */
    protected function isVaultUnlocked(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
        if (!$isAuth) return false;

        $lastActivity = session('vault_group_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_links_last_activity') ?: session('hidden_passwords_last_activity');
        if (!$lastActivity) return true;

        $sessionTimeout = method_exists($user, 'getHiddenFilesSessionLifetime') ? $user->getHiddenFilesSessionLifetime() : 1800;
        if ($sessionTimeout === 0) {
            $sessionTimeout = 300;
        }

        return (now()->timestamp - $lastActivity) <= $sessionTimeout;
    }

    public function delete(Request $request, $id)
    {
        return $this->moveToTrash($request, $id);
    }

    public function moveToTrash(Request $request, $id)
    {
        $id = $this->resolveId($id);
        $file = FileModal::find($id);
        if (!$file) {
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'error' => 'File not found']);
            }
            if ($this->isVaultUnlocked()) {
                return redirect()->route('panel.hiddenFiles')->with('error', 'File not found');
            }
            return redirect()->route('panel.filelist')->with('error', 'File not found');
        }

        if ($file->user_id != Auth::id()) {
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'error' => 'You are not authorized to delete this file']);
            }
            return redirect()->back()->with('error', 'You are not authorized to delete this file');
        }

        // Hidden item: permanently delete immediately (crypto shred storage file + force delete DB record)
        if ($file->is_hidden) {
            return $this->permanentDelete($request, $id);
        }

        // Visible file: Move to trash instead of permanent delete
        $file->is_trashed = 1;
        $file->deleted_at = now();
        $file->save();

        if ($request->ajax()) {
            return response()->json(['ok' => 1, 'info' => 'File moved to trash']);
        }

        return redirect()->back()->with('success', 'File moved to trash');
    }

    public function permanentDelete(Request $request, $id)
    {
        $id = $this->resolveId($id);
        $file = FileModal::withTrashed()->find($id);
        if (!$file) {
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'error' => 'File not found']);
            }
            if ($this->isVaultUnlocked()) {
                return redirect()->route('panel.hiddenFiles')->with('error', 'File not found');
            }
            return redirect()->route('panel.filelist')->with('error', 'File not found');
        }

        if ($file->user_id != Auth::id()) {
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'error' => 'You are not authorized to delete this file']);
            }
            return redirect()->back()->with('error', 'You are not authorized to delete this file');
        }

        $wasHidden = (bool) $file->is_hidden;
        $disk = 'local';
        $filePath = $file->path;
        $fileSize = $file->size;
        $user = Auth::user();

        // Perform Cryptographic Erasure & Logical Shredding
        if (Storage::disk($disk)->exists($filePath)) {
            FileEncryptor::cryptoShred(Storage::disk($disk)->path($filePath));
        }

        // Permanently delete from database
        $file->forceDelete();

        // REDUCE STORAGE USAGE FROM USER QUOTA
        $user->reduceStorageUsage($fileSize);

        Log::info("File permanently deleted", [
            'user_id' => $user->id,
            'file_name' => $file->name,
            'file_size' => BytetoSize($fileSize),
            'storage_used' => BytetoSize($user->storage_used),
            'storage_quota' => BytetoSize($user->storage_quota),
            'storage_percentage' => $user->getStorageUsagePercentage() . '%'
        ]);

        $msg = $wasHidden ? 'Hidden file permanently deleted' : 'File permanently deleted';

        if ($request->ajax()) {
            return response()->json(['ok' => 1, 'info' => $msg, 'is_hidden' => $wasHidden]);
        }

        if ($wasHidden && $this->isVaultUnlocked()) {
            return redirect()->route('panel.hiddenFiles')->with('success', $msg);
        }

        return redirect()->route('panel.filelist')->with('success', $msg);
    }

    public function restore(Request $request, $id)
    {
        $id = decrypt($id);
        $file = FileModal::withTrashed()->find($id);
        if (!$file) {
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'error' => 'File not found']);
            }
            return redirect()->back()->with('error', 'File not found');
        }

        if ($file->user_id != Auth::id()) {
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'error' => 'You are not authorized to restore this file']);
            }
            return redirect()->back()->with('error', 'You are not authorized to restore this file');
        }

        // Restore from trash
        $file->is_trashed = 0;
        $file->deleted_at = null;
        $file->save();

        if ($request->ajax()) {
            return response()->json(['ok' => 1, 'info' => 'File restored from trash']);
        }

        return redirect()->back()->with('success', 'File restored from trash');
    }

    public function listTrashedFiles(Request $request)
    {
        $files = FileModal::where('user_id', Auth::id())
                    ->where('is_trashed', 1)
                    ->orderBy('deleted_at', 'desc')
                    ->get();

        if ($request->ajax()) {
            return response()->json([
                'ok' => 1,
                'files' => $files->map(function($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->name,
                        'size' => $file->size,
                        'deleted_at' => $file->deleted_at->format('Y-m-d H:i:s'),
                        'encrypted_id' => encrypt($file->id)
                    ];
                })
            ]);
        }

        return view('panel.ajax.trash_files', compact('files'));
    }

    public function emptyTrash(Request $request)
    {
        $type = $request->get('type', 'files');

        if ($type !== 'files') {
            return response()->json(['ok' => 0, 'error' => 'Invalid trash type']);
        }



        try {
            // Get all trashed files for current user
            $trashedFiles = FileModal::where('user_id', Auth::id())
                                ->withTrashed()
                                ->where('is_trashed', 1)
                                ->get();
            $deletedCount = 0;
            $failedCount = 0;
            $disk = 'local';
            $errors = [];

            $user = Auth::user();

            foreach ($trashedFiles as $file) {
                try {
                    $fileSize = $file->size;

                    // Perform Cryptographic Erasure & Logical Shredding
                    if (Storage::disk($disk)->exists($file->path)) {
                        FileEncryptor::cryptoShred(Storage::disk($disk)->path($file->path));
                    }

                    // Permanently delete from database
                    $file->forceDelete();

                    // REDUCE STORAGE USAGE FROM USER QUOTA
                    $user->reduceStorageUsage($fileSize);

                    $deletedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Failed to delete {$file->name}: " . $e->getMessage();
                    Log::error("Failed to delete file {$file->id}: " . $e->getMessage());
                    // throw $e;
                }
            }

            if ($request->ajax()) {
                if ($failedCount > 0) {
                    return response()->json([
                        'ok' => 0,
                        'error' => "Deleted {$deletedCount} files, but {$failedCount} failed to delete",
                        'deleted_count' => $deletedCount,
                        'failed_count' => $failedCount,
                        'details' => $errors
                    ]);
                } else {
                    return response()->json([
                        'ok' => 1,
                        'info' => "Successfully deleted all {$deletedCount} files from trash",
                        'deleted_count' => $deletedCount
                    ]);
                }
            }


            if ($failedCount > 0) {
                return redirect()->back()->with('error', "Deleted {$deletedCount} files, but {$failedCount} failed to delete");
            } else {
                return redirect()->back()->with('success', "Successfully deleted all {$deletedCount} files from trash");
            }

        } catch (\Exception $e) {
            // throw $e;

            Log::error("Empty trash failed: " . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['ok' => 0, 'error' => 'Failed to empty trash: ' . $e->getMessage()]);
            }

            return redirect()->back()->with('error', 'Failed to empty trash');
        }
    }

    public function createfile(Request $request)
    {
        $files = $request->get("files");
        $lastFileName = $request->get("lastFileName");
        $action = $request->get("action");

        // Handle saving last file name only
        if ($action === 'saveLastFileName' && $lastFileName) {
            // You can save this to a user preference table or session if needed
            session(['lastFileName' => $lastFileName]);
            return response()->json(['ok' => 1, 'code' => 200, 'info' => 'Last file name saved']);
        }

        if (!$files || !is_array($files)) {
            return response()->json(['ok' => 0, 'info' => 'No files received', 'code' => 400]);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => 0, 'info' => 'User not authenticated', 'code' => 401]);
        }

        // Ensure $user is an Eloquent model instance
        if (!$user instanceof \App\Models\User) {
            $user = \App\Models\User::find($user->id);
        }

        $basePath = env('FILE_UPLOAD_PATH', 'private/uploads/files');
        $disk = 'local';

        // Ensure user directory
        $userDirectory = $user->directory;
        if (empty($userDirectory)) {
            $userDirectory = $user->username . '-' . Str::uuid()->toString();
            $user->directory = $userDirectory;
            $user->save();
        }

        $filePath = $basePath . '/' . $userDirectory;
        if (!Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->makeDirectory($filePath, 0700, true);
        }

        $syncedFiles = [];

        foreach ($files as $file) {
            $fileId = $file['id'] ?? null;
            $name = trim($file['name'] ?? 'Untitled.md');
            $content = $file['content'] ?? '';

            if (!$name) $name = 'Untitled.md';

            $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $name);
            $fileType = pathinfo($safeName, PATHINFO_EXTENSION) ?: 'md';

            // 1. Check if file already exists in DB by direct ID
            $existing = null;
            if ($fileId && is_numeric($fileId)) {
                $existing = FileModal::where('user_id', $user->id)
                    ->where('id', (int)$fileId)
                    ->where('is_trashed', 0)
                    ->first();
            }

            // 2. If not found, check by path pattern
            if (!$existing && $fileId) {
                $existing = FileModal::where('user_id', $user->id)
                    ->where('path', 'LIKE', '%' . $fileId . '-%')
                    ->where('is_trashed', 0)
                    ->first();
            }

            // 3. If not found, check by name for non-trashed files
            if (!$existing) {
                $existing = FileModal::where('user_id', $user->id)
                    ->where('name', $name)
                    ->where('is_trashed', 0)
                    ->first();
            }

            $newFileSize = strlen($content);

            if ($existing) {
                $oldSize = (int)$existing->size;
                $sizeDelta = $newFileSize - $oldSize;

                // Check quota delta
                if ($sizeDelta > 0 && !$user->hasEnoughStorage($sizeDelta)) {
                    return response()->json([
                        'ok' => 0,
                        'info' => 'Storage quota exceeded! Your account has reached its storage limit.',
                        'code' => 400
                    ]);
                }

                // Update existing record and overwrite file content
                $finalPath = $existing->path;
                Storage::disk($disk)->put($finalPath, $content);

                $existing->update([
                    'name' => $name,
                    'size' => $newFileSize,
                    'type' => $fileType,
                    'updated_at' => now()
                ]);

                // Adjust user quota
                if ($sizeDelta > 0) {
                    $user->addStorageUsage($sizeDelta);
                } elseif ($sizeDelta < 0) {
                    $user->reduceStorageUsage(abs($sizeDelta));
                }

                $syncedFiles[] = [
                    'client_id' => $fileId,
                    'db_id' => (string)$existing->id,
                    'name' => $existing->name,
                    'size' => $existing->size
                ];
            } else {
                // Check quota for new file
                if (!$user->hasEnoughStorage($newFileSize)) {
                    return response()->json([
                        'ok' => 0,
                        'info' => 'Storage quota exceeded! Your account has reached its storage limit.',
                        'code' => 400
                    ]);
                }

                // Create new file and record
                $uniqueId = $fileId ? (string)$fileId : (string)time();
                $newFinalPath = $filePath . '/' . $uniqueId . '-' . $safeName;
                Storage::disk($disk)->put($newFinalPath, $content);

                $newRecord = FileModal::create([
                    'name' => $name,
                    'path' => $newFinalPath,
                    'size' => $newFileSize,
                    'type' => $fileType,
                    'user_id' => $user->id,
                    'thumbnail' => null,
                    'status' => 1
                ]);

                $user->addStorageUsage($newFileSize);

                $syncedFiles[] = [
                    'client_id' => $fileId,
                    'db_id' => (string)$newRecord->id,
                    'name' => $newRecord->name,
                    'size' => $newRecord->size
                ];
            }
        }

        if ($lastFileName) {
            session(['lastFileName' => $lastFileName]);
        }

        return response()->json([
            'ok' => 1,
            'code' => 200,
            'info' => 'Files saved/updated successfully',
            'synced_files' => $syncedFiles
        ]);
    }

    public function editFile($fileId)
    {
        try {
            $id = Crypt::decrypt($fileId);
        } catch (\Exception $e) {
            $id = $fileId;
        }

        $file = FileModal::where('user_id', Auth::id())->findOrFail($id);

        $disk = 'local';
        $content = '';
        if (Storage::disk($disk)->exists($file->path)) {
            try {
                $content = FileEncryptor::decryptFileToString(Storage::disk($disk)->path($file->path));
            } catch (\Exception $e) {
                $content = Storage::disk($disk)->get($file->path);
            }
        }

        // Ensure clean UTF-8 encoding
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        }

        return view('panel.newfile', compact('file', 'content'));
    }

    public function renameFile(Request $request)
    {
        $request->validate([
            'fileId' => 'required',
            'newName' => 'required|string|max:255'
        ]);

        $id = Crypt::decrypt($request->fileId);
        $file = FileModal::where('user_id', Auth::id())->findOrFail($id);

        $disk = 'local';
        $oldPath = $file->path;

        // Generate new path with same structure
        if (strpos($oldPath, '-') !== false) {
            // For text files from newfile (format: fileId-filename)
            $pathParts = explode('/', $oldPath);
            $oldFileName = end($pathParts);
            $fileIdPart = explode('-', $oldFileName)[0];
            $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $request->newName);
            $newFileName = $fileIdPart . '-' . $safeName;
            $newPath = str_replace($oldFileName, $newFileName, $oldPath);
        } else {
            // For uploaded files, keep same directory structure
            $pathParts = explode('/', $oldPath);
            array_pop($pathParts); // Remove old filename
            $extension = pathinfo($file->name, PATHINFO_EXTENSION);
            $requestExtension = pathinfo($request->newName, PATHINFO_EXTENSION);

            // Preserve original extension if not provided
            if (!$requestExtension && $extension) {
                $newFileName = $request->newName . '.' . $extension;
            } else {
                $newFileName = $request->newName;
            }

            $newPath = implode('/', $pathParts) . '/' . $newFileName;
        }

        // Rename physical file
        if (Storage::disk($disk)->exists($oldPath)) {
            Storage::disk($disk)->move($oldPath, $newPath);
        }

        // Update database
        $file->update([
            'name' => $request->newName,
            'path' => $newPath
        ]);

        return response()->json(['ok' => 1, 'code' => 200, 'info' => 'File renamed successfully']);
    }

    public function toggleHide(Request $request)
    {
        $request->validate([
            'fileId' => 'required'
        ]);

        $id = Crypt::decrypt($request->fileId);
        $file = FileModal::where('user_id', Auth::id())->findOrFail($id);

        // If unhiding from hidden files page, validate session
        if ($file->is_hidden && $request->input('from_hidden_page')) {
            $lastActivity = session('hidden_files_last_activity');
            if (!session('hidden_files_authenticated') || !$lastActivity || (now()->timestamp - $lastActivity) > 1800) {
                return response()->json(['ok' => 0, 'code' => 401, 'info' => 'Session expired. Please login again.', 'expired' => true]);
            }
        }

        $file->update([
            'is_hidden' => !$file->is_hidden
        ]);

        $action = $file->is_hidden ? 'hidden' : 'shown';
        return response()->json(['ok' => 1, 'code' => 200, 'info' => "File {$action} successfully"]);
    }

    public function loadFileContent(Request $request)
    {
        try {
            $files = json_decode($request->input('loadFiles'), true);
            $lastFileName = session('lastFileName');

            if (!$files || !is_array($files)) {
                return response()->json(['ok' => 0, 'info' => 'No files to load', 'code' => 400]);
            }

            $user = Auth::user();
            $disk = 'local';

            $loadedFiles = [];

            foreach ($files as $fileData) {
                $fileId = $fileData['id'] ?? null;
                if (!$fileId) continue;

                // Try to find existing file in database using the file ID pattern
                $existingFile = FileModal::where('user_id', $user->id)
                    ->where('path', 'LIKE', '%' . $fileId . '-%')
                    ->first();

                if ($existingFile && Storage::disk($disk)->exists($existingFile->path)) {
                    try {
                        $content = FileEncryptor::decryptFileToString(Storage::disk($disk)->path($existingFile->path));
                        $loadedFiles[] = [
                            'id' => $fileId,
                            'name' => $existingFile->name,
                            'content' => $content,
                            'createdAt' => $existingFile->created_at,
                            'updatedAt' => $existingFile->updated_at
                        ];
                    } catch (\Exception $e) {
                        // Fallback for legacy plaintext
                        try {
                            $content = Storage::disk($disk)->get($existingFile->path);
                            $loadedFiles[] = [
                                'id' => $fileId,
                                'name' => $existingFile->name,
                                'content' => $content,
                                'createdAt' => $existingFile->created_at,
                                'updatedAt' => $existingFile->updated_at
                            ];
                        } catch (\Exception $ex) {
                            continue;
                        }
                    }
                }
            }

            return response()->json([
                'ok' => 1,
                'code' => 200,
                'files' => $loadedFiles,
                'lastFileName' => $lastFileName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'info' => 'Error loading files: ' . $e->getMessage(),
                'code' => 500
            ]);
        }
    }

    public function hiddenFilesLogin()
    {
        $user = Auth::user();
        if ($user) {
            $sessionTimeout = $user->getVaultSessionLifetime();
            $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated');
            $lastActivity = session('vault_group_last_activity') ?: session('hidden_files_last_activity');
            if ($isAuth && $lastActivity && (now()->timestamp - $lastActivity) <= $sessionTimeout) {
                return redirect()->route('panel.hiddenFiles');
            }
        }

        return view('panel.hidden-files-login');
    }
    public function hiddenFilesAuth(Request $request)
    {
        $request->validate([
            'passcode' => 'nullable|string',
            'code' => 'nullable|string',
        ]);

        $user = Auth::user();
        $input = trim((string)($request->input('code') ?: $request->input('passcode') ?: $request->input('vault_pass')));

        if (empty($input)) {
            return response()->json([
                'ok' => 0,
                'code' => 422,
                'info' => 'Please enter your verification code or vault passcode.'
            ]);
        }

        $isValid = false;

        // 1. Check if it's a valid TOTP 2FA code, Email OTP, or Recovery Code
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

        // 3. Fallback: check account password if vault password was not set
        if (!$isValid && empty($user->vault_pass)) {
            if (\Illuminate\Support\Facades\Hash::check($input, $user->password)) {
                $isValid = true;
            }
        }

        if ($isValid) {
            \App\Services\AuditLogger::vault('files.unlock_success', "Unlocked Hidden Files vault successfully.", 'success', [], $user);

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

            // Broadcast live security alert to all active user devices (Phone, PC, etc.)
            try {
                $ua = $request->header('User-Agent', '');
                $origin = str_contains($ua, 'Android') ? 'Android Device' : (str_contains($ua, 'Windows') ? 'Windows PC' : (str_contains($ua, 'iPhone') || str_contains($ua, 'Mac') ? 'Apple Device' : 'Web Session'));
                \App\Services\PushNotificationService::sendToUser($user->id, [
                    'title' => '🛡️ Vault Security Alert',
                    'body' => "Master Vault unlocked on {$origin}. Session active for 30 mins.",
                    'url' => route('panel.hiddenFiles'),
                    'tag' => 'filefusion_security',
                    'channelId' => 'filefusion_security',
                ]);
            } catch (\Throwable $pushErr) {
                \Illuminate\Support\Facades\Log::warning('[Vault Unlock Push]: ' . $pushErr->getMessage());
            }

            return response()->json([
                'ok' => 1,
                'code' => 200,
                'info' => 'Access granted',
                'redirect' => route('panel.hiddenFiles')
            ]);
        }

        \App\Services\AuditLogger::vault('files.unlock_failed', "Failed attempt to unlock Hidden Files vault.", 'warning', [], $user);

        // Small delay to prevent brute-force
        sleep(1);

        return response()->json([
            'ok' => 0,
            'code' => 401,
            'info' => 'Invalid verification code or vault password. Please try again.'
        ]);
    }

    /**
     * Authenticate Secret Vault via Native Biometrics / Fingerprint
     */
    public function biometricVaultUnlock(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => 0, 'error' => 'Unauthenticated'], 401);
        }

        $vaultType = $request->input('vault_type', 'files');
        $deviceName = $request->input('device_name', $request->header('User-Agent') ? 'Mobile Device' : 'Native App');
        $platform = $request->input('platform', 'android');

        // Verify that user has enabled Fingerprint / Biometric authentication in Settings
        if (!$user->isVaultBiometricEnabled()) {
            \App\Services\AuditLogger::vault(
                'biometric_unlock_rejected',
                "Biometric vault unlock rejected because Fingerprint unlock is disabled in Settings ({$deviceName}).",
                'warning',
                ['vault_type' => $vaultType, 'platform' => $platform],
                $user
            );

            return response()->json([
                'ok' => 0,
                'code' => 403,
                'info' => 'Fingerprint unlock is disabled in your Settings. Please enter your passcode or enable it in Settings.'
            ], 403);
        }

        // Determine destination redirect
        $redirectUrl = route('panel.hiddenFiles');
        if ($vaultType === 'links') {
            $redirectUrl = route('panel.hiddenLinks');
        } elseif ($vaultType === 'passwords') {
            $redirectUrl = route('panel.hiddenPasswords');
        } elseif ($vaultType === 'categories') {
            $redirectUrl = route('panel.categories');
        } elseif ($vaultType === 'reveal') {
            $redirectUrl = null;
        }

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
            'password_reveal_authenticated' => $now,
            'password_reveal_single_use' => $now,
        ]);
        session()->save();

        // Record security audit trail for biometric unlock
        \App\Services\AuditLogger::vault(
            'biometric_unlock',
            $vaultType === 'reveal'
                ? "Credential revealed via Biometric / Fingerprint authentication ({$deviceName})."
                : "Master Vault unlocked via Biometric / Fingerprint authentication ({$deviceName}).",
            'success',
            [
                'vault_type' => $vaultType,
                'platform' => $platform,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ],
            $user
        );

        // Security push notification (only for full vault unlock sessions, not single credential reveals)
        if ($vaultType !== 'reveal') {
            try {
                \App\Services\PushNotificationService::sendToUser($user->id, [
                    'title' => '🛡️ Vault Biometric Alert',
                    'body' => "Master Vault unlocked via Fingerprint / Biometrics on {$deviceName}.",
                    'url' => $redirectUrl ?: route('panel.hiddenFiles'),
                    'tag' => 'filefusion_security',
                    'channelId' => 'filefusion_security',
                ]);
            } catch (\Throwable $pushErr) {
                \Illuminate\Support\Facades\Log::warning('[Biometric Vault Unlock Push]: ' . $pushErr->getMessage());
            }
        }

        return response()->json([
            'ok' => 1,
            'code' => 200,
            'info' => 'Biometric authentication verified.',
            'redirect' => $redirectUrl
        ]);
    }

    public function hiddenFiles(Request $request)
    {
        $user = Auth::user();
        $sessionTimeout = $user ? $user->getHiddenFilesSessionLifetime() : 1800;

        // Check if user is authenticated for vault group
        $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
        if (!$isAuth) {
            return redirect()->route('panel.hiddenFilesLogin')
                ->with('error', 'Please enter the passcode to access hidden files.');
        }

        // Check if session has expired according to user's configured lifetime
        $lastActivity = session('vault_group_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_links_last_activity') ?: session('hidden_passwords_last_activity');
        if (!$lastActivity || (now()->timestamp - $lastActivity) > $sessionTimeout) {
            session()->forget([
                'vault_group_authenticated', 'vault_group_last_activity',
                'hidden_files_authenticated', 'hidden_files_last_activity',
                'hidden_links_authenticated', 'hidden_links_last_activity',
                'hidden_passwords_authenticated', 'hidden_passwords_last_activity'
            ]);
            return redirect()->route('panel.hiddenFilesLogin')
                ->with('info', 'Vault session expired due to inactivity. Please verify access again.');
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

        $pageItems = \App\Helpers\SettingHelper::getItemsPerPage(24);
        $q = $request->get('q');
        $type = $request->get('type');

        $filesQuery = FileModal::where('user_id', Auth::id())
            ->where('is_hidden', true) // Only show hidden files
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->when($type, function ($query) use ($type) {
                switch ($type) {
                    case 'image':
                        $query->where('type', 'like', 'image/%');
                        break;
                    case 'video':
                        $query->where('type', 'like', 'video/%');
                        break;
                    case 'audio':
                        $query->where('type', 'like', 'audio/%');
                        break;
                    case 'document':
                        $query->where(function ($q) {
                            $q->where('type', 'application/pdf')
                                ->orWhere('type', 'application/msword')
                                ->orWhere('type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                                ->orWhere('type', 'application/vnd.ms-excel')
                                ->orWhere('type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                                ->orWhere('type', 'application/vnd.ms-powerpoint')
                                ->orWhere('type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
                        });
                        break;
                    case 'others':
                        $query->where(function ($q) {
                            $q->whereNotLike('type', 'image/%')
                                ->whereNotLike('type', 'video/%')
                                ->whereNotLike('type', 'audio/%')
                                ->whereNotIn('type', [
                                    'application/pdf',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-powerpoint',
                                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                                ]);
                        });
                        break;
                }
            })->orderBy('id', 'desc');

        $files = $filesQuery->paginate($pageItems)->appends([
            'q' => $q,
            'type' => $type
        ]);

        if ($request->ajax()) {
            return view('panel.ajax.hidden_files_load', compact('files'));
        }

        // Calculate remaining time for timer display based on user's configured lifetime
        $remainingTime = max(0, $sessionTimeout - (now()->timestamp - $now));

        return view('panel.hidden-files', compact('files', 'remainingTime'));
    }

    public function logoutHiddenFiles()
    {
        session()->forget([
            'vault_group_authenticated', 'vault_group_last_activity',
            'hidden_files_authenticated', 'hidden_files_last_activity',
            'hidden_links_authenticated', 'hidden_links_last_activity',
            'hidden_passwords_authenticated', 'hidden_passwords_last_activity',
            'password_reveal_authenticated'
        ]);
        session()->save();
        return redirect()->route('panel.hiddenFilesLogin')
            ->with('success', 'Vault session closed and locked.');
    }

    public function extendHiddenFilesSession()
    {
        // Check if authenticated for any vault section
        $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
        if (!$isAuth) {
            return response()->json(['ok' => 0, 'code' => 401, 'info' => 'Not authenticated', 'remaining_time' => 0]);
        }

        $user = Auth::user();
        $sessionTimeout = $user ? $user->getHiddenFilesSessionLifetime() : 1800;

        // Reset the activity timestamps
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

        return response()->json([
            'ok' => 1,
            'code' => 200,
            'remaining_time' => $sessionTimeout
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:delete,hide,unhide',
            'ids' => 'required|array',
            'ids.*' => 'required'
        ]);

        $decryptedIds = [];
        foreach ($request->ids as $encryptedId) {
            try {
                $decryptedIds[] = decrypt($encryptedId);
            } catch (\Exception $e) {
                try {
                    $decryptedIds[] = Crypt::decrypt($encryptedId);
                } catch (\Exception $ex) {
                    if (is_numeric($encryptedId)) {
                        $decryptedIds[] = $encryptedId;
                    }
                }
            }
        }

        if (empty($decryptedIds)) {
            return response()->json(['ok' => 0, 'info' => 'No valid items selected.'], 400);
        }

        $query = FileModal::where('user_id', Auth::id())->whereIn('id', $decryptedIds);

        if ($request->action === 'delete') {
            $query->update([
                'is_trashed' => 1,
                'deleted_at' => now()
            ]);
            $msg = 'Selected files moved to trash.';
        } elseif ($request->action === 'hide') {
            $query->update(['is_hidden' => true]);
            $msg = 'Selected files hidden successfully.';
        } elseif ($request->action === 'unhide') {
            $query->update(['is_hidden' => false]);
            $msg = 'Selected files unhidden successfully.';
        }

        return response()->json(['ok' => 1, 'info' => $msg]);
    }
}
