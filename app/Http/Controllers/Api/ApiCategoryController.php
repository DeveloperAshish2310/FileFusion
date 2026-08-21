<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FileModal;
use App\Models\Links;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiCategoryController extends Controller
{
    /**
     * List user's categories.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Category::where('user_id', $user->id);

        if ($request->boolean('only_hidden')) {
            $query->where('is_hidden', true);
        } elseif (!$request->boolean('include_hidden') && !$request->boolean('all')) {
            $query->where('is_hidden', false);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $categories = $query->orderBy('id', 'desc')->paginate($perPage);

        $search = strtolower(trim($request->query('search', $request->query('q', ''))));

        $items = collect($categories->items())->map(function ($cat) {
            $decryptedTitle = $cat->title;
            $decryptedDesc = $cat->description;

            $linkCount = Links::where('user_id', $cat->user_id)
                ->where('category_id', $cat->id)
                ->count();

            return [
                'id' => encrypt($cat->id),
                'title' => $decryptedTitle,
                'description' => $decryptedDesc,
                'type' => $cat->type ?? 'general',
                'thumbnail' => $cat->thumbnail ? (preg_match('~^https?://~i', $cat->thumbnail) ? $cat->thumbnail : asset($cat->thumbnail)) : null,
                'subcategories' => is_array($cat->categories) ? $cat->categories : [],
                'is_hidden' => (bool) $cat->is_hidden,
                'is_new' => (bool) $cat->is_new,
                'links_count' => $linkCount,
                'created_at' => $cat->created_at?->toIso8601String(),
                'updated_at' => $cat->updated_at?->toIso8601String(),
            ];
        });

        if ($search !== '') {
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['title'] ?? ''), $search)
                    || str_contains(strtolower($item['description'] ?? ''), $search)
                    || str_contains(strtolower($item['type'] ?? ''), $search);
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'last_page' => $categories->lastPage(),
            ]
        ]);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|string|max:100',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',
            'is_hidden' => 'nullable|boolean',
            'thumbnail' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $category = new Category();
        $category->user_id = $user->id;
        $category->title = $validated['title'];
        $category->description = $validated['description'] ?? null;
        $category->type = $validated['type'] ?? 'general';
        $category->categories = $validated['categories'] ?? [];
        $category->is_hidden = $request->boolean('is_hidden', false);
        $category->thumbnail = $validated['thumbnail'] ?? null;
        $category->is_new = true;
        $category->save();

        \App\Services\AuditLogger::log('category.created', "Created category '{$validated['title']}' via API.", 'info', [
            'category_id' => $category->id,
            'is_hidden' => $category->is_hidden,
        ], $user);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => [
                'id' => encrypt($category->id),
                'title' => $category->title,
                'description' => $category->description,
                'type' => $category->type,
                'subcategories' => $category->categories ?? [],
                'is_hidden' => (bool) $category->is_hidden,
                'created_at' => $category->created_at?->toIso8601String(),
            ]
        ], 201);
    }

    /**
     * Show single category.
     */
    public function show(string $id): JsonResponse
    {
        $resolvedId = $this->resolveId($id);
        if (!$resolvedId) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $user = Auth::user();
        $cat = Category::where('user_id', $user->id)->find($resolvedId);

        if (!$cat) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => encrypt($cat->id),
                'title' => $cat->title,
                'description' => $cat->description,
                'type' => $cat->type ?? 'general',
                'thumbnail' => $cat->thumbnail ? (preg_match('~^https?://~i', $cat->thumbnail) ? $cat->thumbnail : asset($cat->thumbnail)) : null,
                'subcategories' => is_array($cat->categories) ? $cat->categories : [],
                'is_hidden' => (bool) $cat->is_hidden,
                'created_at' => $cat->created_at?->toIso8601String(),
                'updated_at' => $cat->updated_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Update category.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $resolvedId = $this->resolveId($id);
        if (!$resolvedId) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $user = Auth::user();
        $cat = Category::where('user_id', $user->id)->find($resolvedId);

        if (!$cat) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|string|max:100',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',
            'is_hidden' => 'nullable|boolean',
            'thumbnail' => 'nullable|string|max:255',
        ]);

        if ($request->has('title')) {
            $cat->title = $validated['title'];
        }
        if ($request->has('description')) {
            $cat->description = $validated['description'];
        }
        if ($request->has('type')) {
            $cat->type = $validated['type'];
        }
        if ($request->has('categories')) {
            $cat->categories = $validated['categories'];
        }
        if ($request->has('is_hidden')) {
            $cat->is_hidden = $request->boolean('is_hidden');
        }
        if ($request->has('thumbnail')) {
            $cat->thumbnail = $validated['thumbnail'];
        }

        $cat->save();

        \App\Services\AuditLogger::log('category.updated', "Updated category '{$cat->title}' via API.", 'info', [
            'category_id' => $cat->id,
        ], $user);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => [
                'id' => encrypt($cat->id),
                'title' => $cat->title,
                'description' => $cat->description,
                'type' => $cat->type,
                'subcategories' => $cat->categories ?? [],
                'is_hidden' => (bool) $cat->is_hidden,
                'updated_at' => $cat->updated_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Delete category.
     */
    public function destroy(string $id): JsonResponse
    {
        $resolvedId = $this->resolveId($id);
        if (!$resolvedId) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $user = Auth::user();
        $cat = Category::where('user_id', $user->id)->find($resolvedId);

        if (!$cat) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $title = $cat->title;
        $cat->delete();

        \App\Services\AuditLogger::log('category.deleted', "Deleted category '{$title}' via API.", 'warning', [
            'category_id' => $resolvedId,
        ], $user);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }

    /**
     * Resolve encrypted or plain ID.
     */
    protected function resolveId(string $id): ?int
    {
        if (ctype_digit($id)) {
            return (int) $id;
        }

        try {
            $decrypted = decrypt($id);
            return is_numeric($decrypted) ? (int) $decrypted : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
