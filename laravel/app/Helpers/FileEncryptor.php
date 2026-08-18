<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileEncryptor
{
    // Structured Binary Header Constants
    public const MAGIC = 'FF_ENC';       // 6 bytes
    public const VERSION_V2 = 2;          // 1 byte
    public const ALG_AES_256_GCM = 1;     // 1 byte (AES-256-GCM)
    public const DEFAULT_KEY_VERSION = 'v001'; // 4 bytes

    public const CHUNK_SIZE = 65536;      // 64 KB streaming buffers

    /**
     * Derive a 256-bit binary KEK from a given key string or base64 key.
     */
    public static function deriveKekFromRawKey(?string $rawKey = null): string
    {
        if (!empty($rawKey)) {
            $rawKey = trim($rawKey);
            if (str_starts_with($rawKey, 'base64:')) {
                $rawKey = substr($rawKey, 7);
            }
            $decoded = base64_decode($rawKey, true);
            if ($decoded !== false && strlen($decoded) === 32) {
                return $decoded;
            }
            if (strlen($rawKey) === 32) {
                return $rawKey;
            }
            return hash('sha256', 'FILE_KEK:' . $rawKey, true);
        }

        // Fallback to active env key or APP_KEY
        return self::getMasterKek();
    }

    /**
     * Derive the Master Key Encryption Key (KEK).
     */
    public static function getMasterKek(?string $overrideKey = null): string
    {
        if (!empty($overrideKey)) {
            return self::deriveKekFromRawKey($overrideKey);
        }

        $rawKey = config('app.file_encryption_key') ?: env('FILE_ENCRYPTION_KEY');
        if (!empty($rawKey)) {
            if (str_starts_with($rawKey, 'base64:')) {
                $rawKey = substr($rawKey, 7);
            }
            $decoded = base64_decode($rawKey, true);
            if ($decoded !== false && strlen($decoded) === 32) {
                return $decoded;
            }
        }

        // Fallback: derive 256-bit KEK from application key using SHA-256
        $appKey = config('app.key') ?: env('APP_KEY', 'default-file-fusion-secure-key-2026');
        return hash('sha256', 'FILE_KEK:' . $appKey, true);
    }

    /**
     * Get all candidate Master KEKs (primary key + previous keys from env).
     *
     * @return array<string> List of 32-byte binary KEKs
     */
    public static function getAllMasterKeks(): array
    {
        $keks = [];
        $keks[] = self::getMasterKek();

        // 1. Check FILE_PREVIOUS_KEYS
        $filePrev = config('app.file_previous_keys') ?: env('FILE_PREVIOUS_KEYS', '');
        if (is_array($filePrev)) {
            foreach ($filePrev as $k) {
                if (!empty($k)) $keks[] = self::deriveKekFromRawKey($k);
            }
        } elseif (!empty($filePrev)) {
            foreach (explode(',', $filePrev) as $k) {
                $k = trim($k);
                if (!empty($k)) {
                    $keks[] = self::deriveKekFromRawKey($k);
                }
            }
        }

        // 2. Check APP_PREVIOUS_KEYS (for fallback KEKs derived from app keys)
        $appPrev = config('app.previous_keys') ?: env('APP_PREVIOUS_KEYS', '');
        if (is_array($appPrev)) {
            foreach ($appPrev as $k) {
                if (!empty($k)) $keks[] = hash('sha256', 'FILE_KEK:' . $k, true);
            }
        } elseif (!empty($appPrev)) {
            foreach (explode(',', $appPrev) as $k) {
                $k = trim($k);
                if (!empty($k)) {
                    $keks[] = hash('sha256', 'FILE_KEK:' . $k, true);
                }
            }
        }

        return array_values(array_unique($keks));
    }

    /**
     * Get active key version string (strictly 4 ASCII bytes, e.g. "v001").
     */
    public static function getKeyVersion(): string
    {
        $ver = (string) (config('app.file_key_version') ?: env('FILE_KEY_VERSION', 'v1'));
        return str_pad(substr($ver, 0, 4), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Encrypt a Data Encryption Key (DEK) with the Master KEK using AES-256-GCM.
     */
    public static function encryptDek(string $dek, string $kek): string
    {
        $dekNonce = random_bytes(12);
        $dekTag = '';
        $dekCiphertext = openssl_encrypt(
            $dek,
            'aes-256-gcm',
            $kek,
            OPENSSL_RAW_DATA,
            $dekNonce,
            $dekTag,
            'DEK_ENVELOPE',
            16
        );

        if ($dekCiphertext === false) {
            throw new Exception('Failed to encrypt Data Encryption Key (DEK).');
        }

        // Package: 12-byte nonce + 32-byte ciphertext + 16-byte tag = 60 bytes
        return $dekNonce . $dekCiphertext . $dekTag;
    }

    /**
     * Decrypt an Encrypted DEK payload using a single Master KEK.
     */
    public static function decryptDek(string $encryptedDekPayload, string $kek): string
    {
        if (strlen($encryptedDekPayload) < 60) {
            throw new Exception('Invalid encrypted DEK payload length.');
        }

        $dekNonce = substr($encryptedDekPayload, 0, 12);
        $dekCiphertext = substr($encryptedDekPayload, 12, 32);
        $dekTag = substr($encryptedDekPayload, 44, 16);

        $dek = openssl_decrypt(
            $dekCiphertext,
            'aes-256-gcm',
            $kek,
            OPENSSL_RAW_DATA,
            $dekNonce,
            $dekTag,
            'DEK_ENVELOPE'
        );

        if ($dek === false) {
            throw new Exception('Integrity check failed: Unable to decrypt DEK (invalid key or tampered envelope).');
        }

        return $dek;
    }

    /**
     * Decrypt DEK with candidate KEKs fallback chain.
     */
    public static function decryptDekWithFallback(string $encryptedDekPayload, ?string $overrideKek = null): string
    {
        if (!empty($overrideKek)) {
            try {
                return self::decryptDek($encryptedDekPayload, $overrideKek);
            } catch (Exception $e) {
                // Continue to candidate list
            }
        }

        $candidates = self::getAllMasterKeks();
        $lastException = null;

        foreach ($candidates as $kek) {
            try {
                return self::decryptDek($encryptedDekPayload, $kek);
            } catch (Exception $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new Exception('Unable to decrypt DEK: No valid Master KEK found in key chain.');
    }

    /**
     * Re-encrypt the 60-byte DEK header block of a file with a new Master KEK without touching ciphertext.
     *
     * @param string $fullPath Absolute path to file
     * @param string|null $oldKek Optional old master KEK override
     * @param string|null $newKek New master KEK
     * @return bool True if re-encrypted successfully
     */
    public static function reEncryptFileHeader(string $fullPath, ?string $oldKek = null, ?string $newKek = null): bool
    {
        if (!file_exists($fullPath) || filesize($fullPath) < 102) {
            return false;
        }

        $handle = fopen($fullPath, 'r+b');
        if (!$handle) {
            throw new Exception("Unable to open file for header re-encryption: {$fullPath}");
        }

        $magic = fread($handle, 6);
        if ($magic !== self::MAGIC) {
            fclose($handle);
            return false; // Not an envelope-encrypted file
        }

        // Read fixed header fields
        fseek($handle, 24);
        $dekLenData = fread($handle, 2);
        $dekLen = unpack('n', $dekLenData)[1] ?? 60;

        $encryptedDek = fread($handle, $dekLen);

        // 1. Decrypt 32-byte DEK with old KEK or fallback chain
        $dek = self::decryptDekWithFallback($encryptedDek, $oldKek);

        // 2. Re-encrypt 32-byte DEK with new KEK
        $targetNewKek = $newKek ?? self::getMasterKek();
        $newEncryptedDek = self::encryptDek($dek, $targetNewKek);

        // 3. Seek back to offset 26 (after MAGIC 6B + VER 1B + ALG 1B + KEYVER 4B + NONCE 12B + DEKLEN 2B = 26B)
        fseek($handle, 26);
        fwrite($handle, $newEncryptedDek);
        fflush($handle);
        fclose($handle);

        return true;
    }

    /**
     * Encrypt a file from source path to destination path using AES-256-GCM Envelope Encryption.
     *
     * @param string $sourceFullPath Absolute path to source unencrypted file
     * @param string $destFullPath Absolute path to write encrypted file
     * @param string|null $keyVersion Optional key version override
     * @return array Encryption metadata (original size, encrypted size, sha256 checksum)
     */
    public static function encryptFile(string $sourceFullPath, string $destFullPath, ?string $keyVersion = null): array
    {
        if (!file_exists($sourceFullPath)) {
            throw new Exception("Source file not found: {$sourceFullPath}");
        }

        $origSize = filesize($sourceFullPath);
        $origSha256 = hash_file('sha256', $sourceFullPath);

        $kek = self::getMasterKek();
        $dek = random_bytes(32); // 256-bit unique per-file key
        $fileNonce = random_bytes(12); // 96-bit unique nonce for file data
        $keyVerStr = $keyVersion ? str_pad(substr($keyVersion, 0, 4), 4, '0', STR_PAD_LEFT) : self::getKeyVersion();

        // 1. Encrypt the DEK
        $encryptedDek = self::encryptDek($dek, $kek);
        $encryptedDekLen = strlen($encryptedDek); // 60 bytes

        // 2. Encrypt file content with DEK using AES-256-GCM
        // Associated Authenticated Data (AAD): Magic + Version + Alg + KeyVersion + FileNonce
        $aad = self::MAGIC . chr(self::VERSION_V2) . chr(self::ALG_AES_256_GCM) . $keyVerStr . $fileNonce;

        $sourceHandle = fopen($sourceFullPath, 'rb');
        if (!$sourceHandle) {
            throw new Exception("Unable to open source file for reading: {$sourceFullPath}");
        }

        // Read and encrypt source file
        $plainData = fread($sourceHandle, $origSize > 0 ? $origSize : 1);
        fclose($sourceHandle);

        if ($plainData === false) {
            $plainData = '';
        }

        $fileTag = '';
        $ciphertext = openssl_encrypt(
            $plainData,
            'aes-256-gcm',
            $dek,
            OPENSSL_RAW_DATA,
            $fileNonce,
            $fileTag,
            $aad,
            16
        );

        if ($ciphertext === false) {
            throw new Exception('AES-256-GCM file encryption failed.');
        }

        // 3. Assemble binary envelope:
        // [MAGIC 6B][VER 1B][ALG 1B][KEYVER 4B][NONCE 12B][DEK_LEN 2B][ENC_DEK 60B][TAG 16B][CIPHERTEXT]
        $header = self::MAGIC
            . chr(self::VERSION_V2)
            . chr(self::ALG_AES_256_GCM)
            . $keyVerStr
            . $fileNonce
            . pack('n', $encryptedDekLen)
            . $encryptedDek
            . $fileTag;

        // Write to destination atomically
        $destDir = dirname($destFullPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0700, true);
        }

        $bytesWritten = file_put_contents($destFullPath, $header . $ciphertext, LOCK_EX);
        if ($bytesWritten === false) {
            throw new Exception("Failed to write encrypted file to: {$destFullPath}");
        }

        return [
            'original_size' => $origSize,
            'encrypted_size' => $bytesWritten,
            'original_sha256' => $origSha256,
            'key_version' => $keyVerStr,
            'cipher' => 'aes-256-gcm',
        ];
    }

    /**
     * Decrypt an encrypted file to memory string or throw on tampering.
     */
    public static function decryptFileToString(string $encryptedFullPath): string
    {
        if (!file_exists($encryptedFullPath)) {
            throw new Exception("Encrypted file not found: {$encryptedFullPath}");
        }

        $handle = fopen($encryptedFullPath, 'rb');
        if (!$handle) {
            throw new Exception("Unable to open encrypted file: {$encryptedFullPath}");
        }

        $fileSize = filesize($encryptedFullPath);
        if ($fileSize < 102) { // 6 + 1 + 1 + 4 + 12 + 2 + 60 + 16 = 102 bytes minimum header
            fclose($handle);
            throw new Exception('Invalid encrypted file: Header truncated.');
        }

        // Read and parse fixed header parts
        $magic = fread($handle, 6);
        if ($magic !== self::MAGIC) {
            fclose($handle);
            // Fallback: If not encrypted, return raw bytes (backward-compat)
            return file_get_contents($encryptedFullPath);
        }

        $version = ord(fread($handle, 1));
        $algId = ord(fread($handle, 1));
        $keyVer = fread($handle, 4);
        $fileNonce = fread($handle, 12);
        $dekLenData = fread($handle, 2);
        $dekLen = unpack('n', $dekLenData)[1] ?? 60;
        $encryptedDek = fread($handle, $dekLen);
        $fileTag = fread($handle, 16);

        $ciphertext = '';
        while (!feof($handle)) {
            $chunk = fread($handle, self::CHUNK_SIZE);
            if ($chunk !== false) {
                $ciphertext .= $chunk;
            }
        }
        fclose($handle);

        // Decrypt DEK using primary key or fallback key chain
        $dek = self::decryptDekWithFallback($encryptedDek);

        // Decrypt file content
        $aad = $magic . chr($version) . chr($algId) . $keyVer . $fileNonce;
        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $dek,
            OPENSSL_RAW_DATA,
            $fileNonce,
            $fileTag,
            $aad
        );

        if ($plaintext === false) {
            throw new Exception('Integrity check failed: File ciphertext has been modified or corrupted.');
        }

        return $plaintext;
    }

    /**
     * Check if a given file contains the encrypted magic header.
     */
    public static function isEncrypted(string $filePath, string $disk = 'local'): bool
    {
        $fullPath = file_exists($filePath) ? $filePath : Storage::disk($disk)->path($filePath);
        if (!file_exists($fullPath) || filesize($fullPath) < 6) {
            return false;
        }

        $handle = @fopen($fullPath, 'rb');
        if (!$handle) {
            return false;
        }

        $magic = fread($handle, 6);
        fclose($handle);

        return ($magic === self::MAGIC);
    }

    /**
     * Inspect and parse header metadata from an encrypted file.
     */
    public static function getHeaderInfo(string $fullPath): ?array
    {
        if (!file_exists($fullPath) || filesize($fullPath) < 102) {
            return null;
        }

        $handle = @fopen($fullPath, 'rb');
        if (!$handle) return null;

        $magic = fread($handle, 6);
        if ($magic !== self::MAGIC) {
            fclose($handle);
            return null;
        }

        $version = ord(fread($handle, 1));
        $algId = ord(fread($handle, 1));
        $keyVer = fread($handle, 4);
        $nonce = fread($handle, 12);
        fclose($handle);

        return [
            'magic' => $magic,
            'version' => $version,
            'algorithm' => ($algId === self::ALG_AES_256_GCM ? 'AES-256-GCM' : 'Unknown'),
            'key_version' => trim($keyVer),
            'nonce_hex' => bin2hex($nonce),
        ];
    }

    /**
     * Perform On-The-Fly Decrypted Streaming directly into a StreamedResponse.
     *
     * @param string $relativeStoragePath Relative path in Storage::disk($disk)
     * @param string $downloadFileName Display filename for client download
     * @param string $mimeType MIME type of the file
     * @param string $disk Storage disk
     * @return StreamedResponse
     */
    public static function streamDecrypted(
        string $relativeStoragePath,
        string $downloadFileName,
        string $mimeType = 'application/octet-stream',
        string $disk = 'local'
    ): StreamedResponse {
        $fullPath = Storage::disk($disk)->path($relativeStoragePath);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found in storage.');
        }

        // If legacy unencrypted file, stream normally
        if (!self::isEncrypted($relativeStoragePath, $disk)) {
            return response()->streamDownload(function () use ($fullPath) {
                $stream = fopen($fullPath, 'rb');
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            }, $downloadFileName, [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return response()->streamDownload(function () use ($fullPath) {
            try {
                $decryptedContent = self::decryptFileToString($fullPath);
                echo $decryptedContent;
                flush();
            } catch (Exception $e) {
                Log::error("Decryption streaming error: {$e->getMessage()}", ['file' => $fullPath]);
                echo "Error: File integrity verification failed.";
            }
        }, $downloadFileName, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Perform On-The-Fly Decrypted Streaming with inline disposition for in-browser previews.
     */
    public static function streamDecryptedInline(
        string $relativeStoragePath,
        string $displayFileName,
        string $mimeType = 'application/octet-stream',
        string $disk = 'local'
    ): StreamedResponse {
        $fullPath = Storage::disk($disk)->path($relativeStoragePath);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found in storage.');
        }

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($displayFileName) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data: blob:; media-src 'self' blob:; style-src 'unsafe-inline'; font-src 'self'; frame-ancestors 'self';",
        ];

        // If legacy unencrypted file, stream normally
        if (!self::isEncrypted($relativeStoragePath, $disk)) {
            return new StreamedResponse(function () use ($fullPath) {
                $stream = fopen($fullPath, 'rb');
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            }, 200, $headers);
        }

        // Decrypt in memory & stream inline
        return new StreamedResponse(function () use ($fullPath) {
            try {
                $decryptedContent = self::decryptFileToString($fullPath);
                echo $decryptedContent;
                flush();
            } catch (Exception $e) {
                Log::error("Decryption preview streaming error: {$e->getMessage()}", ['file' => $fullPath]);
                echo "Error: File integrity verification failed.";
            }
        }, 200, $headers);
    }

    /**
     * Perform Cryptographic Erasure (destroy DEK block) + Best-Effort Logical Shredding.
     */
    public static function cryptoShred(string $fullPath): bool
    {
        if (!file_exists($fullPath)) {
            return true;
        }

        try {
            $fileSize = filesize($fullPath);
            if ($fileSize > 0 && is_writable($fullPath)) {
                // 1. Overwrite the first 256 bytes (Envelope DEK + Header) with random noise
                $handle = fopen($fullPath, 'r+b');
                if ($handle) {
                    $overwriteBytes = min($fileSize, 512);
                    fwrite($handle, random_bytes($overwriteBytes));
                    fflush($handle);
                    fclose($handle);
                }
            }

            // 2. Unlink the file from filesystem
            return @unlink($fullPath);
        } catch (Exception $e) {
            Log::warning("Crypto-shred warning: " . $e->getMessage());
            return @unlink($fullPath);
        }
    }
}
