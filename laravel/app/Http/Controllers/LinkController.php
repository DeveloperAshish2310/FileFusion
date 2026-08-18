<?php

namespace App\Http\Controllers;

use App\Helpers\Encryptor;
use App\Models\Category;
use App\Models\Links;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Illuminate\Log\log;
use function PHPSTORM_META\map;

class LinkController extends Controller
{

    public function index(Request $request)
    {
        $query = Links::where('user_id', Auth::id())
            ->where('is_hidden', false)
            ->with('category');

        // Filter by category
        if ($request->filled('category')) {
            try {
                $categoryId = decrypt($request->category);
                $query->where('category_id', $categoryId);
            } catch (\Exception $e) {}
        }

        // Filter by starred
        if ($request->filled('starred') && $request->starred == '1') {
            $query->where('is_starred', true);
        }

        $allLinks = $query->orderBy('created_at', 'desc')->get();

        // Decrypt fields
        $decryptedAll = $allLinks->map(function ($link) {
            $link->title = Encryptor::decrypt($link->title);
            $link->url = Encryptor::decrypt($link->url);
            $link->description = Encryptor::decrypt($link->description);
            $link->tags = Encryptor::decrypt($link->tags);
            return $link;
        });

        // Extract unique tags that are actually used across non-hidden links (case-insensitive)
        $allUserLinksForTags = Links::where('user_id', Auth::id())
            ->where('is_hidden', false)
            ->whereNotNull('tags')
            ->select('tags')
            ->get();

        $tags = [];
        foreach ($allUserLinksForTags as $l) {
            $decryptedTags = Encryptor::decrypt($l->tags);
            if (!empty($decryptedTags)) {
                $raw = explode(',', $decryptedTags);
                foreach ($raw as $t) {
                    $clean = strtolower(trim($t));
                    if ($clean !== '' && !in_array($clean, $tags)) {
                        $tags[] = $clean;
                    }
                }
            }
        }
        sort($tags);

        // Apply Tag & Search filters
        $tagFilter = $request->filled('tag') ? strtolower(trim($request->tag)) : null;
        $searchTerm = $request->filled('search') ? strtolower(trim($request->search)) : null;

        $filteredLinks = $decryptedAll->filter(function ($link) use ($searchTerm, $tagFilter) {
            if ($tagFilter) {
                if (empty($link->tags)) return false;
                $linkTags = array_map('trim', explode(',', strtolower($link->tags)));
                if (!in_array($tagFilter, $linkTags)) return false;
            }

            if ($searchTerm) {
                return str_contains(strtolower($link->title ?? ''), $searchTerm) ||
                       str_contains(strtolower($link->url ?? ''), $searchTerm) ||
                       str_contains(strtolower($link->description ?? ''), $searchTerm) ||
                       str_contains(strtolower($link->tags ?? ''), $searchTerm);
            }

            return true;
        });

        // Paginate manually using persistent items_per_page setting
        $perPage = \App\Helpers\SettingHelper::getItemsPerPage(12);
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $links = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredLinks->slice($offset, $perPage)->values(),
            $filteredLinks->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get ONLY categories that are actually used by non-hidden links
        $usedCategoryIds = Links::where('user_id', Auth::id())
            ->where('is_hidden', false)
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->unique();

        $categories = Category::whereIn('id', $usedCategoryIds)
            ->where('user_id', Auth::id())
            ->visible()
            ->orderBy('title', 'asc')
            ->get();

        // If it's an AJAX request, return only the cards
        if ($request->ajax()) {
            return view('panel.ajax.links_card_load', compact('links'))->render();
        }

        return view('panel.linklist', compact('links', 'categories', 'tags'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'both');

        $categories = Category::where('user_id', Auth::id())
            ->when($type !== 'both', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->visible()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('panel.addlink', compact('categories'));
    }

    public function store(Request $request)
    {

        // magicstring($request->all());
        // die;
        $validated = $request->validate([
            'url' => 'required|url|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|string',
            'thumbnail' => 'nullable|url|max:255',
            'thumbnail_file' => 'nullable|image|max:5120',
            'categories' => 'nullable|string',
            'isNew' => 'nullable|boolean',
            'isHidden' => 'nullable|boolean',
        ]);

        try {
            // Decrypt category_id if provided
            $categoryId = null;
            if (!empty($validated['category_id'])) {
                try {
                    $categoryId = decrypt($validated['category_id']);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['category_id' => 'Invalid category selected'])->withInput();
                }
            }

            // 1. Direct image file upload
            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = time() . '-' . \Illuminate\Support\Str::random(12) . '.' . $ext;
                $targetDir = public_path('thumbnails/links');
                if (!\Illuminate\Support\Facades\File::exists($targetDir)) {
                    \Illuminate\Support\Facades\File::makeDirectory($targetDir, 0755, true);
                }
                $file->move($targetDir, $filename);
                $validated['thumbnail'] = 'thumbnails/links/' . $filename;
            }
            // 2. Automated screenshot
            elseif (empty($validated['thumbnail']) && ($request->get('makeThumbnailWithBrowershot') == '1' || $request->boolean('makeThumbnailWithBrowershot'))) {
                $webSiteController = new WebsiteController();
                $screenshotResponse = $webSiteController->getWebScreenshot(base64_encode($request->get('url')));
                if ($screenshotResponse->getData()->ss_path != null){
                    $validated['thumbnail'] = $screenshotResponse->getData()->ss_path;
                } else {
                    \Illuminate\Support\Facades\Log::warning('Thumbnail generation failed for URL ' . $request->get('url') . ' with Message: ' . ($screenshotResponse->getData()->message ?? 'Unknown'));
                }
            }
            // 3. Remote URL download
            elseif (!empty($validated['thumbnail'])) {
                $validated['thumbnail'] = \App\Helpers\ThumbnailHelper::downloadAndSave($validated['thumbnail'], 'thumbnails/links');
            }

            // Normalize Tagify tags output
            $cleanTags = $validated['categories'] ?? null;
            if (!empty($cleanTags)) {
                $decoded = json_decode($cleanTags, true);
                if (is_array($decoded)) {
                    $extracted = array_filter(array_column($decoded, 'value'));
                    if (!empty($extracted)) {
                        $cleanTags = implode(', ', $extracted);
                    }
                }
            }

            // Create the link with encrypted data
            $link = Links::create([
                'user_id' => Auth::id(),
                'category_id' => $categoryId,
                'title' => Encryptor::encrypt($validated['title']),
                'url' => Encryptor::encrypt($validated['url']),
                'description' => Encryptor::encrypt($validated['description'] ?? null),
                'thumbnail' => $validated['thumbnail'] ?? null,
                'tags' => Encryptor::encrypt($cleanTags),
                'is_new' => $request->has('isNew'),
                'is_hidden' => $request->has('isHidden'),
            ]);

            return redirect()->route('panel.linklist')->with('success', 'Link added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to add link: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $linkId = decrypt($id);
            $link = Links::where('id', $linkId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Decrypt sensitive fields
            $link->title = Encryptor::decrypt($link->title);
            $link->url = Encryptor::decrypt($link->url);
            $link->description = Encryptor::decrypt($link->description);
            $link->tags = Encryptor::decrypt($link->tags);

            $categories = Category::where('user_id', Auth::id())
                ->visible()
                ->orderBy('created_at', 'desc')
                ->get();

            return view('panel.addlink', compact('link', 'categories'));
        } catch (\Exception $e) {
            return redirect()->route('panel.linklist')->withErrors(['error' => 'Link not found']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $linkId = decrypt($id);
            $link = Links::where('id', $linkId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $validated = $request->validate([
                'url' => 'required|url|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'nullable|string',
                'thumbnail' => 'nullable|url|max:255',
                'thumbnail_file' => 'nullable|image|max:5120',
                'categories' => 'nullable|string',
                'isNew' => 'nullable|boolean',
                'isHidden' => 'nullable|boolean',
            ]);

            // Decrypt category_id if provided
            $categoryId = null;
            if (!empty($validated['category_id'])) {
                try {
                    $categoryId = decrypt($validated['category_id']);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['category_id' => 'Invalid category selected'])->withInput();
                }
            }

            // 1. Direct image file upload
            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = time() . '-' . \Illuminate\Support\Str::random(12) . '.' . $ext;
                $targetDir = public_path('thumbnails/links');
                if (!\Illuminate\Support\Facades\File::exists($targetDir)) {
                    \Illuminate\Support\Facades\File::makeDirectory($targetDir, 0755, true);
                }
                $file->move($targetDir, $filename);
                if ($link->thumbnail) {
                    \App\Helpers\ThumbnailHelper::deleteLocalThumbnail($link->thumbnail);
                }
                $validated['thumbnail'] = 'thumbnails/links/' . $filename;
            }
            // 2. Automated screenshot
            elseif (empty($validated['thumbnail']) && ($request->get('makeThumbnailWithBrowershot') == '1' || $request->boolean('makeThumbnailWithBrowershot'))) {
                $webSiteController = new WebsiteController();
                $screenshotResponse = $webSiteController->getWebScreenshot(base64_encode($request->get('url')));
                if ($screenshotResponse->getData()->ss_path != null){
                    $validated['thumbnail'] = $screenshotResponse->getData()->ss_path;
                } else {
                    \Illuminate\Support\Facades\Log::warning('Thumbnail generation failed for URL ' . $request->get('url') . ' with Message: ' . ($screenshotResponse->getData()->message ?? 'Unknown'));
                }
            }
            // 3. Remote URL download
            elseif (!empty($validated['thumbnail']) && $validated['thumbnail'] !== $link->thumbnail) {
                $validated['thumbnail'] = \App\Helpers\ThumbnailHelper::downloadAndSave($validated['thumbnail'], 'thumbnails/links', $link->thumbnail);
            }

            // Normalize Tagify tags output
            $cleanTags = $validated['categories'] ?? null;
            if (!empty($cleanTags)) {
                $decoded = json_decode($cleanTags, true);
                if (is_array($decoded)) {
                    $extracted = array_filter(array_column($decoded, 'value'));
                    if (!empty($extracted)) {
                        $cleanTags = implode(', ', $extracted);
                    }
                }
            }


            // Update the link with encrypted data
            $link->update([
                'category_id' => $categoryId,
                'title' => Encryptor::encrypt($validated['title']),
                'url' => Encryptor::encrypt($validated['url']),
                'description' => Encryptor::encrypt($validated['description'] ?? null),
                'thumbnail' => $validated['thumbnail'] ?? null,
                'tags' => Encryptor::encrypt($cleanTags),
                'is_new' => $request->has('isNew'),
                'is_hidden' => $request->has('isHidden'),
            ]);

            return redirect()->route('panel.linklist')->with('success', 'Link updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update link: ' . $e->getMessage()])->withInput();
        }
    }

    public function toggleStar(Request $request)
    {
        try {
            $linkId = decrypt($request->link_id);
            $link = Links::where('id', $linkId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $link->is_starred = !$link->is_starred;
            $link->save();

            return response()->json([
                'success' => true,
                'is_starred' => $link->is_starred,
                'message' => $link->is_starred ? 'Link starred' : 'Link unstarred'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle star'
            ], 400);
        }
    }

    public function toggleHide(Request $request)
    {
        try {
            $linkId = decrypt($request->link_id);
            $link = Links::where('id', $linkId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $link->is_hidden = !$link->is_hidden;
            $link->save();

            return response()->json([
                'success' => true,
                'is_hidden' => $link->is_hidden,
                'message' => $link->is_hidden ? 'Link hidden' : 'Link unhidden'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle visibility'
            ], 400);
        }
    }

    public function delete($id)
    {
        try {
            $linkId = decrypt($id);
            $link = Links::where('id', $linkId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $link->delete();

            return redirect()->route('panel.linklist')->with('success', 'Link deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('panel.linklist')->withErrors(['error' => 'Failed to delete link']);
        }
    }

    // Hidden Links Methods
    public function hiddenLinksLogin()
    {
        $user = Auth::user();
        if ($user) {
            $sessionTimeout = $user->getVaultSessionLifetime();
            $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
            $lastActivity = session('vault_group_last_activity') ?: session('hidden_links_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_passwords_last_activity');
            if ($isAuth && $lastActivity && (now()->timestamp - $lastActivity) <= $sessionTimeout) {
                return redirect()->route('panel.hiddenLinks');
            }
        }

        return view('panel.hidden-links-login');
    }

    public function hiddenLinksAuth(Request $request)
    {
        $request->validate([
            'vault_pass' => 'nullable|string',
            'code' => 'nullable|string',
        ]);

        $user = Auth::user();
        $input = trim((string)($request->input('code') ?: $request->input('vault_pass')));

        if (empty($input)) {
            return redirect()->back()->withErrors(['vault_pass' => 'Please enter your verification code or vault passcode.']);
        }

        $isValid = false;

        // 1. Check TOTP / 2FA / Recovery Code
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

        // 3. Fallback: check account password
        if (!$isValid && empty($user->vault_pass)) {
            if (\Illuminate\Support\Facades\Hash::check($input, $user->password)) {
                $isValid = true;
            }
        }

        if ($isValid) {
            \App\Services\AuditLogger::vault('links.unlock_success', "Unlocked Hidden Links vault successfully.", 'success', [], $user);

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
            return redirect()->route('panel.hiddenLinks');
        }

        \App\Services\AuditLogger::vault('links.unlock_failed', "Failed attempt to unlock Hidden Links vault.", 'warning', [], $user);

        sleep(1);

        return redirect()->back()->withErrors(['vault_pass' => 'Invalid verification code or vault password. Please try again.']);
    }

    public function hiddenLinks(Request $request)
    {
        $user = Auth::user();
        $sessionTimeout = $user ? $user->getHiddenLinksSessionLifetime() : 1800;

        // Check if session is authenticated across vault group
        $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
        if (!$isAuth) {
            return redirect()->route('panel.hiddenLinksLogin');
        }

        // Check if session has expired according to user's configured lifetime
        $lastActivity = session('vault_group_last_activity') ?: session('hidden_links_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_passwords_last_activity');
        if (!$lastActivity || (now()->timestamp - $lastActivity) > $sessionTimeout) {
            session()->forget([
                'vault_group_authenticated', 'vault_group_last_activity',
                'hidden_files_authenticated', 'hidden_files_last_activity',
                'hidden_links_authenticated', 'hidden_links_last_activity',
                'hidden_passwords_authenticated', 'hidden_passwords_last_activity'
            ]);
            return redirect()->route('panel.hiddenLinksLogin')
                ->with('info', 'Vault session expired due to inactivity. Please login again.');
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

        $query = Links::where('user_id', Auth::id())
            ->where('is_hidden', true)
            ->with('category');

        // Filter by category
        if ($request->filled('category')) {
            try {
                $categoryId = decrypt($request->category);
                $query->where('category_id', $categoryId);
            } catch (\Exception $e) {}
        }

        // Filter by starred
        if ($request->filled('starred') && $request->starred == '1') {
            $query->where('is_starred', true);
        }

        $allLinks = $query->orderBy('created_at', 'desc')->get();

        // Decrypt fields
        $decryptedAll = $allLinks->map(function ($link) {
            $link->title = Encryptor::decrypt($link->title);
            $link->url = Encryptor::decrypt($link->url);
            $link->description = Encryptor::decrypt($link->description);
            $link->tags = Encryptor::decrypt($link->tags);
            return $link;
        });

        // Extract unique tags that are actually used across hidden links (case-insensitive)
        $allUserLinksForTags = Links::where('user_id', Auth::id())
            ->where('is_hidden', true)
            ->whereNotNull('tags')
            ->select('tags')
            ->get();

        $tags = [];
        foreach ($allUserLinksForTags as $l) {
            $decryptedTags = Encryptor::decrypt($l->tags);
            if (!empty($decryptedTags)) {
                $raw = explode(',', $decryptedTags);
                foreach ($raw as $t) {
                    $clean = strtolower(trim($t));
                    if ($clean !== '' && !in_array($clean, $tags)) {
                        $tags[] = $clean;
                    }
                }
            }
        }
        sort($tags);

        // Apply Tag & Search filters
        $tagFilter = $request->filled('tag') ? strtolower(trim($request->tag)) : null;
        $searchTerm = $request->filled('search') ? strtolower(trim($request->search)) : null;

        $filteredLinks = $decryptedAll->filter(function ($link) use ($searchTerm, $tagFilter) {
            if ($tagFilter) {
                if (empty($link->tags)) return false;
                $linkTags = array_map('trim', explode(',', strtolower($link->tags)));
                if (!in_array($tagFilter, $linkTags)) return false;
            }

            if ($searchTerm) {
                return str_contains(strtolower($link->title ?? ''), $searchTerm) ||
                       str_contains(strtolower($link->url ?? ''), $searchTerm) ||
                       str_contains(strtolower($link->description ?? ''), $searchTerm) ||
                       str_contains(strtolower($link->tags ?? ''), $searchTerm);
            }

            return true;
        });

        // Paginate manually using persistent items_per_page setting
        $perPage = \App\Helpers\SettingHelper::getItemsPerPage(12);
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $links = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredLinks->slice($offset, $perPage)->values(),
            $filteredLinks->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get ONLY categories that are actually used by hidden links
        $usedCategoryIds = Links::where('user_id', Auth::id())
            ->where('is_hidden', true)
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->unique();

        $categories = Category::whereIn('id', $usedCategoryIds)
            ->where('user_id', Auth::id())
            ->orderBy('title', 'asc')
            ->get();

        // Calculate remaining time for the view
        $remainingTime = max(0, $sessionTimeout - (now()->timestamp - $now));

        // If it's an AJAX request, return only the cards
        if ($request->ajax()) {
            return view('panel.ajax.hidden_links_card_load', compact('links'))->render();
        }

        return view('panel.hidden-links', compact('links', 'categories', 'tags', 'remainingTime'));
    }

    public function logoutHiddenLinks()
    {
        session()->forget([
            'vault_group_authenticated', 'vault_group_last_activity',
            'hidden_files_authenticated', 'hidden_files_last_activity',
            'hidden_links_authenticated', 'hidden_links_last_activity',
            'hidden_passwords_authenticated', 'hidden_passwords_last_activity',
            'password_reveal_authenticated'
        ]);
        session()->save();
        return redirect()->route('panel.hiddenLinksLogin')
            ->with('success', 'Vault session closed and locked.');
    }

    public function unhideLink(Request $request)
    {
        if (!session('hidden_links_authenticated')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Check if session has expired
        $lastActivity = session('hidden_links_last_activity');
        $sessionTimeout = 1800; // 30 minutes

        if ($lastActivity && (now()->timestamp - $lastActivity) > $sessionTimeout) {
            session()->forget(['hidden_links_authenticated', 'hidden_links_last_activity']);
            return response()->json([
                'success' => false,
                'message' => 'Session expired',
                'expired' => true
            ], 401);
        }

        // Update last activity
        session(['hidden_links_last_activity' => now()->timestamp]);

        try {
            $linkId = decrypt($request->link_id);
            $link = Links::where('id', $linkId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $link->is_hidden = false;
            $link->save();

            return response()->json([
                'success' => true,
                'message' => 'Link unhidden successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unhide link'
            ], 400);
        }
    }

    public function extendSession()
    {
        if (!session('hidden_links_authenticated')) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $user = Auth::user();
        $sessionTimeout = $user ? $user->getHiddenLinksSessionLifetime() : 1800;

        // Update last activity timestamp
        session(['hidden_links_last_activity' => now()->timestamp]);

        return response()->json([
            'success' => true,
            'ok' => 1,
            'code' => 200,
            'remaining_time' => $sessionTimeout,
            'message' => 'Session extended'
        ]);
    }

    // Trash Operations
    public function restore($id)
    {
        try {
            $linkId = decrypt($id);
            $link = Links::where('id', $linkId)
                ->where('user_id', Auth::id())
                ->onlyTrashed()
                ->firstOrFail();

            $link->restore();

            return response()->json([
                'ok' => 1,
                'code' => 200,
                'info' => 'Link restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'code' => 400,
                'info' => 'Failed to restore link: ' . $e->getMessage()
            ], 400);
        }
    }

    public function permanentDelete($id)
    {
        try {
            $linkId = decrypt($id);
            $link = Links::where('id', $linkId)
                ->where('user_id', Auth::id())
                ->onlyTrashed()
                ->firstOrFail();

            $link->forceDelete();

            return response()->json([
                'ok' => 1,
                'code' => 200,
                'info' => 'Link permanently deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'code' => 400,
                'info' => 'Failed to delete link: ' . $e->getMessage()
            ], 400);
        }
    }

    public function emptyTrash()
    {
        try {
            $trashedLinks = Links::where('user_id', Auth::id())
                ->onlyTrashed()
                ->get();

            $count = $trashedLinks->count();

            foreach ($trashedLinks as $link) {
                $link->forceDelete();
            }

            return response()->json([
                'ok' => 1,
                'code' => 200,
                'deleted_count' => $count,
                'info' => "All {$count} links permanently deleted from trash"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'code' => 400,
                'info' => 'Failed to empty trash: ' . $e->getMessage()
            ], 400);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:delete,hide,unhide,star,unstar',
            'ids' => 'required|array',
            'ids.*' => 'required'
        ]);

        $decryptedIds = [];
        foreach ($request->ids as $encryptedId) {
            try {
                $decryptedIds[] = decrypt($encryptedId);
            } catch (\Exception $e) {
                try {
                    $decryptedIds[] = \Illuminate\Support\Facades\Crypt::decrypt($encryptedId);
                } catch (\Exception $ex) {
                    if (is_numeric($encryptedId)) {
                        $decryptedIds[] = $encryptedId;
                    }
                }
            }
        }

        if (empty($decryptedIds)) {
            return response()->json(['success' => false, 'message' => 'No valid items selected.'], 400);
        }

        $query = Links::where('user_id', Auth::id())->whereIn('id', $decryptedIds);

        if ($request->action === 'delete') {
            $query->delete();
            $msg = 'Selected links deleted successfully.';
        } elseif ($request->action === 'hide') {
            $query->update(['is_hidden' => true]);
            $msg = 'Selected links hidden successfully.';
        } elseif ($request->action === 'unhide') {
            $query->update(['is_hidden' => false]);
            $msg = 'Selected links unhidden successfully.';
        } elseif ($request->action === 'star') {
            $query->update(['is_starred' => true]);
            $msg = 'Selected links starred successfully.';
        } elseif ($request->action === 'unstar') {
            $query->update(['is_starred' => false]);
            $msg = 'Selected links unstarred successfully.';
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function recaptureScreenshot(Request $request)
    {
        $request->validate([
            'linkId' => 'required'
        ]);

        try {
            $id = decrypt($request->linkId);
        } catch (\Exception $e) {
            try {
                $id = \Illuminate\Support\Facades\Crypt::decrypt($request->linkId);
            } catch (\Exception $ex) {
                $id = $request->linkId;
            }
        }

        $link = Links::where('user_id', Auth::id())->findOrFail($id);
        $decryptedUrl = Encryptor::decrypt($link->url);

        if (!$decryptedUrl) {
            return response()->json(['code' => 400, 'info' => 'Unable to decrypt link URL'], 400);
        }

        $webSiteController = new WebsiteController();
        $screenshotResponse = $webSiteController->getWebScreenshot(base64_encode($decryptedUrl));
        $data = $screenshotResponse->getData();

        if (!empty($data->ss_path)) {
            // Delete old screenshot file if it was a generated Browsershot image
            if ($link->thumbnail && $link->thumbnail !== $data->ss_path && str_starts_with($link->thumbnail, 'Browsershot/')) {
                if (file_exists(public_path($link->thumbnail))) {
                    @unlink(public_path($link->thumbnail));
                }
            }

            $link->thumbnail = $data->ss_path;
            $link->save();

            return response()->json([
                'code' => 200,
                'info' => 'Screenshot updated successfully!',
                'thumbnail' => $data->ss_path,
                'thumbnail_url' => asset($data->ss_path)
            ]);
        }

        return response()->json([
            'code' => 400,
            'info' => $data->message ?? 'Unable to capture screenshot for this URL'
        ], 400);
    }

    /**
     * Bulk import mapped links from Excel / CSV
     */
    public function importBatch(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.url' => 'required|string|max:2048',
            'items.*.description' => 'nullable|string',
            'items.*.tags' => 'nullable|string',
            'items.*.category' => 'nullable|string',
        ]);

        $items = $request->input('items', []);
        $importedCount = 0;
        $userId = Auth::id();

        // Cache user's existing categories for fast lookup/creation
        $existingCategories = Category::where('user_id', $userId)->get();
        $categoriesMap = [];
        foreach ($existingCategories as $cat) {
            $categoriesMap[trim($cat->title)] = $cat->id;
        }

        foreach ($items as $item) {
            $title = trim($item['title'] ?? '');
            $url = trim($item['url'] ?? '');
            if (empty($title) || empty($url)) continue;

            // Ensure URL has protocol
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }

            $categoryId = null;
            $catName = trim($item['category'] ?? '');
            if (!empty($catName)) {
                if (isset($categoriesMap[$catName])) {
                    $categoryId = $categoriesMap[$catName];
                } else {
                    $newCat = Category::create([
                        'user_id' => $userId,
                        'title' => $catName,
                        'description' => 'Imported category',
                        'type' => 'links',
                        'is_new' => false,
                        'is_hidden' => false,
                    ]);
                    $categoryId = $newCat->id;
                    $categoriesMap[$catName] = $categoryId;
                }
            }

            Links::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => Encryptor::encrypt($title),
                'url' => Encryptor::encrypt($url),
                'description' => Encryptor::encrypt(trim($item['description'] ?? '') ?: null),
                'tags' => Encryptor::encrypt(trim($item['tags'] ?? '') ?: null),
                'is_new' => false,
                'is_hidden' => false,
            ]);

            $importedCount++;
        }

        return response()->json([
            'success' => true,
            'imported_count' => $importedCount,
            'message' => "Successfully imported {$importedCount} links!"
        ]);
    }

    /**
     * Export links with selected custom fields
     */
    public function exportData(Request $request)
    {
        $request->validate([
            'fields' => 'required|array|min:1',
            'format' => 'required|in:csv,json,xlsx'
        ]);

        $selectedFields = $request->input('fields', []);
        $format = $request->input('format', 'csv');

        $links = Links::with('category')
            ->where('user_id', Auth::id())
            ->where('is_trashed', false)
            ->get();

        $rows = [];
        foreach ($links as $link) {
            $row = [];
            if (in_array('title', $selectedFields)) {
                $row['Title'] = Encryptor::decrypt($link->title) ?: '';
            }
            if (in_array('url', $selectedFields)) {
                $row['URL'] = Encryptor::decrypt($link->url) ?: '';
            }
            if (in_array('category', $selectedFields)) {
                $row['Category'] = $link->category ? (Encryptor::decrypt($link->category->title) ?: '') : 'Uncategorized';
            }
            if (in_array('description', $selectedFields)) {
                $row['Description'] = Encryptor::decrypt($link->description) ?: '';
            }
            if (in_array('tags', $selectedFields)) {
                $row['Tags'] = Encryptor::decrypt($link->tags) ?: '';
            }
            if (in_array('created_at', $selectedFields)) {
                $row['Created Date'] = $link->created_at ? $link->created_at->format('Y-m-d H:i:s') : '';
            }
            $rows[] = $row;
        }

        if ($format === 'json' || $format === 'xlsx') {
            return response()->json([
                'success' => true,
                'headers' => array_keys($rows[0] ?? []),
                'rows' => $rows,
                'filename' => 'Links_Export_' . date('Y-m-d_His')
            ]);
        }

        // CSV Direct Stream
        $headers = array_keys($rows[0] ?? ['Title', 'URL']);
        $callback = function() use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM for Excel
            fputcsv($file, $headers);
            foreach ($rows as $r) {
                fputcsv($file, array_values($r));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Links_Export_' . date('Y-m-d_His') . '.csv"',
        ]);
    }
}
