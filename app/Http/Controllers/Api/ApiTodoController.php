<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TodoCollection;
use App\Models\TodoTask;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTodoController extends Controller
{
    /**
     * List user's tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = TodoTask::where('user_id', $user->id)->with(['collection', 'steps']);

        if ($request->boolean('completed_only')) {
            $query->where('is_completed', true);
        } elseif ($request->boolean('pending_only')) {
            $query->where('is_completed', false);
        }

        if ($request->filled('collection_id')) {
            $colId = $this->resolveId($request->query('collection_id'));
            if ($colId) {
                $query->where('todo_collection_id', $colId);
            }
        }

        if ($request->boolean('starred_only')) {
            $query->where('is_starred', true);
        }

        if ($request->boolean('only_hidden')) {
            $query->where('is_hidden', true);
        } elseif (!$request->boolean('include_hidden')) {
            $query->where('is_hidden', false);
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 30)));
        $tasks = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->paginate($perPage);

        $search = strtolower(trim($request->query('search', $request->query('q', ''))));

        $items = collect($tasks->items())->map(function ($t) {
            return [
                'id' => encrypt($t->id),
                'title' => $t->title,
                'notes' => $t->notes,
                'is_completed' => (bool) $t->is_completed,
                'is_starred' => (bool) $t->is_starred,
                'is_hidden' => (bool) $t->is_hidden,
                'due_date' => $t->due_date?->format('Y-m-d'),
                'is_overdue' => $t->is_overdue,
                'due_badge' => $t->due_badge,
                'collection' => $t->collection ? [
                    'id' => encrypt($t->collection->id),
                    'name' => $t->collection->name,
                    'color' => $t->collection->color,
                    'icon' => $t->collection->icon,
                ] : null,
                'steps_count' => $t->steps->count(),
                'completed_steps_count' => $t->steps->where('is_completed', true)->count(),
                'created_at' => $t->created_at?->toIso8601String(),
                'completed_at' => $t->completed_at?->toIso8601String(),
            ];
        });

        if ($search !== '') {
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['title'] ?? ''), $search)
                    || str_contains(strtolower($item['notes'] ?? ''), $search);
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage(),
            ]
        ]);
    }

    /**
     * Store new task.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'todo_collection_id' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_starred' => 'nullable|boolean',
            'is_hidden' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        $colId = null;
        if (!empty($validated['todo_collection_id'])) {
            $colId = $this->resolveId($validated['todo_collection_id']);
        }

        $task = new TodoTask();
        $task->user_id = $user->id;
        $task->title = $validated['title'];
        $task->notes = $validated['notes'] ?? null;
        $task->todo_collection_id = $colId;
        $task->due_date = !empty($validated['due_date']) ? Carbon::parse($validated['due_date']) : null;
        $task->is_starred = $request->boolean('is_starred', false);
        $task->is_hidden = $request->boolean('is_hidden', false);
        $task->is_completed = false;
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully.',
            'data' => [
                'id' => encrypt($task->id),
                'title' => $task->title,
                'notes' => $task->notes,
                'is_completed' => (bool) $task->is_completed,
                'is_starred' => (bool) $task->is_starred,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'created_at' => $task->created_at?->toIso8601String(),
            ]
        ], 201);
    }

    /**
     * Show single task.
     */
    public function show(string $id): JsonResponse
    {
        $resolvedId = $this->resolveId($id);
        if (!$resolvedId) {
            return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
        }

        $user = Auth::user();
        $t = TodoTask::where('user_id', $user->id)->with(['collection', 'steps'])->find($resolvedId);

        if (!$t) {
            return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => encrypt($t->id),
                'title' => $t->title,
                'notes' => $t->notes,
                'is_completed' => (bool) $t->is_completed,
                'is_starred' => (bool) $t->is_starred,
                'is_hidden' => (bool) $t->is_hidden,
                'due_date' => $t->due_date?->format('Y-m-d'),
                'collection' => $t->collection ? [
                    'id' => encrypt($t->collection->id),
                    'name' => $t->collection->name,
                    'color' => $t->collection->color,
                ] : null,
                'steps' => $t->steps->map(fn($s) => [
                    'id' => encrypt($s->id),
                    'title' => $s->title,
                    'is_completed' => (bool) $s->is_completed,
                ]),
                'created_at' => $t->created_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Update task status / details.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $resolvedId = $this->resolveId($id);
        if (!$resolvedId) {
            return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
        }

        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->find($resolvedId);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'is_completed' => 'nullable|boolean',
            'is_starred' => 'nullable|boolean',
            'due_date' => 'nullable|date',
            'todo_collection_id' => 'nullable|string',
        ]);

        if ($request->has('title')) {
            $task->title = $validated['title'];
        }
        if ($request->has('notes')) {
            $task->notes = $validated['notes'];
        }
        if ($request->has('is_completed')) {
            $task->is_completed = $request->boolean('is_completed');
            $task->completed_at = $task->is_completed ? now() : null;
        }
        if ($request->has('is_starred')) {
            $task->is_starred = $request->boolean('is_starred');
        }
        if ($request->has('due_date')) {
            $task->due_date = !empty($validated['due_date']) ? Carbon::parse($validated['due_date']) : null;
        }
        if ($request->has('todo_collection_id')) {
            $task->todo_collection_id = $this->resolveId($validated['todo_collection_id']);
        }

        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.',
            'data' => [
                'id' => encrypt($task->id),
                'title' => $task->title,
                'is_completed' => (bool) $task->is_completed,
                'is_starred' => (bool) $task->is_starred,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'updated_at' => $task->updated_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Delete task.
     */
    public function destroy(string $id): JsonResponse
    {
        $resolvedId = $this->resolveId($id);
        if (!$resolvedId) {
            return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
        }

        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->find($resolvedId);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
        }

        $task->steps()->delete();
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.'
        ]);
    }

    /**
     * Resolve ID.
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
