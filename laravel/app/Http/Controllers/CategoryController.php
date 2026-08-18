<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'both');

        $categories = Category::where('user_id', Auth::id())
            ->when($type !== 'both', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->visible()
            ->orderBy('created_at', 'desc')
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

        return view('panel.categories', compact('categories', 'type'));
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
            'type' => 'required|in:links,files,both',
            'thumbnail' => 'nullable|url',
            'thumbnailfile' => 'nullable|image|max:5120', // 5MB max
            'categories' => 'nullable|string',
            'isNew' => 'boolean',
            'isHidden' => 'boolean'
        ]);

        $thumbnailPath = null;

        // Handle file upload
        if ($request->hasFile('thumbnailfile')) {
            $file = $request->file('thumbnailfile');
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $thumbnailPath = $file->storeAs('categories/thumbnails', $fileName, 'public');
            $thumbnailPath = Storage::url($thumbnailPath);
        }
        // Use URL thumbnail if no file uploaded
        elseif ($request->filled('thumbnail')) {
            $thumbnailPath = \App\Helpers\ThumbnailHelper::downloadAndSave($request->thumbnail, 'thumbnails/categories');
        }

        $category = Category::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'thumbnail' => $thumbnailPath,
            'categories' => $validated['categories'] ?? null,
            'is_new' => $request->boolean('isNew'),
            'is_hidden' => $request->boolean('isHidden'),
            'user_id' => Auth::id()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Category created successfully',
                'category' => $category
            ]);
        }

        return redirect()->route('panel.categories.index')
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
            'type' => 'required|in:links,files,both',
            'thumbnail' => 'nullable|url',
            'thumbnailfile' => 'nullable|image|max:5120',
            'categories' => 'nullable|string',
            'isNew' => 'boolean',
            'isHidden' => 'boolean'
        ]);

        $thumbnailPath = $category->thumbnail;

        // Handle file upload
        if ($request->hasFile('thumbnailfile')) {
            // Delete old thumbnail if it was a file upload
            if ($category->thumbnail && Str::startsWith($category->thumbnail, '/storage/')) {
                Storage::disk('public')->delete(Str::after($category->thumbnail, '/storage/'));
            }

            $file = $request->file('thumbnailfile');
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $thumbnailPath = $file->storeAs('categories/thumbnails', $fileName, 'public');
            $thumbnailPath = Storage::url($thumbnailPath);
        }
        // Use URL thumbnail if provided
        elseif ($request->filled('thumbnail')) {
            $thumbnailPath = \App\Helpers\ThumbnailHelper::downloadAndSave($request->thumbnail, 'thumbnails/categories', $category->thumbnail);
        }

        $category->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'thumbnail' => $thumbnailPath,
            'categories' => $validated['categories'] ?? null,
            'is_new' => $request->boolean('isNew'),
            'is_hidden' => $request->boolean('isHidden')
        ]);

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

        $category->delete();

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
