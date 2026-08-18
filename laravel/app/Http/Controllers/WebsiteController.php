<?php

namespace App\Http\Controllers;

use App\Helpers\Encryptor;
use App\Helpers\FileEncryptor;
use App\Models\Category;
use App\Models\FileModal;
use App\Models\FileShare;
use App\Models\Links;
use App\Models\Password;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Container\Attributes\Log;

use function Illuminate\Log\log;

class WebsiteController extends Controller
{

    public function dashboard()
    {
        $user = Auth::user();

        // Get recent files (last 5, non-hidden only)
        $recentFiles = FileModal::where('user_id', $user->id)
            ->where('is_trashed', 0)
            ->where(function($q) {
                $q->where('is_hidden', 0)->orWhereNull('is_hidden');
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get shared files (shared with the user or shared by the user, non-hidden only)
        $sharedShares = FileShare::where(function ($q) use ($user) {
            $q->where('recipient_user_id', $user->id)
              ->orWhere('user_id', $user->id);
        })->valid()->with(['file', 'owner'])->latest()->limit(6)->get();

        $sharedFiles = $sharedShares->map(function ($share) use ($user) {
            if (!$share->file || $share->file->is_trashed || $share->file->is_hidden) {
                return null;
            }
            $file = $share->file;
            $file->shared_by_name = ($share->user_id == $user->id) ? 'Me' : ($share->owner->name ?? 'Shared User');
            $file->share_type = $share->share_type;
            return $file;
        })->filter()->unique('id')->values();


        // Get latest links (last 6, non-hidden only, decrypted)
        $latestLinks = Links::where('user_id', $user->id)
            ->where('is_trashed', 0)
            ->where(function($q) {
                $q->where('is_hidden', 0)->orWhereNull('is_hidden');
            })
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($link) {
                $link->title = Encryptor::decrypt($link->title);
                $link->url = Encryptor::decrypt($link->url);
                $link->description = Encryptor::decrypt($link->description);
                return $link;
            });

        // Calculate storage statistics
        $storageUsed = $user->storage_used;
        $storageQuota = $user->storage_quota;
        $storagePercentage = $user->getStorageUsagePercentage();

        // Calculate Overview KPI statistics (non-hidden only)
        $totalFiles = FileModal::where('user_id', $user->id)
            ->where('is_trashed', 0)
            ->where(function($q) {
                $q->where('is_hidden', 0)->orWhereNull('is_hidden');
            })
            ->count();

        $totalLinks = Links::where('user_id', $user->id)
            ->where('is_trashed', 0)
            ->where(function($q) {
                $q->where('is_hidden', 0)->orWhereNull('is_hidden');
            })
            ->count();

        $totalStarredLinks = Links::where('user_id', $user->id)
            ->where('is_trashed', 0)
            ->where(function($q) {
                $q->where('is_hidden', 0)->orWhereNull('is_hidden');
            })
            ->where('is_starred', 1)
            ->count();

        $totalPasswords = Password::where('user_id', $user->id)
            ->where('is_hidden', false)
            ->count();

        // Security indicators
        $twoFactorActive = (bool) ($user->two_factor_secret && $user->two_factor_confirmed_at) || (bool) ($user->two_factor_enabled ?? false);
        $vaultPassSet = !empty($user->vault_password_hash) || !empty($user->private_pass);

        // Check if unified vault session is actively unlocked
        $sessionTimeout = $user->getVaultSessionLifetime();
        $lastActivity = session('vault_group_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_links_last_activity') ?: session('hidden_passwords_last_activity');
        $isVaultAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
        $isVaultUnlocked = (bool) ($isVaultAuth && $lastActivity && (now()->timestamp - $lastActivity) <= $sessionTimeout);

        $hiddenFilesCount = 0;
        $hiddenLinksCount = 0;
        $hiddenPasswordsCount = 0;
        $vaultRemainingTime = 0;

        if ($isVaultUnlocked) {
            $hiddenFilesCount = FileModal::where('user_id', $user->id)->where('is_trashed', 0)->where('is_hidden', 1)->count();
            $hiddenLinksCount = Links::where('user_id', $user->id)->where('is_trashed', 0)->where('is_hidden', 1)->count();
            $hiddenPasswordsCount = Password::where('user_id', $user->id)->where('is_hidden', true)->count();
            $vaultRemainingTime = max(0, $sessionTimeout - (now()->timestamp - $lastActivity));
        }

        // Get latest passwords (last 4, non-hidden only, decrypted)
        $latestPasswords = Password::where('user_id', $user->id)
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($p) {
                $p->title = Encryptor::decrypt($p->title) ?? 'Untitled Account';
                $p->username = Encryptor::decrypt($p->username) ?? '';
                $p->url = Encryptor::decrypt($p->url) ?? '';
                return $p;
            });

        // Get starred links for quick access
        $starredLinks = Links::where('user_id', $user->id)
            ->where('is_trashed', 0)
            ->where(function($q) {
                $q->where('is_hidden', 0)->orWhereNull('is_hidden');
            })
            ->where('is_starred', 1)
            ->orderBy('updated_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($link) {
                $link->title = Encryptor::decrypt($link->title);
                $link->url = Encryptor::decrypt($link->url);
                return $link;
            });

