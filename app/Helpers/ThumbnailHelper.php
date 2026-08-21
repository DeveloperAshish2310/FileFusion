<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ThumbnailHelper
{
    /**
     * Download an external image URL and save it to public storage.
     *
     * @param string|null $url Remote image URL
     * @param string $subfolder Directory inside public/ (default: 'thumbnails')
     * @param string|null $oldThumbnail Old relative path to remove if overwritten
     * @return string|null Relative path from public/ (e.g. 'thumbnails/1786901234-abc123.jpg') or original URL
     */
    public static function downloadAndSave(?string $url, string $subfolder = 'thumbnails', ?string $oldThumbnail = null): ?string
    {
        if (empty($url) || !is_string($url)) {
            return null;
        }

        $url = trim($url);

        // If it's already a relative path, return as is
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            return $url;
        }

        // If it points to the application's own local domain/asset, extract the relative path
        $appUrl = config('app.url');
        if (!empty($appUrl) && str_starts_with($url, $appUrl)) {
            $parsedPath = parse_url($url, PHP_URL_PATH);
            return ltrim($parsedPath, '/');
        }

        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning("ThumbnailHelper: Remote URL {$url} returned HTTP {$response->status()}");
                return $url;
            }

            $body = $response->body();
            if (empty($body)) {
                return $url;
            }

            // Determine image extension
            $contentType = strtolower($response->header('Content-Type') ?? '');
            $extension = 'png';

            if (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) {
                $extension = 'jpg';
            } elseif (str_contains($contentType, 'webp')) {
                $extension = 'webp';
            } elseif (str_contains($contentType, 'gif')) {
                $extension = 'gif';
            } elseif (str_contains($contentType, 'svg')) {
                $extension = 'svg';
            } elseif (str_contains($contentType, 'x-icon') || str_contains($contentType, 'vnd.microsoft.icon')) {
                $extension = 'ico';
            } else {
                $path = parse_url($url, PHP_URL_PATH);
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'ico'])) {
                    $extension = $ext;
                }
            }

            // Ensure destination directory exists
            $targetDir = public_path($subfolder);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            // Generate clean unique filename
            $filename = time() . '-' . Str::random(12) . '.' . $extension;
            $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

            file_put_contents($fullPath, $body);

            // Clean up old local file if provided and different
            if ($oldThumbnail && $oldThumbnail !== ($subfolder . '/' . $filename)) {
                self::deleteLocalThumbnail($oldThumbnail);
            }

            return $subfolder . '/' . $filename;
        } catch (\Throwable $e) {
            Log::warning("ThumbnailHelper: Failed to download thumbnail from {$url}: " . $e->getMessage());
            return $url;
        }
    }

    /**
     * Delete an old local thumbnail file from public storage if it exists.
     *
     * @param string|null $path
     */
    public static function deleteLocalThumbnail(?string $path): void
    {
        if (empty($path) || preg_match('~^(?:f|ht)tps?://~i', $path)) {
            return;
        }

        $cleanPath = ltrim($path, '/');
        $fullPath = public_path($cleanPath);

        if (File::exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
