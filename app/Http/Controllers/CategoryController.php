<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryController extends Controller
{
    public function isVaultUnlocked()
    {
        $user = Auth::user();
        if (!$user) return false;

        $isAuth = session('vault_group_authenticated') || session('hidden_files_authenticated') || session('hidden_links_authenticated') || session('hidden_passwords_authenticated');
        $lastActivity = session('vault_group_last_activity') ?: session('hidden_links_last_activity') ?: session('hidden_files_last_activity') ?: session('hidden_passwords_last_activity');

        if (!$isAuth || !$lastActivity) return false;

        $sessionTimeout = method_exists($user, 'getVaultSessionLifetime') ? $user->getVaultSessionLifetime() : 1800;
        return (now()->timestamp - $lastActivity) <= $sessionTimeout;
    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'both');
        $isVaultAuth = $this->isVaultUnlocked();

        if ($type === 'hidden' && !$isVaultAuth) {
            return view('panel.hidden-categories-login');
        }

        $query = Category::where('user_id', Auth::id());

        if ($type === 'hidden') {
            $query->where('is_hidden', true);
        } else {
            if ($type !== 'both') {
                if ($type === 'tasks' || $type === 'todos') {
                    $query->whereIn('type', ['tasks', 'todos']);
                } else {
                    $query->where('type', $type);
                }
            }
            $query->visible();
        }

        $categories = $query->orderBy('created_at', 'desc')
            ->paginate(\App\Helpers\SettingHelper::getItemsPerPage(12));

        if ($request->ajax()) {
            return response()->json([
                'ok' => 1,
                'categories' => $categories->items(),
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'total' => $categories->total()
                ]
            ]);
        }

        return view('panel.categories', compact('categories', 'type', 'isVaultAuth'));
    }

    public function hiddenCategoriesAuth(Request $request)
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

        if (method_exists($user, 'hasTwoFactorEnabled') && $user->hasTwoFactorEnabled()) {
            $totpCheck = \App\Services\TwoFactorService::verifyAny($user, $input);
            if ($totpCheck['success']) {
                $isValid = true;
            }
        }

        if (!$isValid && !empty($user->vault_pass)) {
            if (password_verify($input, $user->vault_pass)) {
                $isValid = true;
            }
        }

        if (!$isValid && empty($user->vault_pass)) {
            if (\Illuminate\Support\Facades\Hash::check($input, $user->password)) {
                $isValid = true;
            }
        }

        if ($isValid) {
            session([
                'vault_group_authenticated' => true,
                'vault_group_last_activity' => now()->timestamp,
                'hidden_files_authenticated' => true,
                'hidden_files_last_activity' => now()->timestamp,
                'hidden_links_authenticated' => true,
                'hidden_links_last_activity' => now()->timestamp,
                'hidden_passwords_authenticated' => true,
                'hidden_passwords_last_activity' => now()->timestamp,
                'password_reveal_authenticated' => time(),
            ]);

            // Broadcast live security alert to all active user devices (Phone, PC, etc.)
            try {
                $ua = $request->header('User-Agent', '');
                $origin = str_contains($ua, 'Android') ? 'Android Device' : (str_contains($ua, 'Windows') ? 'Windows PC' : (str_contains($ua, 'iPhone') || str_contains($ua, 'Mac') ? 'Apple Device' : 'Web Session'));
                \App\Services\PushNotificationService::sendToUser($user->id, [
                    'title' => '🛡️ Vault Security Alert',
                    'body' => "Secret Categories Vault unlocked on {$origin}. Session active.",
                    'url' => route('panel.categories.index', ['type' => 'hidden']),
                    'tag' => 'filefusion_security',
                    'channelId' => 'filefusion_security',
                ]);
            } catch (\Throwable $pushErr) {
                \Illuminate\Support\Facades\Log::warning('[Category Vault Unlock Push]: ' . $pushErr->getMessage());
            }

            return redirect()->route('panel.categories.index', ['type' => 'hidden'])
                ->with('success', 'Hidden categories unlocked.');
        }

        return redirect()->back()->withErrors(['vault_pass' => 'Incorrect passcode or verification code.']);
    }

    public function secretCategoriesApi()
    {
        if (!$this->isVaultUnlocked()) {
            return response()->json([
                'ok' => 0,
                'message' => 'Vault is locked.'
            ], 403);
        }

        $secretCategories = Category::where('user_id', Auth::id())
            ->where('is_hidden', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($cat) {
            return [
                'id' => $cat->id,
                'title' => $cat->title,
                'description' => $cat->description,
                'type' => $cat->type,
                'thumbnail' => $cat->thumbnail,
                'created_at' => $cat->created_at ? $cat->created_at->format('M d, Y') : '',
            ];
        });

        return response()->json([
            'ok' => 1,
            'categories' => $secretCategories
        ]);
    }

    public function create()
    {
        return view('panel.addlinkcategory');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:links,files,both,tasks,todos',
            'thumbnail' => 'nullable|url',
            'thumbnail_url' => 'nullable|url',
            'thumbnailfile' => 'nullable|image|max:5120', // 5MB max
            'thumbnail_file' => 'nullable|image|max:5120',
            'categories' => 'nullable|string',
            'is_new' => 'nullable|boolean',
            'isNew' => 'nullable|boolean',
            'is_hidden' => 'nullable|boolean',
            'isHidden' => 'nullable|boolean'
        ]);

        $thumbnailPath = null;
        $file = $request->file('thumbnail_file') ?: $request->file('thumbnailfile');
        $urlThumb = $request->input('thumbnail_url') ?: $request->input('thumbnail');

        // Handle file upload
        if ($file && $file->isValid()) {
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $thumbnailPath = $file->storeAs('categories/thumbnails', $fileName, 'public');
            $thumbnailPath = Storage::url($thumbnailPath);
        }
        // Use URL thumbnail if no file uploaded
        elseif (!empty($urlThumb)) {
            $thumbnailPath = \App\Helpers\ThumbnailHelper::downloadAndSave($urlThumb, 'thumbnails/categories');
        }

        $isNew = $request->boolean('is_new') || $request->boolean('isNew');
        $isHidden = $request->boolean('is_hidden') || $request->boolean('isHidden');

        $tagsArray = null;
        if (!empty($validated['categories'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['categories']))));
        }

        $category = Category::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'thumbnail' => $thumbnailPath,
            'categories' => $tagsArray,
            'is_new' => $isNew,
            'is_hidden' => $isHidden,
            'user_id' => Auth::id()
        ]);

        // If category is Tasks, synchronize with TodoCollection
        if (in_array($validated['type'], ['tasks', 'todos'])) {
            \App\Models\TodoCollection::firstOrCreate(
                ['user_id' => Auth::id(), 'name' => $validated['title']],
                [
                    'color' => '#6366f1',
                    'icon' => 'list-todo',
                    'cover_image' => $thumbnailPath,
                    'is_hidden' => $isHidden,
                    'sort_order' => \App\Models\TodoCollection::where('user_id', Auth::id())->count(),
                ]
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Category created successfully',
                'category' => $category
            ]);
        }

        return redirect()->route('panel.categories.index', $isHidden ? ['type' => 'hidden'] : ($validated['type'] === 'tasks' ? ['type' => 'tasks'] : []))
            ->with('success', 'Category created successfully!');
    }

    public function show(Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('panel.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('panel.addlinkcategory', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:links,files,both,tasks,todos',
            'thumbnail' => 'nullable|url',
            'thumbnail_url' => 'nullable|url',
            'thumbnailfile' => 'nullable|image|max:5120',
            'thumbnail_file' => 'nullable|image|max:5120',
            'categories' => 'nullable|string',
            'is_new' => 'nullable|boolean',
            'isNew' => 'nullable|boolean',
            'is_hidden' => 'nullable|boolean',
            'isHidden' => 'nullable|boolean'
        ]);

        $thumbnailPath = $category->thumbnail;
        $file = $request->file('thumbnail_file') ?: $request->file('thumbnailfile');
        $urlThumb = $request->input('thumbnail_url') ?: $request->input('thumbnail');

        // Handle file upload
        if ($file && $file->isValid()) {
            // Delete old thumbnail if it was a file upload
            if ($category->thumbnail && Str::startsWith($category->thumbnail, '/storage/')) {
                Storage::disk('public')->delete(Str::after($category->thumbnail, '/storage/'));
            }

            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $thumbnailPath = $file->storeAs('categories/thumbnails', $fileName, 'public');
            $thumbnailPath = Storage::url($thumbnailPath);
        }
        // Use URL thumbnail if provided
        elseif (!empty($urlThumb)) {
            $thumbnailPath = \App\Helpers\ThumbnailHelper::downloadAndSave($urlThumb, 'thumbnails/categories', $category->thumbnail);
        }

        $isNew = $request->boolean('is_new') || $request->boolean('isNew');
        $isHidden = $request->boolean('is_hidden') || $request->boolean('isHidden');

        $tagsArray = null;
        if (!empty($validated['categories'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['categories']))));
        }

        $oldTitle = $category->title;

        $category->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'thumbnail' => $thumbnailPath,
            'categories' => $tagsArray,
            'is_new' => $isNew,
            'is_hidden' => $isHidden
        ]);

        // If category is Tasks, synchronize with TodoCollection
        if (in_array($validated['type'], ['tasks', 'todos'])) {
            $todoCol = \App\Models\TodoCollection::where('user_id', Auth::id())
                ->where('name', $oldTitle)
                ->first();
            if ($todoCol) {
                $todoCol->update([
                    'name' => $validated['title'],
                    'cover_image' => $thumbnailPath,
                    'is_hidden' => $isHidden,
                ]);
            } else {
                \App\Models\TodoCollection::create([
                    'user_id' => Auth::id(),
                    'name' => $validated['title'],
                    'color' => '#6366f1',
                    'icon' => 'list-todo',
                    'cover_image' => $thumbnailPath,
                    'is_hidden' => $isHidden,
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        }

        return redirect()->route('panel.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Delete thumbnail file if it exists
        if ($category->thumbnail && Str::startsWith($category->thumbnail, '/storage/')) {
            Storage::disk('public')->delete(Str::after($category->thumbnail, '/storage/'));
        }

        $oldTitle = $category->title;
        $type = $category->type;

        $category->delete();

        // If category was tasks/todos, delete matching TodoCollection
        if (in_array($type, ['tasks', 'todos'])) {
            \App\Models\TodoCollection::where('user_id', Auth::id())
                ->where('name', $oldTitle)
                ->delete();
        }

        if (request()->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Category deleted successfully'
            ]);
        }

        return redirect()->route('panel.categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    public function toggle(Request $request, Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $field = $request->get('field');
        if (!in_array($field, ['is_new', 'is_hidden'])) {
            return response()->json(['ok' => 0, 'error' => 'Invalid field']);
        }

        $category->update([
            $field => !$category->{$field}
        ]);

        return response()->json([
            'ok' => 1,
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }
}
