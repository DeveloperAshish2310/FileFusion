<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ThumbnailHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Links;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ApiLinkController extends Controller
{
    /**
     * List user's saved links/bookmarks.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Links::where('user_id', $user->id)
            ->where('is_trashed', 0)
            ->with('category');

        if ($request->boolean('only_hidden')) {
            $query->where('is_hidden', 1);
        } elseif (!$request->boolean('include_hidden')) {
            $query->where('is_hidden', 0);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->boolean('starred_only')) {
            $query->where('is_starred', 1);
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $links = $query->orderBy('id', 'desc')->paginate($perPage);

        $search = strtolower(trim($request->query('search', $request->query('q', ''))));
        $tagFilter = strtolower(trim($request->query('tag', '')));

        $items = collect($links->items())->map(function ($link) {
            return [
                'id' => encrypt($link->id),
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'tags' => array_filter(array_map('trim', explode(',', $link->tags ?? ''))),
                'category' => $link->category ? [
                    'id' => encrypt($link->category->id),
                    'name' => $link->category->name,
                    'color' => $link->category->color,
                ] : null,
                'thumbnail_url' => $link->thumbnail ? (preg_match('~^https?://~i', $link->thumbnail) ? $link->thumbnail : asset($link->thumbnail)) : null,
                'is_starred' => (bool) $link->is_starred,
                'is_hidden' => (bool) $link->is_hidden,
                'is_new' => (bool) $link->is_new,
                'created_at' => $link->created_at?->toIso8601String(),
                'updated_at' => $link->updated_at?->toIso8601String(),
            ];
        });

        if (!empty($search)) {
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['title'] ?? ''), $search)
                    || str_contains(strtolower($item['url'] ?? ''), $search)
                    || str_contains(strtolower($item['description'] ?? ''), $search);
            })->values();
        }

        if (!empty($tagFilter)) {
            $items = $items->filter(function ($item) use ($tagFilter) {
                return in_array($tagFilter, array_map('strtolower', $item['tags']), true);
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $links->currentPage(),
                'last_page' => $links->lastPage(),
                'per_page' => $links->perPage(),
                'total' => $links->total(),
            ],
        ]);
    }

    /**
     * Create a new bookmark.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'url' => 'required|url|max:2000',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
            'category_id' => 'nullable',
            'thumbnail' => 'nullable|string|max:1000',
            'is_hidden' => 'nullable|boolean',
            'is_starred' => 'nullable|boolean',
        ]);

        $url = $validated['url'];
        $title = $validated['title'] ?? null;
        $description = $validated['description'] ?? null;
        $thumbnail = $validated['thumbnail'] ?? null;
        $categoryId = !empty($validated['category_id']) ? $this->resolveId($validated['category_id']) : null;

        // Auto-scrape page metadata if title is empty
        if (empty($title)) {
            $scraped = $this->scrapeMetadata($url);
            $title = $scraped['title'] ?: parse_url($url, PHP_URL_HOST);
            if (empty($description) && !empty($scraped['description'])) {
                $description = $scraped['description'];
            }
            if (empty($thumbnail) && !empty($scraped['image'])) {
                $thumbnail = $scraped['image'];
            }
        }

        // Cache remote thumbnail if provided
        if (!empty($thumbnail) && preg_match('~^https?://~i', $thumbnail)) {
            $thumbnail = ThumbnailHelper::downloadAndSave($thumbnail, 'thumbnails/links');
        }

        $link = Links::create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'title' => $title,
            'url' => $url,
            'description' => $description,
            'tags' => $validated['tags'] ?? null,
            'thumbnail' => $thumbnail,
            'is_hidden' => $request->boolean('is_hidden', false),
            'is_starred' => $request->boolean('is_starred', false),
            'is_trashed' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bookmark created successfully.',
            'link' => [
                'id' => encrypt($link->id),
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'tags' => array_filter(array_map('trim', explode(',', $link->tags ?? ''))),
                'thumbnail_url' => $link->thumbnail ? (preg_match('~^https?://~i', $link->thumbnail) ? $link->thumbnail : asset($link->thumbnail)) : null,
                'is_hidden' => (bool) $link->is_hidden,
                'is_starred' => (bool) $link->is_starred,
                'created_at' => $link->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get single bookmark.
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $link = Links::where('user_id', $user->id)->with('category')->findOrFail($realId);

        return response()->json([
            'success' => true,
            'link' => [
                'id' => encrypt($link->id),
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'tags' => array_filter(array_map('trim', explode(',', $link->tags ?? ''))),
                'category' => $link->category ? [
                    'id' => encrypt($link->category->id),
                    'name' => $link->category->name,
                    'color' => $link->category->color,
                ] : null,
                'thumbnail_url' => $link->thumbnail ? (preg_match('~^https?://~i', $link->thumbnail) ? $link->thumbnail : asset($link->thumbnail)) : null,
                'is_starred' => (bool) $link->is_starred,
                'is_hidden' => (bool) $link->is_hidden,
                'created_at' => $link->created_at?->toIso8601String(),
                'updated_at' => $link->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update bookmark.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $link = Links::where('user_id', $user->id)->findOrFail($realId);

        $validated = $request->validate([
            'url' => 'nullable|url|max:2000',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
            'category_id' => 'nullable',
            'thumbnail' => 'nullable|string|max:1000',
            'is_hidden' => 'nullable|boolean',
            'is_starred' => 'nullable|boolean',
        ]);

        if (array_key_exists('category_id', $validated)) {
            $validated['category_id'] = !empty($validated['category_id']) ? $this->resolveId($validated['category_id']) : null;
        }

        if (array_key_exists('thumbnail', $validated) && !empty($validated['thumbnail']) && $validated['thumbnail'] !== $link->thumbnail) {
            $validated['thumbnail'] = ThumbnailHelper::downloadAndSave($validated['thumbnail'], 'thumbnails/links', $link->thumbnail);
        }

        $link->update(array_filter($validated, fn ($val) => $val !== null));

        return response()->json([
            'success' => true,
            'message' => 'Bookmark updated successfully.',
            'link' => [
                'id' => encrypt($link->id),
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'tags' => array_filter(array_map('trim', explode(',', $link->tags ?? ''))),
                'thumbnail_url' => $link->thumbnail ? (preg_match('~^https?://~i', $link->thumbnail) ? $link->thumbnail : asset($link->thumbnail)) : null,
                'is_starred' => (bool) $link->is_starred,
                'is_hidden' => (bool) $link->is_hidden,
                'updated_at' => $link->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Delete bookmark.
     */
    public function destroy($id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $realId = $this->resolveId($id);
        $link = Links::where('user_id', $user->id)->findOrFail($realId);

        if ($request->boolean('force', false)) {
            if ($link->thumbnail) {
                ThumbnailHelper::deleteLocalThumbnail($link->thumbnail);
            }
            $link->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Bookmark permanently deleted.',
            ]);
        }

        $link->update(['is_trashed' => 1]);
        $link->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark moved to trash.',
        ]);
    }

    /**
     * Resolve ID if passed as numeric or encrypted string.
     */
    protected function resolveId($id): int
    {
        if (is_numeric($id)) {
            return (int) $id;
        }

        try {
            return (int) decrypt($id);
        } catch (\Throwable $e) {
            try {
                return (int) \Illuminate\Support\Facades\Crypt::decrypt($id);
            } catch (\Throwable $ex) {
                try {
                    return (int) \App\Helpers\Encryptor::decrypt($id);
                } catch (\Throwable $ex2) {
                    abort(404, 'Invalid link identifier.');
                }
            }
        }
    }

    protected function scrapeMetadata(string $url): array
    {
        try {
            $response = Http::timeout(8)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
            ])->get($url);

            if (!$response->successful()) {
                return ['title' => null, 'description' => null, 'image' => null];
            }

            $html = $response->body();
            $title = null;
            $description = null;
            $image = null;

            if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
                $title = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5);
            }
            if (preg_match('/<meta[^>]*property=["\']og:description["\'][^>]*content=["\'](.*?)["\']/is', $html, $matches) ||
                preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/is', $html, $matches)) {
                $description = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5);
            }
            if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\'](.*?)["\']/is', $html, $matches)) {
                $image = trim($matches[1]);
            }

            return ['title' => $title, 'description' => $description, 'image' => $image];
        } catch (\Exception $e) {
            return ['title' => null, 'description' => null, 'image' => null];
        }
    }
}