        // Calculate storage breakdown by file type
        $userFiles = FileModal::where('user_id', $user->id)
            ->where('is_trashed', 0)
            ->where(function($q) {
                $q->where('is_hidden', 0)->orWhereNull('is_hidden');
            })
            ->select('id', 'name', 'type', 'size')
            ->get();

        // Calculate avatar file size if user has uploaded avatar
        $avatarBytes = 0;
        if (!empty($user->avatar)) {
            $avatarPath = storage_path('app/' . $user->avatar);
            if (file_exists($avatarPath)) {
                $avatarBytes = filesize($avatarPath);
            }
        }

        $storageCategories = [
            'documents' => ['label' => 'Documents',       'color' => '#3b82f6', 'bytes' => 0],
            'images'    => ['label' => 'Images',          'color' => '#10b981', 'bytes' => 0],
            'videos'    => ['label' => 'Videos',          'color' => '#f59e0b', 'bytes' => 0],
            'audio'     => ['label' => 'Audio',           'color' => '#8b5cf6', 'bytes' => 0],
            'profile'   => ['label' => 'Profile Picture', 'color' => '#ec4899', 'bytes' => $avatarBytes],
            'others'    => ['label' => 'Others',          'color' => '#64748b', 'bytes' => 0],
        ];

        $totalActiveFileBytes = $avatarBytes;
        foreach ($userFiles as $f) {
            $size = (int) ($f->size ?? 0);
            $totalActiveFileBytes += $size;
            $mime = (string) ($f->type ?? '');
            $ext = strtolower((string) ($f->extension ?? ''));

            if (str_starts_with($mime, 'image/')) {
                $storageCategories['images']['bytes'] += $size;
            } elseif (str_starts_with($mime, 'video/')) {
                $storageCategories['videos']['bytes'] += $size;
            } elseif (str_starts_with($mime, 'audio/')) {
                $storageCategories['audio']['bytes'] += $size;
            } elseif (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf', 'md'])) {
                $storageCategories['documents']['bytes'] += $size;
            } else {
                $storageCategories['others']['bytes'] += $size;
            }
        }

        foreach ($storageCategories as $k => $cat) {
            $storageCategories[$k]['formatted'] = BytetoSize($cat['bytes']);
            $storageCategories[$k]['percent'] = $totalActiveFileBytes > 0
                ? round(($cat['bytes'] / $totalActiveFileBytes) * 100, 1)
                : 0;
        }

        $data = [
            'user' => $user,
            'recentFiles' => $recentFiles,
            'sharedFiles' => $sharedFiles,
            'latestLinks' => $latestLinks,
            'latestPasswords' => $latestPasswords,
            'starredLinks' => $starredLinks,
            'totalFiles' => $totalFiles,
            'totalLinks' => $totalLinks,
            'totalStarredLinks' => $totalStarredLinks,
            'totalPasswords' => $totalPasswords,
            'twoFactorActive' => $twoFactorActive,
            'vaultPassSet' => $vaultPassSet,
            'isVaultUnlocked' => $isVaultUnlocked,
            'hiddenFilesCount' => $hiddenFilesCount,
            'hiddenLinksCount' => $hiddenLinksCount,
            'hiddenPasswordsCount' => $hiddenPasswordsCount,
            'vaultRemainingTime' => $vaultRemainingTime,
            'storageCategories' => $storageCategories,
            'totalActiveFileBytes' => $totalActiveFileBytes,
            'storageUsed' => $user->getStorageUsedFormatted(),
            'storageQuota' => $user->getStorageQuotaFormatted(),
            'storagePercentage' => $storagePercentage,
        ];

