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
    public function manifest(): JsonResponse
    {
        try {
            $appName = LandingPageSetting::get('sys_app_name', config('app.name', 'FileFusion'));
            $appDesc = LandingPageSetting::get('hero_description', 'Where links, files, and credentials come together in a secure workspace.');
        } catch (\Throwable $e) {
            $appName = config('app.name', 'FileFusion');
            $appDesc = 'Where links, files, and credentials come together in a secure workspace.';
        }
        $shortName = Str::limit($appName, 15, '');

        $manifest = [
            'name' => $appName . ' - Secure Files & Link Vault',
            'short_name' => $shortName,
            'description' => $appDesc,
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#FAFAFA',
            'theme_color' => '#E0392E',
            'categories' => ['productivity', 'utilities', 'business'],
            'icons' => [
                [
                    'src' => '/assets/icons/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/assets/icons/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/favicon.ico',
                    'sizes' => '48x48 32x32 16x16',
                    'type' => 'image/x-icon'
                ]
            ],
            'shortcuts' => [
                [
                    'name' => 'Upload File',
                    'short_name' => 'Upload',
                    'description' => 'Quickly upload a new document or media',
                    'url' => '/panel/upload',
                    'icons' => [
                        [
                            'src' => '/favicon.ico',
                            'sizes' => '48x48'
                        ]
                    ]
                ],
                [
                    'name' => 'Add Link',
                    'short_name' => 'Save Link',
                    'description' => 'Bookmark a new web link',
                    'url' => '/panel/add-links',
                    'icons' => [
                        [
                            'src' => '/favicon.ico',
                            'sizes' => '48x48'
                        ]
                    ]
                ],
                [
                    'name' => 'Credentials Vault',
                    'short_name' => 'Passwords',
                    'description' => 'Access your encrypted credentials',
                    'url' => '/panel/passwords',
                    'icons' => [
                        [
                            'src' => '/favicon.ico',
                            'sizes' => '48x48'
                        ]
                    ]
                ],
                [
                    'name' => 'New File / Note',
                    'short_name' => 'New Note',
                    'description' => 'Create a new text note or file',
                    'url' => '/panel/newfile',
                    'icons' => [
                        [
                            'src' => '/favicon.ico',
                            'sizes' => '48x48'
                        ]
                    ]
                ]
            ],
            'share_target' => [
                'action' => '/pwa/share-target',
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
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }

    /**
     * Handle incoming Web Share Target requests from OS share sheets.
     */
    public function shareTarget(Request $request)
    {
        $title = $request->input('title', '');
        $text = $request->input('text', '');
        $url = $request->input('url', '');

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

        // Check if a URL was shared
        if (!empty($url)) {
            return redirect()->route('panel.addlinkview', [
                'prefill_url' => $url,
                'prefill_name' => $title ?: $text ?: 'Shared Link'
            ])->with('info', 'Shared link captured! Review and save below.');
        }

        // If files were shared via share sheet
        if ($request->hasFile('shared_files')) {
            return redirect()->route('panel.uploadfile')->with('info', 'Files received from share sheet. Proceed with upload.');
        }

        // If plain text or note was shared
        if (!empty($text)) {
            return redirect()->route('panel.newfile', [
                'prefill_title' => $title ?: 'Shared Note',
                'prefill_content' => $text
            ])->with('info', 'Shared note captured! Edit and save your file.');
        }

        return redirect()->route('panel.dashboard')->with('success', 'Shared item processed.');
    }
}
