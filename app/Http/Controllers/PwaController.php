<?php

namespace App\Http\Controllers;

use App\Models\LandingPageSetting;
use App\Models\Link;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Generate dynamic Web App Manifest.
     */
    public function manifest(Request $request): JsonResponse
    {
        try {
            $appName = LandingPageSetting::get('sys_app_name', config('app.name', 'FileFusion'));
            $appDesc = LandingPageSetting::get('hero_description', 'Where links, files, and credentials come together in a secure workspace.');
        } catch (\Throwable $e) {
            $appName = config('app.name', 'FileFusion');
            $appDesc = 'Where links, files, and credentials come together in a secure workspace.';
        }
        $shortName = Str::limit($appName, 15, '');

        // Dynamically resolve base URL from the active HTTP request (supports subdirectories and LAN IPs)
        $baseUrl = rtrim($request->root(), '/');

        $manifest = [
            'name' => $appName . ' - Secure Files & Link Vault',
            'short_name' => $shortName,
            'description' => $appDesc,
            'start_url' => $baseUrl . '/',
            'scope' => $baseUrl . '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#FAFAFA',
            'theme_color' => '#E0392E',
            'categories' => ['productivity', 'utilities', 'business'],
            'icons' => [
                [
                    'src' => $baseUrl . '/assets/icons/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $baseUrl . '/assets/icons/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $baseUrl . '/favicon.ico',
                    'sizes' => '48x48 32x32 16x16',
                    'type' => 'image/x-icon'
                ]
            ],
            'shortcuts' => [
                [
                    'name' => 'Upload File',
                    'short_name' => 'Upload',
                    'description' => 'Quickly upload a new document or media',
                    'url' => $baseUrl . '/panel/upload',
                    'icons' => [
                        [
                            'src' => $baseUrl . '/favicon.ico',
                            'sizes' => '48x48'
                        ]
                    ]
                ],
                [
                    'name' => 'Add Link',
                    'short_name' => 'Save Link',
                    'description' => 'Bookmark a new web link',
                    'url' => $baseUrl . '/panel/add-links',
                    'icons' => [
                        [
                            'src' => $baseUrl . '/favicon.ico',
                            'sizes' => '48x48'
                        ]
                    ]
                ],
                [
                    'name' => 'Credentials Vault',
                    'short_name' => 'Passwords',
                    'description' => 'Access your encrypted credentials',
                    'url' => $baseUrl . '/panel/passwords',
                    'icons' => [
                        [
                            'src' => $baseUrl . '/favicon.ico',
                            'sizes' => '48x48'
                        ]
                    ]
                ],
                [
                    'name' => 'New File / Note',
                    'short_name' => 'New Note',
                    'description' => 'Create a new text note or file',
                    'url' => $baseUrl . '/panel/newfile',
                    'icons' => [
                        [
                            'src' => $baseUrl . '/favicon.ico',
                            'sizes' => '48x48'
                        ]
                    ]
                ]
            ],
            'share_target' => [
                'action' => $baseUrl . '/pwa/share-target',
                'method' => 'POST',
                'enctype' => 'multipart/form-data',
                'params' => [
                    'title' => 'title',
                    'text' => 'text',
                    'url' => 'url',
                    'files' => [
                        [
                            'name' => 'shared_files',
                            'accept' => ['image/*', 'video/*', 'audio/*', 'application/*', 'text/*']
                        ]
                    ]
                ]
            ]
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json; charset=UTF-8',
            'Cache-Control' => 'no-cache, private'
        ]);
    }

    /**
     * Handle incoming Web Share Target requests from OS share sheets.
     */
    public function shareTarget(Request $request)
    {
        $title = trim((string) $request->input('title', ''));
        $text = trim((string) $request->input('text', ''));
        $url = trim((string) $request->input('url', ''));

        // If not authenticated, redirect to login with flash notice
        if (!Auth::check()) {
            session([
                'pwa_share_pending' => [
                    'title' => $title,
                    'text' => $text,
                    'url' => $url,
                ]
            ]);
            return redirect()->route('login')->with('info', 'Please sign in to save the shared item to your FileFusion workspace.');
        }

        // 1. Process files shared via share sheet (Stage for upload form review)
        if ($request->hasFile('shared_files')) {
            $files = $request->file('shared_files');
            if (!is_array($files)) {
                $files = [$files];
            }

            $stagedFiles = [];
            foreach ($files as $f) {
                if ($f && $f->isValid()) {
                    $ogName = basename(str_replace(['../', '..\\', '%00'], '', $f->getClientOriginalName()));
                    $mime = $f->getMimeType() ?: 'application/octet-stream';
                    $size = $f->getSize();
                    $content = file_get_contents($f->getRealPath());
                    $base64 = base64_encode($content);

                    $stagedFiles[] = [
                        'name' => $ogName,
                        'type' => $mime,
                        'size' => $size,
                        'base64' => $base64,
                    ];
                }
            }

            if (!empty($stagedFiles)) {
                session(['pwa_staged_files' => $stagedFiles]);
                return redirect()->route('panel.uploadfile', ['intent' => 'pwa_share'])->with('info', count($stagedFiles) . ' file(s) captured from Share Sheet! Review settings and upload.');
            }
        }

        // 2. Check if a URL was shared or if text contains an embedded URL
        $extractedUrl = $url;
        if (empty($extractedUrl) && !empty($text)) {
            if (preg_match('/https?:\/\/[^\s]+/', $text, $matches)) {
                $extractedUrl = $matches[0];
            }
        }

        if (!empty($extractedUrl)) {
            return redirect()->route('panel.addlinkview', [
                'prefill_url' => $extractedUrl,
                'prefill_name' => $title ?: ($text !== $extractedUrl && !empty($text) ? $text : 'Shared Link')
            ])->with('info', 'Shared link captured! Review and save below.');
        }

        // 3. If plain text or note was shared
        if (!empty($text)) {
            return redirect()->route('panel.newfile', [
                'prefill_title' => $title ?: 'Shared Note',
                'prefill_content' => $text
            ])->with('info', 'Shared note captured! Edit and save your file.');
        }

        return redirect()->route('panel.dashboard')->with('success', 'Shared item processed.');
    }

    /**
     * Store and encrypt a single uploaded file from share sheet
     */
    public static function processSharedUpload($uploadedFile, $user)
    {
        $disk = config('filesystems.default', 'local');
        $basePath = config('panel.storage.path', 'private');
        $userDirectory = $user->getUserDirectory();
        $filePath = "{$basePath}/{$userDirectory}";

        if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($filePath)) {
            \Illuminate\Support\Facades\Storage::disk($disk)->makeDirectory($filePath, 0700, true);
        }

        $ogFileName = basename(str_replace(['../', '..\\', '%00'], '', $uploadedFile->getClientOriginalName()));
        $fileExtension = pathinfo($ogFileName, PATHINFO_EXTENSION) ?: 'bin';
        $fileType = $uploadedFile->getMimeType() ?: 'application/octet-stream';

        $randomName = Str::random(35) . '.' . $fileExtension;
        $finalPath = $filePath . "/{$randomName}.enc";
        $finalFullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($finalPath);

        // Encrypt file at rest using AES-256-GCM Envelope Encryption
        $encMetadata = \App\Helpers\FileEncryptor::encryptFile($uploadedFile->getRealPath(), $finalFullPath);
        $fileSize = $encMetadata['original_size'] ?? $uploadedFile->getSize();

        // Check storage quota
        if (method_exists($user, 'hasEnoughStorage') && !$user->hasEnoughStorage($fileSize)) {
            \App\Helpers\FileEncryptor::cryptoShred($finalFullPath);
            return null;
        }

        $fileData = [
            'name' => $ogFileName,
            'path' => $finalPath,
            'size' => $fileSize,
            'type' => $fileType,
            'user_id' => $user->id,
            'thumbnail' => null,
            'status' => '1',
            'is_hidden' => false,
        ];

        $savedFile = \App\Models\FileModal::create($fileData);
        if ($savedFile && method_exists($user, 'addStorageUsage')) {
            $user->addStorageUsage($fileSize);
        }

        return $savedFile;
    }
}