        return view('panel.dashboard', $data);
    }


    // public function filelist(Request $request)
    // {
    //     $pageItems = 25;
    //     $query = $request->get('q');

    //     // Always apply the filter logic
    //     $filesQuery = FileModal::where('user_id', Auth::id())
    //         ->when($query, function ($qBuilder) use ($query) {
    //             $qBuilder->where('name', 'like', "%$query%");
    //         })
    //         ->orderBy('id', 'DESC');

    //     // Always apply query param to pagination links
    //     $files = $filesQuery->paginate($pageItems)->appends(['q' => $query]);

    //     // Return AJAX view for dynamic loading
    //     if ($request->ajax()) {
    //         return view('panel.ajax.file_load', compact('files'));
    //     }

    //     // Return full page view for normal load
    //     return view('panel.filelist', compact('files'));
    // }


    public function filelist(Request $request)
    {
        $pageItems = \App\Helpers\SettingHelper::getItemsPerPage(24);

        $q = $request->get('q');
        $type = $request->get('type'); // e.g. image, video, audio, document

        $filesQuery = FileModal::where('user_id', Auth::id())
            ->where('is_hidden', false) // Don't show hidden files by default
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
                        // ->orWhere('type', 'like', 'application/octet-stream');
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
            return view('panel.ajax.file_load', compact('files'));
        }

        return view('panel.filelist', compact('files'));
    }




    public function uploadfile()
    {
        // Categories power the "Details" panel on the upload screen.
        $categories = Category::where('user_id', Auth::id())
            ->whereIn('type', ['files', 'both'])
            ->orderBy('title')
            ->get();

        return view('panel.uploadfile', compact('categories'));
    }

    public function newfile(Request $request, $fileId = null)
    {
        $file = null;
        $content = '';

        // If we're editing an existing file (from edit link)
        if ($fileId) {
            try {
                $id = decrypt($fileId);
            } catch (\Exception $e) {
                $id = $fileId;
            }
            $file = FileModal::where('user_id', Auth::id())->findOrFail($id);

            // Load decrypted file content if it exists
            $disk = 'local';
            if (Storage::disk($disk)->exists($file->path)) {
                try {
                    $content = \App\Helpers\FileEncryptor::decryptFileToString(Storage::disk($disk)->path($file->path));
                } catch (\Exception $e) {
                    $content = Storage::disk($disk)->get($file->path);
                }
            }

            // Ensure clean UTF-8 encoding
            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            }
        }

        return view('panel.newfile', compact('file', 'content'));
    }


    public function trashview($type)
    {
        // return view('panel.addlinkcategory');

        $avl_types = ['files', 'links', 'passwords'];
        if (!in_array($type, $avl_types)) {
            return redirect(route('panel.dashboard'));
        }

        $files = null;
        $links = null;
        // Credential vault has no table yet, so its trash tab renders empty.
        $passwords = collect();

        if ($type === 'files') {
            // Load trashed files for the current user
            $files = FileModal::where('user_id', Auth::id())
                ->where('is_trashed', 1)
                ->orderBy('deleted_at', 'desc')
                ->withTrashed()
                ->get();
        } elseif ($type === 'links') {
            // Load trashed links for the current user
            $links = Links::where('user_id', Auth::id())
                ->onlyTrashed()
                ->with('category')
                ->orderBy('deleted_at', 'desc')
                ->get();
        } elseif ($type === 'passwords') {
            // Load trashed passwords for the current user
            $passwords = Password::where('user_id', Auth::id())
                ->onlyTrashed()
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->map(function ($p) {
                    $p->title = Encryptor::decrypt($p->title) ?? '';
                    $p->username = Encryptor::decrypt($p->username) ?? '';
                    return $p;
                });
        }

        return view('panel.trash', compact('type', 'files', 'links', 'passwords'));
    }

    public function sharemodal(Request $request)
    {
        $type = $request->get('type');
        $id = '';
        $link = '';
        $name = '';
        $file = null;
        $publicShare = null;
        $anonShare = null;
        $privateShares = collect();

        if ($request->has('id')) {
            try {
                $id = decrypt($request->get('id'));
            } catch (\Exception $e) {
                try {
                    $id = Crypt::decrypt($request->get('id'));
                } catch (\Exception $ex) {
                    $id = $request->get('id');
                }
            }
        }

        if ($type == 'file' && $id) {
            $file = FileModal::where('id', $id)->where('user_id', Auth::id())->first();
            if ($file) {
                $name = $file->name;
                $publicShare = FileShare::where('file_id', $file->id)->where('share_type', 'public_link')->first();
                $anonShare = FileShare::where('file_id', $file->id)->where('share_type', 'anonymous_qr')->first();
                $privateShares = FileShare::where('file_id', $file->id)->where('share_type', 'private_user')->with('recipient')->get();
                $link = $publicShare ? url('/s/' . $publicShare->share_token) : '';
            }
        }

        return view('panel.ajax.share_modal', compact('type', 'id', 'name', 'file', 'link', 'publicShare', 'anonShare', 'privateShares'));
    }

    /**
     * In-Browser Multi-Format Preview Modal Content.
     */
    public function previewmodal(Request $request)
    {
        $id = null;
        if ($request->has('id')) {
            try {
                $id = decrypt($request->get('id'));
            } catch (\Exception $e) {
                try {
                    $id = Crypt::decrypt($request->get('id'));
                } catch (\Exception $ex) {
                    $id = $request->get('id');
                }
            }
        }

        $file = FileModal::where('is_trashed', 0)->findOrFail($id);

        $isOwner = $file->user_id == Auth::id();
        $isSharedRecipient = FileShare::where('file_id', $file->id)
            ->where('recipient_user_id', Auth::id())
            ->where('share_type', 'private_user')
            ->exists();
        $isSuperAdmin = Auth::user() && Auth::user()->isSuperAdmin();

        if (!$isOwner && !$isSharedRecipient && !$isSuperAdmin) {
            return response()->json(['ok' => 0, 'info' => 'Unauthorized'], 403);
        }

        $type = $file->type ?? 'application/octet-stream';
        $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));

        $category = 'other';
        if (str_starts_with($type, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'])) {
            $category = 'image';
        } elseif (str_starts_with($type, 'video/') || in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'mkv'])) {
            $category = 'video';
        } elseif (str_starts_with($type, 'audio/') || in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'flac'])) {
            $category = 'audio';
        } elseif ($type === 'application/pdf' || $ext === 'pdf') {
            $category = 'pdf';
        } elseif (in_array($ext, ['csv', 'tsv'])) {
            $category = 'spreadsheet';
        } elseif (in_array($ext, ['txt', 'md', 'json', 'js', 'ts', 'html', 'css', 'py', 'go', 'php', 'sql', 'sh', 'rs', 'cpp', 'c', 'java', 'xml', 'yaml', 'yml', 'env', 'log', 'ini'])) {
            $category = 'code';
        }

        $previewUrl = route('panel.previewFile', encrypt($file->id));
        $downloadUrl = route('panel.downloadFile', encrypt($file->id));

        $codeContent = null;
        if ($category === 'code' && Storage::disk('local')->exists($file->path)) {
            try {
                $codeContent = FileEncryptor::decryptFileToString(Storage::disk('local')->path($file->path));
                if (strlen($codeContent) > 51200) {
                    $codeContent = substr($codeContent, 0, 51200) . "\n\n... [File truncated for preview] ...";
                }
            } catch (\Exception $e) {
                $codeContent = 'Unable to decrypt preview.';
            }
        }

        return view('panel.ajax.file_preview_modal', compact('file', 'category', 'previewUrl', 'downloadUrl', 'codeContent', 'ext'));
    }

    public function deletemodal(Request $request)
    {

        $filename = '';
        $fileId = '';
        if ($request->has('file')) {
            $fileId = decrypt($request->get('file'));
            $FileData = FileModal::where('id', $fileId)->first();
            if ($FileData->exists()) {
                $filename = $FileData->name;
            }
        }

        return view('panel.ajax.delete_modal', compact('filename', 'fileId'));
    }




    /**
     * Check if a URL is safe from SSRF attacks.
     * Blocks private/internal networks, loopback addresses, cloud metadata endpoints, and non-http/https schemes.
     */
    public function isSafeUrl($url)
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, ['http', 'https'])) {
            return false;
        }

        $host = strtolower($parts['host']);

        // Explicitly block localhost, metadata services and internal names
        $blockedHosts = [
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            '::1',
            '169.254.169.254',
            'metadata.google.internal',
            'instance-data',
        ];

        if (in_array($host, $blockedHosts) || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return false;
        }

        // Resolve DNS and check if IP is private or reserved
        $ips = @gethostbynamel($host);
        if (!$ips || !is_array($ips)) {
            return false;
        }

        foreach ($ips as $ip) {
            // Reject private, loopback and reserved IP ranges
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    public function getWebScreenshot($website_url = null)
    {

        // calculating time
        $start = time();

        $response = [
            'code' => 400,
            'message' => 'Unable to process The Request',
            'ss_path' => null,
            'reason' => 'Website URL is Required!! with base64 encoding',
            'ss_name' => null,
            'time_taken' => null
        ];
        if ($website_url == null) {
            $response['message'] = 'Website URL is Required!!';
            return response()->json($response);
        }

        $website_url = base64_decode($website_url);

        if (!$this->isSafeUrl($website_url)) {
            $response['message'] = 'Target URL is invalid or points to an internal/restricted network address.';
            $response['reason'] = 'SSRF protection blocked the request.';
            return response()->json($response);
        }

        try {
            // Define the path to the public/Browsershot directory and set a unique filename
            $directory = public_path('Browsershot');
            $fileName = time() . '-' . Str::uuid()->toString() . '-screenshot.jpg';
            $filePath = $directory . '/' . $fileName;

            // Create the Browsershot directory if it doesn't already exist
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Capture screenshot via fast Cloud APIs (No NPM/Node/Chromium server dependencies)
            $imageBinary = null;

            // Strategy 1: Microlink Cloud Screenshot API
            try {
                $microlinkUrl = "https://api.microlink.io/?url=" . urlencode($website_url) . "&screenshot=true&meta=false&embed=screenshot.url";
                $apiRes = \Illuminate\Support\Facades\Http::timeout(12)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])->get($microlinkUrl);

                if ($apiRes->successful() && strlen($apiRes->body()) > 1000) {
                    $imageBinary = $apiRes->body();
                }
            } catch (\Throwable $e) {}

            // Strategy 2: WordPress mShots API Fallback
            if (!$imageBinary) {
                try {
                    $mshotsUrl = "https://s0.wp.com/mshots/v1/" . urlencode($website_url) . "?w=1280&h=720";
                    $apiRes = \Illuminate\Support\Facades\Http::timeout(10)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                    ])->get($mshotsUrl);

                    if ($apiRes->successful() && strlen($apiRes->body()) > 1000) {
                        $imageBinary = $apiRes->body();
                    }
                } catch (\Throwable $e) {}
            }

            // Strategy 3: Thum.io Fallback
            if (!$imageBinary) {
                try {
                    $thumUrl = "https://image.thum.io/get/width/1200/crop/720/" . $website_url;
                    $apiRes = \Illuminate\Support\Facades\Http::timeout(10)->get($thumUrl);
                    if ($apiRes->successful() && strlen($apiRes->body()) > 1000) {
                        $imageBinary = $apiRes->body();
                    }
                } catch (\Throwable $e) {}
            }

            if ($imageBinary) {
                file_put_contents($filePath, $imageBinary);
                $response['code'] = 200;
                $response['message'] = 'Screenshot Captured Successfully';
                $response['ss_path'] = 'Browsershot/' . $fileName;
                $response['ss_name'] = $fileName;
                $response['reason'] = null;
            } else {
                $response['message'] = 'Unable to capture screenshot from target website.';
                $response['reason'] = 'All cloud screenshot API services timed out or target site refused connections.';
            }
        } catch (\Throwable $th) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $response['message'] = 'Unable to Capture Screenshot: ' . $th->getMessage();
        } finally {
            $end = time();
            $executionTime = $end - $start;
            $response['time_taken'] = $executionTime; // seconds
            return response()->json($response);
        }
    }



    public function globalSearch(Request $request)
    {
        $query = $request->get('q', '');
        $includeTrashed = $request->has('include_trashed');
        $includeHidden = $request->has('include_hidden');
        $tab = $request->get('tab', 'all');

        $files = collect();
        $links = collect();
        $passwords = collect();
        $filesCount = 0;
        $linksCount = 0;
        $passwordsCount = 0;

        if (!empty($query)) {
            // Search Files
            if ($tab === 'all' || $tab === 'files') {
                $filesQuery = FileModal::where('user_id', Auth::id())
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', '%' . $query . '%')
                            ->orWhere('type', 'like', '%' . $query . '%')
                            ->orWhere('path', 'like', '%' . $query . '%');
                    });

                if (!$includeHidden) {
                    $filesQuery->where('is_hidden', false);
                }

                if ($includeTrashed) {
                    $filesQuery->withTrashed();
                } else {
                    $filesQuery->where('is_trashed', false);
                }

                $files = $filesQuery->orderBy('updated_at', 'desc')->limit(50)->get();
                $filesCount = $files->count();
            }

            // Search Links
            if ($tab === 'all' || $tab === 'links') {
                $linksQuery = Links::where('user_id', Auth::id());

                if (!$includeHidden) {
                    $linksQuery->where('is_hidden', false);
                }

                if ($includeTrashed) {
                    $linksQuery->withTrashed();
                }

                $rawLinks = $linksQuery->with('category')->orderBy('updated_at', 'desc')->limit(50)->get();

                $searchLower = strtolower($query);
                $links = $rawLinks->filter(function ($l) use ($searchLower) {
                    return str_contains(strtolower($l->title ?? ''), $searchLower)
                        || str_contains(strtolower($l->url ?? ''), $searchLower)
                        || str_contains(strtolower($l->description ?? ''), $searchLower)
                        || str_contains(strtolower($l->tags ?? ''), $searchLower);
                })->values();

                $linksCount = $links->count();
            }


            // Search Passwords
            if ($tab === 'all' || $tab === 'passwords') {
                $passwordsQuery = Password::where('user_id', Auth::id());

                if (!$includeHidden) {
                    $passwordsQuery->where('is_hidden', false);
                }

                if ($includeTrashed) {
                    $passwordsQuery->withTrashed();
                }

                $rawPasswords = $passwordsQuery->orderBy('updated_at', 'desc')->limit(50)->get();

                $searchLower = strtolower($query);
                $passwords = $rawPasswords->map(function ($p) {
                    $p->title = Encryptor::decrypt($p->title) ?? '';
                    $p->username = Encryptor::decrypt($p->username) ?? '';
                    $p->url = Encryptor::decrypt($p->url) ?? '';
                    $p->notes = Encryptor::decrypt($p->notes) ?? '';
                    return $p;
                })->filter(function ($p) use ($searchLower) {
                    return str_contains(strtolower($p->title), $searchLower)
                        || str_contains(strtolower($p->username), $searchLower)
                        || str_contains(strtolower($p->url), $searchLower)
                        || str_contains(strtolower($p->notes), $searchLower);
                })->values();

                $passwordsCount = $passwords->count();
            }
        }

        $totalResults = $filesCount + $linksCount + $passwordsCount;

        return view('panel.global-search', compact(
            'query',
            'files',
            'links',
            'passwords',
            'filesCount',
            'linksCount',
            'passwordsCount',
            'totalResults'
        ));
    }

    public function scanStorage(Request $request)
    {
        // 1. Orphan Screenshots
        $directory = public_path('Browsershot');
        $files = is_dir($directory) ? array_diff(scandir($directory), ['.', '..']) : [];

        $usedFiles = [];
        $allLinks = Links::withTrashed()->get();
        foreach ($allLinks as $link) {
            if ($link->thumbnail) {
                $usedFiles[] = basename($link->thumbnail);
            }
        }

        $unusedFiles = [];
        foreach ($files as $file) {
            if (!in_array($file, $usedFiles)) {
                $unusedFiles[] = $file;
            }
        }

        $screenshotCount = count($unusedFiles);
        $screenshotBytes = 0;
        foreach ($unusedFiles as $file) {
            $filePath = $directory . '/' . $file;
            if (file_exists($filePath)) {
                $screenshotBytes += filesize($filePath);
            }
        }

        // 2. Soft-deleted Trashed Files Breakdown
        $trashedFiles = FileModal::onlyTrashed()->orWhere('is_trashed', 1)->with('user')->get();

        $trashedTotalCount = count($trashedFiles);
        $trashedTotalBytes = 0;

        $trashedFullCount = 0;
        $trashedFullBytes = 0;

        $trashedNonAdminCount = 0;
        $trashedNonAdminBytes = 0;

        foreach ($trashedFiles as $tf) {
            $size = (int)$tf->size;
            $trashedTotalBytes += $size;
            $user = $tf->user;

            if ($user) {
                if ($user->getStorageUsagePercentage() >= 90) {
                    $trashedFullCount++;
                    $trashedFullBytes += $size;
                }
                if (!$user->isSuperAdmin() && (int)$user->account_type !== 1) {
                    $trashedNonAdminCount++;
                    $trashedNonAdminBytes += $size;
                }
            } else {
                $trashedNonAdminCount++;
                $trashedNonAdminBytes += $size;
            }
        }

        // 3. Expired Share Links
        $expiredShareCount = FileShare::where('expires_at', '<', now())->count();

        $stats = [
            'screenshots' => [
                'count' => $screenshotCount,
                'bytes' => $screenshotBytes,
                'formatted' => User::formatBytes($screenshotBytes),
            ],
            'trashed_full' => [
                'count' => $trashedFullCount,
                'bytes' => $trashedFullBytes,
                'formatted' => User::formatBytes($trashedFullBytes),
            ],
            'trashed_non_admin' => [
                'count' => $trashedNonAdminCount,
                'bytes' => $trashedNonAdminBytes,
                'formatted' => User::formatBytes($trashedNonAdminBytes),
            ],
            'trashed_total' => [
                'count' => $trashedTotalCount,
                'bytes' => $trashedTotalBytes,
                'formatted' => User::formatBytes($trashedTotalBytes),
            ],
            'expired_shares' => [
                'count' => $expiredShareCount,
            ],
        ];

        return response()->json([
            'status' => 'success',
            'ok' => 1,
            'stats' => $stats,
        ]);
    }

    public function cleanStoragePage(Request $request)
    {
        $scanResponse = $this->scanStorage($request);
        $scannedData = json_decode($scanResponse->getContent(), true);
        $scannedStats = $scannedData['stats'] ?? [];

        return view('panel.storage-cleaner', compact('scannedStats'));
    }


    public function cleanStorage(Request $request)
    {
        $target = $request->input('target', 'all'); // 'all', 'screenshots', 'trashed', 'shares', 'cache'

        $deletedFilesCount = 0;
        $freedSpaceBytes = 0;
        $actionsSummary = [];

        $screenshotCount = 0;
        $screenshotBytes = 0;
        $trashedCount = 0;
        $trashedBytes = 0;

        // 1. Clean Orphan Browsershot Screenshots
        if (in_array($target, ['all', 'screenshots'])) {
            $directory = public_path('Browsershot');
            $files = is_dir($directory) ? array_diff(scandir($directory), ['.', '..']) : [];

            $usedFiles = [];
            $allLinks = Links::withTrashed()->get();
            foreach ($allLinks as $link) {
                if ($link->thumbnail) {
                    $usedFiles[] = basename($link->thumbnail);
                }
            }

            foreach ($files as $file) {
                if (!in_array($file, $usedFiles)) {
                    $filePath = $directory . '/' . $file;
                    if (file_exists($filePath)) {
                        $size = filesize($filePath);
                        if (unlink($filePath)) {
                            $screenshotCount++;
                            $screenshotBytes += $size;
                            $deletedFilesCount++;
                            $freedSpaceBytes += $size;
                        }
                    }
                }
            }
            if ($screenshotCount > 0) {
                $actionsSummary[] = "{$screenshotCount} Orphan Screenshots Removed (" . User::formatBytes($screenshotBytes) . " freed)";
            } else {
                $actionsSummary[] = "Orphan Screenshots: 0 files to clean";
            }
        }

        // 2. Purge Soft-deleted Trashed Files (ONLY WHEN EXPLICITLY CHECKED BY ADMIN)
        $trashFilter = $request->input('trash_filter', null); // 'storage_full', 'non_admin', 'both', 'all_manual'

        if (in_array($target, ['all', 'trashed']) && !empty($trashFilter)) {
            $trashedFiles = FileModal::onlyTrashed()->orWhere('is_trashed', 1)->with('user')->get();

            foreach ($trashedFiles as $tf) {
                $user = $tf->user;
                $shouldPurge = false;

                if (!$user) {
                    $shouldPurge = in_array($trashFilter, ['all_manual', 'non_admin', 'both']);
                } else {
                    $isFull = $user->getStorageUsagePercentage() >= 90;
                    $isNonAdmin = !$user->isSuperAdmin() && (int)$user->account_type !== 1;

                    if ($trashFilter === 'storage_full') {
                        $shouldPurge = $isFull;
                    } elseif ($trashFilter === 'non_admin') {
                        $shouldPurge = $isNonAdmin;
                    } elseif ($trashFilter === 'both') {
                        $shouldPurge = ($isFull || $isNonAdmin);
                    } elseif ($trashFilter === 'all_manual') {
                        $shouldPurge = true;
                    }
                }

                if ($shouldPurge) {
                    if ($tf->path && Storage::exists($tf->path)) {
                        $freedSpaceBytes += (int) $tf->size;
                        $trashedBytes += (int) $tf->size;
                        Storage::delete($tf->path);
                    }
                    // Reduce owner user storage usage
                    if ($user) {
                        $user->reduceStorageUsage($tf->size);
                        // Dispatch File Removal Report (Not for Self-Delete)
                        if ($tf->user_id !== Auth::id()) {
                            \App\Helpers\MailHelper::sendNotification($user->email, 'file_deleted', [
                                'user_name' => $user->name,
                                'file_name' => $tf->name,
                                'deleted_by' => 'System Storage Cleanup Engine (Admin Selective Filter)',
                                'reason' => "Trash retention purge policy for filter option '{$trashFilter}'",
                            ]);
                        }
                    }
                    $tf->forceDelete();
                    $trashedCount++;
                    $deletedFilesCount++;
                }
            }

            if ($trashedCount > 0) {
                $actionsSummary[] = "{$trashedCount} Trashed Files Purged [Filter: {$trashFilter}] (" . User::formatBytes($trashedBytes) . " freed)";
            } else {
                $actionsSummary[] = "Trashed Files: 0 matching files found for filter '{$trashFilter}'";
            }
        } elseif (in_array($target, ['all', 'trashed']) && empty($trashFilter)) {
            $actionsSummary[] = "Trashed Files Skipped (No trash purge option selected)";
        }


        // 3. Purge Expired Share Records
        if (in_array($target, ['all', 'shares'])) {
            $expiredShares = FileShare::where('expires_at', '<', now())->delete();
            $actionsSummary[] = "{$expiredShares} Expired Share Links Cleared";
        }

        // 4. Clear Blade View & System Cache
        if (in_array($target, ['all', 'cache'])) {
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            $actionsSummary[] = "Application & Blade View Cache Cleared";
        }


        $humanReadableFreed = User::formatBytes($freedSpaceBytes);
        $summaryText = implode(' | ', $actionsSummary);
        $fullDetailMessage = "Storage Cleanup Complete! Total Freed: {$humanReadableFreed} across {$deletedFilesCount} file(s). Details: {$summaryText}";

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'status' => 'success',
                'ok' => 1,
                'message' => $fullDetailMessage,
                'summary' => $actionsSummary,
                'deleted_files_count' => $deletedFilesCount,
                'freed_space_bytes' => $freedSpaceBytes,
                'freed_space_human_readable' => $humanReadableFreed,
                'screenshot_count' => $screenshotCount,
                'screenshot_freed' => User::formatBytes($screenshotBytes),
                'trashed_count' => $trashedCount,
                'trashed_freed' => User::formatBytes($trashedBytes),
            ]);
        }

        return redirect()->back()->with('success', $fullDetailMessage);
    }






    // public function ashish()
    // {
    //     // echo "<pre>";
    //     // print_r(Auth::user());
    //     // echo Str::uuid()->toString();

    //     // Fetch data from API
    //     $apiUrl = 'https://theashishkumar.in/api/view'; // Replace with actual API URL
    //     $response = file_get_contents($apiUrl);
    //     $data = json_decode($response, true);

    //     return response()->json($data);

    // if ($data && is_array($data)) {
    //     $insertedCount = 0;


    //     foreach ($data as $item) {
    //         // Create new link record

    //         $screenshotResponse = $this->getWebScreenshot(base64_encode($item['url']));
    //         if ($screenshotResponse->getData()->ss_path != null){
    //             $thumbnail = $screenshotResponse->getData()->ss_path;
    //             // echo 'Thumbnail generated successfully for URL ' . $request->get('url') . ' at ' . $screenshotResponse->getData()->ss_path;
    //         }

    //         $link = new Links();
    //         $link->user_id = Auth::id();
    //         $link->title = $item['title'] ?? '';
    //         $link->url = $item['url'] ?? '';
    //         $link->description = $item['description'] ?? '';
    //         $link->tags = $item['tags'] ?? '';
    //         $link->thumbnail = $thumbnail ?? null;
    //         $link->save();

    //         $insertedCount++;
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => "Successfully inserted {$insertedCount} links",
    //         'inserted_count' => $insertedCount
    //     ]);
    // } else {
    //     return response()->json([
    //         'status' => 'error',
    //         'message' => 'Failed to fetch or parse API data'
    //     ]);
    // }

    // }


    public function ashish()
    {
        \Illuminate\Support\Facades\Log::info("Ashish function called");

        $apiUrl = 'https://theashishkumar.in/api/view';
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);
        \Illuminate\Support\Facades\Log::info('Fetched Data from API', ['data_count' => is_array($data) ? count($data) : 0]);

        if ($data && is_array($data)) {
            $client = new Client();
            $promises = [];
            $processedCount = 0;
            $totalCount = count($data);

            foreach ($data as $index => $item) {
                $promises[] = $client->postAsync(route('panel.getWebScreenshot'), [
                    'form_params' => ['url' => base64_encode($item['url'])]
                ])->then(function ($response) use ($item, &$processedCount, $totalCount, $index) {
                    $screenshotData = json_decode($response->getBody(), true);
                    $thumbnail = $screenshotData['ss_path'] ?? null;

                    Links::create([
                        'user_id' => Auth::id(),
                        'title' => $item['title'] ?? '',
                        'url' => $item['url'] ?? '',
                        'description' => $item['description'] ?? '',
                        'tags' => $item['tags'] ?? '',
                        'thumbnail' => $thumbnail,
                    ]);

                    $processedCount++;
                    log("Processed item {$processedCount}/{$totalCount}", [
                        'url' => $item['url'],
                        'thumbnail' => $thumbnail,
                        'progress_percentage' => round(($processedCount / $totalCount) * 100, 2)
                    ]);
                });
            }

            // Wait for all promises to finish
            Promise\Utils::unwrap($promises);

            log("All items processed successfully", ['total_processed' => $processedCount]);
            return response()->json(['status' => 'success', 'processed_count' => $processedCount]);
        }

        return response()->json(['status' => 'error']);
    }
}
