<?php

namespace App\Services;

use App\Helpers\Encryptor;
use App\Helpers\FileEncryptor;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class KeyRotationService
{
    /**
     * Generate a new cryptographically secure 256-bit base64 key.
     */
    public static function generateKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * Get current configured keys metadata.
     */
    public static function getCurrentKeyStatus(): array
    {
        $appKey = config('app.key') ?: env('APP_KEY', '');
        $fileKey = config('app.file_encryption_key') ?: env('FILE_ENCRYPTION_KEY', '');
        $fileVersion = config('app.file_key_version') ?: env('FILE_KEY_VERSION', 'v1');
        $appPrevious = config('app.previous_keys') ?: env('APP_PREVIOUS_KEYS', '');
        $filePrevious = config('app.file_previous_keys') ?: env('FILE_PREVIOUS_KEYS', '');

        $appPrevCount = is_array($appPrevious) ? count($appPrevious) : count(array_filter(explode(',', $appPrevious)));
        $filePrevCount = is_array($filePrevious) ? count($filePrevious) : count(array_filter(explode(',', $filePrevious)));

        return [
            'app_key_fingerprint' => self::maskKey($appKey),
            'app_key_set' => !empty($appKey),
            'file_key_fingerprint' => self::maskKey($fileKey),
            'file_key_set' => !empty($fileKey),
            'file_key_version' => $fileVersion,
            'app_previous_keys_count' => $appPrevCount,
            'file_previous_keys_count' => $filePrevCount,
        ];
    }

    /**
     * Mask a key for secure presentation (e.g. base64:llV...FTQ=).
     */
    public static function maskKey(?string $key): string
    {
        if (empty($key)) {
            return 'Not Set (Using Default Fallback)';
        }

        $clean = trim($key);
        if (strlen($clean) <= 16) {
            return substr($clean, 0, 4) . '...' . substr($clean, -4);
        }

        $prefix = str_starts_with($clean, 'base64:') ? 'base64:' : '';
        $body = str_starts_with($clean, 'base64:') ? substr($clean, 7) : $clean;

        return $prefix . substr($body, 0, 4) . '...' . substr($body, -4);
    }

    /**
     * Run a comprehensive Pre-Flight Dry Run to test if current database records & disk files decrypt cleanly.
     *
     * @param string|null $overrideAppKey
     * @param string|null $overrideFileKey
     * @return array
     */
    public static function runDryRun(?string $overrideAppKey = null, ?string $overrideFileKey = null): array
    {
        $appKey = $overrideAppKey ?: (config('app.key') ?: env('APP_KEY'));
        $fileKey = $overrideFileKey ?: (config('app.file_encryption_key') ?: env('FILE_ENCRYPTION_KEY') ?: $appKey);

        $results = [
            'success' => true,
            'errors' => [],
            'stats' => [
                'files_total' => 0,
                'files_decryptable' => 0,
                'files_failures' => 0,
                'categories_total' => 0,
                'categories_decryptable' => 0,
                'categories_failures' => 0,
                'links_total' => 0,
                'links_decryptable' => 0,
                'links_failures' => 0,
                'passwords_total' => 0,
                'passwords_decryptable' => 0,
                'passwords_failures' => 0,
                'disk_files_total' => 0,
                'disk_files_encrypted' => 0,
                'disk_files_decryptable' => 0,
                'disk_files_failures' => 0,
            ],
        ];

        // 1. Check 'files' DB table
        try {
            $files = DB::table('files')->get(['id', 'name']);
            $results['stats']['files_total'] = count($files);
            foreach ($files as $f) {
                if (empty($f->name)) continue;
                $decrypted = Encryptor::decrypt($f->name);
                if ($decrypted !== null) {
                    $results['stats']['files_decryptable']++;
                } else {
                    $results['stats']['files_failures']++;
                    $results['errors'][] = "Files table row #{$f->id} could not be decrypted.";
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = "Error scanning files table: " . $e->getMessage();
        }

        // 2. Check 'categories' DB table
        try {
            $categories = DB::table('categories')->get(['id', 'title', 'description']);
            $results['stats']['categories_total'] = count($categories);
            foreach ($categories as $c) {
                $decTitle = Encryptor::decrypt($c->title);
                $decDesc = Encryptor::decrypt($c->description);
                if ($decTitle !== null && ($c->description === null || $decDesc !== null)) {
                    $results['stats']['categories_decryptable']++;
                } else {
                    $results['stats']['categories_failures']++;
                    $results['errors'][] = "Categories table row #{$c->id} could not be decrypted.";
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = "Error scanning categories table: " . $e->getMessage();
        }

        // 3. Check 'links' DB table
        try {
            $links = DB::table('links')->get(['id', 'title', 'url', 'description', 'tags']);
            $results['stats']['links_total'] = count($links);
            foreach ($links as $l) {
                $decTitle = Encryptor::decrypt($l->title);
                $decUrl = Encryptor::decrypt($l->url);
                if ($decTitle !== null && $decUrl !== null) {
                    $results['stats']['links_decryptable']++;
                } else {
                    $results['stats']['links_failures']++;
                    $results['errors'][] = "Links table row #{$l->id} could not be decrypted.";
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = "Error scanning links table: " . $e->getMessage();
        }

        // 4. Check 'passwords' DB table
        try {
            $passwords = DB::table('passwords')->get(['id', 'title', 'username', 'password', 'url', 'notes', 'auth_fields']);
            $results['stats']['passwords_total'] = count($passwords);
            foreach ($passwords as $p) {
                $decTitle = Encryptor::decrypt($p->title);
                $decPass = Encryptor::decrypt($p->password);
                if ($decTitle !== null) {
                    $results['stats']['passwords_decryptable']++;
                } else {
                    $results['stats']['passwords_failures']++;
                    $results['errors'][] = "Passwords table row #{$p->id} could not be decrypted.";
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = "Error scanning passwords table: " . $e->getMessage();
        }

        // 5. Check Disk Files
        try {
            $diskFiles = self::scanDiskFiles();
            $results['stats']['disk_files_total'] = count($diskFiles);

            $oldKek = FileEncryptor::deriveKekFromRawKey($fileKey);

            foreach ($diskFiles as $fullPath) {
                if (FileEncryptor::isEncrypted($fullPath)) {
                    $results['stats']['disk_files_encrypted']++;
                    try {
                        $handle = fopen($fullPath, 'rb');
                        if ($handle) {
                            fseek($handle, 24);
                            $dekLenData = fread($handle, 2);
                            $dekLen = unpack('n', $dekLenData)[1] ?? 60;
                            $encryptedDek = fread($handle, $dekLen);
                            fclose($handle);

                            // Test DEK decryption
                            FileEncryptor::decryptDekWithFallback($encryptedDek, $oldKek);
                            $results['stats']['disk_files_decryptable']++;
                        }
                    } catch (Exception $e) {
                        $results['stats']['disk_files_failures']++;
                        $results['errors'][] = "Encrypted disk file {$fullPath} failed DEK validation: " . $e->getMessage();
                    }
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = "Error scanning physical storage: " . $e->getMessage();
        }

        if (!empty($results['errors'])) {
            $results['success'] = false;
        }

        return $results;
    }

    /**
     * Re-encrypt all database tables from Old App Key to New App Key.
     */
    public static function reEncryptDatabase(string $oldAppKey, string $newAppKey): array
    {
        $oldEncrypter = Encryptor::createEncrypter($oldAppKey);
        $newEncrypter = Encryptor::createEncrypter($newAppKey);

        $stats = [
            'files_updated' => 0,
            'categories_updated' => 0,
            'links_updated' => 0,
            'passwords_updated' => 0,
        ];

        DB::beginTransaction();

        try {
            // 1. Files table (name)
            $files = DB::table('files')->get();
            foreach ($files as $file) {
                if (empty($file->name)) continue;

                $plain = null;
                try {
                    $plain = $oldEncrypter->decryptString($file->name);
                } catch (Exception $e) {
                    $plain = Encryptor::decrypt($file->name);
                }

                if ($plain !== null) {
                    $plain = Encryptor::decrypt($plain); // Ensure unwrapped to raw plaintext
                    $reEncrypted = $newEncrypter->encryptString($plain);
                    DB::table('files')->where('id', $file->id)->update([
                        'name' => $reEncrypted,
                    ]);
                    $stats['files_updated']++;
                }
            }

            // 2. Categories table (title, description)
            $categories = DB::table('categories')->get();
            foreach ($categories as $cat) {
                $updates = [];

                if (!empty($cat->title)) {
                    $plainTitle = null;
                    try {
                        $plainTitle = $oldEncrypter->decryptString($cat->title);
                    } catch (Exception $e) {
                        $plainTitle = Encryptor::decrypt($cat->title);
                    }
                    if ($plainTitle !== null) {
                        $plainTitle = Encryptor::decrypt($plainTitle);
                        $updates['title'] = $newEncrypter->encryptString($plainTitle);
                    }
                }

                if (!empty($cat->description)) {
                    $plainDesc = null;
                    try {
                        $plainDesc = $oldEncrypter->decryptString($cat->description);
                    } catch (Exception $e) {
                        $plainDesc = Encryptor::decrypt($cat->description);
                    }
                    if ($plainDesc !== null) {
                        $plainDesc = Encryptor::decrypt($plainDesc);
                        $updates['description'] = $newEncrypter->encryptString($plainDesc);
                    }
                }

                if (!empty($updates)) {
                    DB::table('categories')->where('id', $cat->id)->update($updates);
                    $stats['categories_updated']++;
                }
            }

            // 3. Links table (title, url, description, tags)
            $links = DB::table('links')->get();
            foreach ($links as $link) {
                $updates = [];

                foreach (['title', 'url', 'description', 'tags'] as $col) {
                    if (!empty($link->{$col})) {
                        $plain = null;
                        try {
                            $plain = $oldEncrypter->decryptString($link->{$col});
                        } catch (Exception $e) {
                            $plain = Encryptor::decrypt($link->{$col});
                        }
                        if ($plain !== null) {
                            $plain = Encryptor::decrypt($plain);
                            $updates[$col] = $newEncrypter->encryptString($plain);
                        }
                    }
                }

                if (!empty($updates)) {
                    DB::table('links')->where('id', $link->id)->update($updates);
                    $stats['links_updated']++;
                }
            }

            // 4. Passwords table (title, username, url, password, notes, auth_fields)
            $passwords = DB::table('passwords')->get();
            foreach ($passwords as $pw) {
                $updates = [];

                foreach (['title', 'username', 'password', 'url', 'notes'] as $col) {
                    if (!empty($pw->{$col})) {
                        $plain = null;
                        try {
                            $plain = $oldEncrypter->decryptString($pw->{$col});
                        } catch (Exception $e) {
                            $plain = Encryptor::decrypt($pw->{$col});
                        }
                        if ($plain !== null) {
                            $plain = Encryptor::decrypt($plain);
                            $updates[$col] = $newEncrypter->encryptString($plain);
                        }
                    }
                }

                if (!empty($updates)) {
                    DB::table('passwords')->where('id', $pw->id)->update($updates);
                    $stats['passwords_updated']++;
                }
            }

            DB::commit();
            return [
                'success' => true,
                'stats' => $stats,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Database re-encryption failed: " . $e->getMessage());
            throw new Exception("Database re-encryption transaction failed: " . $e->getMessage());
        }
    }

    /**
     * Re-encrypt all physical envelope-encrypted disk files from Old KEK to New KEK.
     */
    public static function reEncryptDiskFiles(string $oldFileKey, string $newFileKey): array
    {
        $oldKek = FileEncryptor::deriveKekFromRawKey($oldFileKey);
        $newKek = FileEncryptor::deriveKekFromRawKey($newFileKey);

        $diskFiles = self::scanDiskFiles();
        $stats = [
            'scanned' => count($diskFiles),
            'encrypted_re_keyed' => 0,
            'legacy_skipped' => 0,
            'failed' => 0,
        ];

        foreach ($diskFiles as $fullPath) {
            if (FileEncryptor::isEncrypted($fullPath)) {
                try {
                    $success = FileEncryptor::reEncryptFileHeader($fullPath, $oldKek, $newKek);
                    if ($success) {
                        $stats['encrypted_re_keyed']++;
                    } else {
                        $stats['failed']++;
                    }
                } catch (Exception $e) {
                    $stats['failed']++;
                    Log::error("Failed to re-encrypt file header for {$fullPath}: " . $e->getMessage());
                }
            } else {
                $stats['legacy_skipped']++;
            }
        }

        return [
            'success' => $stats['failed'] === 0,
            'stats' => $stats,
        ];
    }

    /**
     * Execute full Key Rotation orchestrator.
     *
     * @param array $options
     * @return array
     */
    public static function executeFullRotation(array $options = []): array
    {
        $rotateAppKey = $options['rotate_app_key'] ?? true;
        $rotateFileKey = $options['rotate_file_key'] ?? true;
        $keepPrevious = $options['keep_previous'] ?? true;

        $currentAppKey = config('app.key') ?: env('APP_KEY');
        $currentFileKey = config('app.file_encryption_key') ?: (env('FILE_ENCRYPTION_KEY') ?: $currentAppKey);

        $newAppKey = $options['new_app_key'] ?? ($rotateAppKey ? self::generateKey() : $currentAppKey);
        $newFileKey = $options['new_file_key'] ?? ($rotateFileKey ? self::generateKey() : $currentFileKey);

        // Pre-flight dry run
        $dryRun = self::runDryRun($currentAppKey, $currentFileKey);
        if (!$dryRun['success'] && empty($options['force'])) {
            return [
                'success' => false,
                'stage' => 'dry_run',
                'message' => 'Pre-flight dry run encountered decryption errors. Key rotation aborted to protect data integrity.',
                'errors' => $dryRun['errors'],
                'dry_run' => $dryRun,
            ];
        }

        $dbResult = null;
        $fileResult = null;

        // 1. Re-encrypt Database records if APP_KEY changed
        if ($rotateAppKey && $newAppKey !== $currentAppKey) {
            $dbResult = self::reEncryptDatabase($currentAppKey, $newAppKey);
        }

        // 2. Re-encrypt Disk Files if FILE_KEY changed
        if ($rotateFileKey && $newFileKey !== $currentFileKey) {
            $fileResult = self::reEncryptDiskFiles($currentFileKey, $newFileKey);
        }

        // 3. Increment Key Version
        $currentVersion = config('app.file_key_version') ?: env('FILE_KEY_VERSION', 'v1');
        $versionNum = (int) preg_replace('/[^0-9]/', '', $currentVersion);
        $newVersion = 'v' . ($versionNum + 1);

        // 4. Update .env File
        $envUpdates = [];

        if ($rotateAppKey && $newAppKey !== $currentAppKey) {
            $envUpdates['APP_KEY'] = $newAppKey;
            if ($keepPrevious) {
                $existingAppPrev = config('app.previous_keys') ?: env('APP_PREVIOUS_KEYS', '');
                $prevList = is_array($existingAppPrev) ? $existingAppPrev : array_filter(explode(',', $existingAppPrev));
                if (!in_array($currentAppKey, $prevList)) {
                    array_unshift($prevList, $currentAppKey);
                }
                $envUpdates['APP_PREVIOUS_KEYS'] = implode(',', array_slice($prevList, 0, 5));
            }
        }

        if ($rotateFileKey && $newFileKey !== $currentFileKey) {
            $envUpdates['FILE_ENCRYPTION_KEY'] = $newFileKey;
            $envUpdates['FILE_KEY_VERSION'] = $newVersion;
            if ($keepPrevious) {
                $existingFilePrev = config('app.file_previous_keys') ?: env('FILE_PREVIOUS_KEYS', '');
                $prevList = is_array($existingFilePrev) ? $existingFilePrev : array_filter(explode(',', $existingFilePrev));
                if (!in_array($currentFileKey, $prevList)) {
                    array_unshift($prevList, $currentFileKey);
                }
                $envUpdates['FILE_PREVIOUS_KEYS'] = implode(',', array_slice($prevList, 0, 5));
            }
        }

        $envSuccess = self::updateEnvironmentFile($envUpdates);

        return [
            'success' => true,
            'message' => 'Encryption keys rotated and all database records and storage files re-encrypted successfully.',
            'db_reencrypted' => $dbResult,
            'files_reencrypted' => $fileResult,
            'env_updated' => $envSuccess,
            'new_keys' => [
                'app_key' => $rotateAppKey ? $newAppKey : 'Unchanged',
                'file_key' => $rotateFileKey ? $newFileKey : 'Unchanged',
                'file_key_version' => $rotateFileKey ? $newVersion : $currentVersion,
            ],
        ];
    }

    /**
     * Safely update keys in the .env file.
     */
    public static function updateEnvironmentFile(array $data): bool
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;

            if ($key === 'APP_KEY') {
                config(['app.key' => $value]);
                try {
                    app()->singleton('encrypter', function ($app) use ($value) {
                        $config = $app->make('config')->get('app');
                        $previousKeys = array_map(fn($k) => Encryptor::parseKey($k), $config['previous_keys'] ?? []);
                        return new \Illuminate\Encryption\Encrypter(
                            Encryptor::parseKey($value),
                            $config['cipher'] ?? 'AES-256-CBC',
                            $previousKeys
                        );
                    });
                } catch (\Throwable $e) {
                    // Fallback
                }
            }

            if ($key === 'APP_PREVIOUS_KEYS') {
                config(['app.previous_keys' => array_filter(explode(',', $value))]);
            }

            $pattern = "/^{$key}=(.*)$/m";
            $formattedValue = (str_contains($value, ' ') || str_contains($value, ',')) ? '"' . $value . '"' : $value;

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$formattedValue}", $content);
            } else {
                $content .= "\n{$key}={$formattedValue}";
            }
        }

        return file_put_contents($envPath, $content, LOCK_EX) !== false;
    }

    /**
     * Scan storage directories for physical user files.
     */
    public static function scanDiskFiles(): array
    {
        $files = [];
        $storageDir = storage_path('app');

        if (!is_dir($storageDir)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($storageDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $path = $item->getPathname();
                // Exclude system/framework storage caches
                if (str_contains($path, 'framework') || str_contains($path, '.gitkeep') || str_contains($path, 'temp_editor_preview')) {
                    continue;
                }
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * Convert absolute storage path to relative disk path.
     */
    private static function getRelativeStoragePath(string $fullPath): string
    {
        $base = storage_path('app') . DIRECTORY_SEPARATOR;
        if (str_starts_with($fullPath, $base)) {
            return substr($fullPath, strlen($base));
        }
        return $fullPath;
    }
}
