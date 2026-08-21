<?php

namespace App\Http\Controllers;

use App\Helpers\Encryptor;
use App\Helpers\FileEncryptor;
use App\Models\FileModal;
use App\Models\TodoCollection;
use App\Models\TodoStep;
use App\Models\TodoTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TodoController extends Controller
{
    /**
     * Helper to check if Vault is currently unlocked
     */
    protected function isVaultUnlocked(): bool
    {
        $lifetime = (int) (Auth::user()->vault_session_lifetime ?? 15);
        $lastActivity = session('vault_group_last_activity');
        $isAuth = session('vault_group_authenticated', false)
            || session('hidden_files_authenticated', false)
            || session('hidden_links_authenticated', false)
            || session('hidden_passwords_authenticated', false);

        if ($isAuth && $lastActivity && (now()->timestamp - $lastActivity < ($lifetime * 60))) {
            return true;
        }

        return false;
    }

    /**
     * Main Todo Workspace View
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $vaultUnlocked = $this->isVaultUnlocked();

        // 1. Fetch Collections
        $collectionsQuery = TodoCollection::where('user_id', $user->id)
            ->withCount([
                'tasks as pending_tasks_count' => fn($q) => $q->where('is_completed', false),
                'tasks as total_tasks_count',
            ])
            ->orderBy('sort_order')
            ->orderBy('id');

        if (!$vaultUnlocked) {
            $collectionsQuery->where('is_hidden', false);
        }

        $collections = $collectionsQuery->get();

        // 2. Determine Filter & Active Collection
        $filter = $request->query('filter', 'all');
        $collectionId = $request->query('collection');
        $status = $request->query('status', 'active');
        $selectedTaskId = $request->query('task');

        $activeCollection = null;
        if ($collectionId) {
            $activeCollection = TodoCollection::where('user_id', $user->id)
                ->where('id', $collectionId)
                ->first();
        }

        // 3. Build Task Query
        $taskQuery = TodoTask::where('user_id', $user->id)
            ->with(['collection', 'steps']);

        if (!$vaultUnlocked) {
            $taskQuery->where('is_hidden', false);
            $taskQuery->whereHas('collection', fn($q) => $q->where('is_hidden', false), '>=', 0);
        }

        // Apply Collection or Smart List Filter
        if ($activeCollection) {
            $taskQuery->where('todo_collection_id', $activeCollection->id);
        } else {
            switch ($filter) {
                case 'my_day':
                    $today = Carbon::today()->toDateString();
                    $taskQuery->where(function ($q) use ($today) {
                        $q->whereDate('due_date', $today)
                          ->orWhereDate('created_at', $today);
                    });
                    break;

                case 'important':
                    $taskQuery->where('is_starred', true);
                    break;

                case 'planned':
                    $taskQuery->whereNotNull('due_date')->orderBy('due_date');
                    break;

                case 'dashboard':
                    $taskQuery->where('is_pinned_to_dashboard', true);
                    break;

                case 'secret':
                    if ($vaultUnlocked) {
                        $taskQuery->where('is_hidden', true);
                    }
                    break;

                case 'all':
                default:
                    // All tasks
                    break;
            }
        }

        // Apply Status Filter
        if ($status === 'active') {
            $taskQuery->where('is_completed', false);
        } elseif ($status === 'completed') {
            $taskQuery->where('is_completed', true);
        }

        // Sort Tasks
        $tasks = $taskQuery->orderBy('sort_order')
            ->orderBy('is_completed')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderByDesc('is_starred')
            ->orderByDesc('id')
            ->get();

        // 4. Calculate Smart List Counts
        $counts = [
            'all' => TodoTask::where('user_id', $user->id)->where('is_completed', false)->count(),
            'my_day' => TodoTask::where('user_id', $user->id)->where('is_completed', false)->where(fn($q) => $q->whereDate('due_date', Carbon::today()->toDateString())->orWhereDate('created_at', Carbon::today()->toDateString()))->count(),
            'important' => TodoTask::where('user_id', $user->id)->where('is_completed', false)->where('is_starred', true)->count(),
            'planned' => TodoTask::where('user_id', $user->id)->where('is_completed', false)->whereNotNull('due_date')->count(),
            'dashboard' => TodoTask::where('user_id', $user->id)->where('is_completed', false)->where('is_pinned_to_dashboard', true)->count(),
            'secret' => $vaultUnlocked ? TodoTask::where('user_id', $user->id)->where('is_completed', false)->where('is_hidden', true)->count() : 0,
        ];

        // 5. Selected Task for Details Drawer
        $selectedTask = null;
        if ($selectedTaskId) {
            $selectedTask = TodoTask::where('user_id', $user->id)->with(['steps', 'collection'])->find($selectedTaskId);
        }

        return view('panel.todos.index', compact(
            'collections',
            'tasks',
            'activeCollection',
            'filter',
            'status',
            'counts',
            'selectedTask',
            'vaultUnlocked'
        ));
    }

    /**
     * Interactive Visual Calendar View
     */
    public function calendarView(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $vaultUnlocked = $this->isVaultUnlocked();

        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $currentDate = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        // Query tasks in date range
        $tasksQuery = TodoTask::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startOfMonth->copy()->subDays(7)->toDateString(), $endOfMonth->copy()->addDays(7)->toDateString()])
            ->with('collection');

        if (!$vaultUnlocked) {
            $tasksQuery->where('is_hidden', false);
        }

        $tasks = $tasksQuery->get();

        // Group tasks by due_date string (YYYY-MM-DD)
        $tasksByDate = [];
        foreach ($tasks as $task) {
            $dateKey = $task->due_date->toDateString();
            $tasksByDate[$dateKey][] = $task;
        }

        $collections = TodoCollection::where('user_id', $user->id)
            ->when(!$vaultUnlocked, fn($q) => $q->where('is_hidden', false))
            ->get();

        return view('panel.todos.calendar', compact(
            'month',
            'year',
            'currentDate',
            'startOfMonth',
            'endOfMonth',
            'tasksByDate',
            'collections',
            'vaultUnlocked'
        ));
    }

    /**
     * Get a single Task details with steps and attachments (for AJAX drawer)
     */
    public function showTask($id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)
            ->with(['steps', 'collection'])
            ->findOrFail($id);

        return response()->json([
            'ok' => 1,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'notes' => $task->notes,
                'is_completed' => (bool) $task->is_completed,
                'is_starred' => (bool) $task->is_starred,
                'is_pinned_to_dashboard' => (bool) $task->is_pinned_to_dashboard,
                'is_hidden' => (bool) $task->is_hidden,
                'due_date' => $task->due_date ? $task->due_date->toDateString() : '',
                'due_badge' => $task->due_badge,
                'repeat_interval' => $task->repeat_interval ?? 'none',
                'progress' => $task->progress,
                'created_at_formatted' => $task->created_at->format('M j, Y'),
                'collection' => $task->collection ? [
                    'id' => $task->collection->id,
                    'name' => $task->collection->name,
                    'color' => $task->collection->color,
                ] : null,
                'steps' => $task->steps->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'is_completed' => (bool) $s->is_completed,
                ]),
                'attachments' => $task->attachments ?: [],
            ],
        ]);
    }

    /**
     * Store a new Todo Task (Supports AJAX & Form)
     */
    public function storeTask(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'todo_collection_id' => 'nullable|exists:todo_collections,id',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
            'remind_at' => 'nullable|date',
            'repeat_interval' => 'nullable|in:none,daily,weekdays,weekly,monthly,yearly,custom',
            'is_starred' => 'nullable|boolean',
            'is_pinned_to_dashboard' => 'nullable|boolean',
            'is_hidden' => 'nullable|boolean',
            'file' => 'nullable|file|max:102400', // 100MB
        ]);

        $task = new TodoTask([
            'user_id' => $user->id,
            'todo_collection_id' => $validated['todo_collection_id'] ?? null,
            'title' => trim($validated['title']),
            'notes' => $validated['notes'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'remind_at' => $validated['remind_at'] ?? null,
            'repeat_interval' => $validated['repeat_interval'] ?? 'none',
            'is_starred' => $request->boolean('is_starred', false),
            'is_pinned_to_dashboard' => $request->boolean('is_pinned_to_dashboard', false),
            'is_hidden' => $request->boolean('is_hidden', false),
            'attachments' => [],
        ]);

        // Handle initial file attachment if present
        if ($request->hasFile('file')) {
            $uploadedAttachment = $this->processAndStoreAttachment($request->file('file'), $user);
            if ($uploadedAttachment) {
                $task->attachments = [$uploadedAttachment];
            }
        }

        $task->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Task created successfully.',
                'task' => $task->load(['steps', 'collection']),
            ]);
        }

        return redirect()->back()->with('success', 'Task created successfully.');
    }

    /**
     * Update an existing Todo Task
     */
    public function updateTask(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'todo_collection_id' => 'nullable|exists:todo_collections,id',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
            'remind_at' => 'nullable|date',
            'repeat_interval' => 'nullable|in:none,daily,weekdays,weekly,monthly,yearly,custom',
            'is_starred' => 'nullable|boolean',
            'is_pinned_to_dashboard' => 'nullable|boolean',
            'is_hidden' => 'nullable|boolean',
        ]);

        if (array_key_exists('title', $validated)) {
            $task->title = trim($validated['title']);
        }
        if (array_key_exists('todo_collection_id', $validated)) {
            $task->todo_collection_id = $validated['todo_collection_id'];
        }
        if (array_key_exists('notes', $validated)) {
            $task->notes = $validated['notes'];
        }
        if (array_key_exists('due_date', $validated)) {
            $task->due_date = $validated['due_date'];
        }
        if (array_key_exists('remind_at', $validated)) {
            $task->remind_at = $validated['remind_at'];
        }
        if (array_key_exists('repeat_interval', $validated)) {
            $task->repeat_interval = $validated['repeat_interval'] ?? 'none';
        }
        if ($request->has('is_starred')) {
            $task->is_starred = $request->boolean('is_starred');
        }
        if ($request->has('is_pinned_to_dashboard')) {
            $task->is_pinned_to_dashboard = $request->boolean('is_pinned_to_dashboard');
        }
        if ($request->has('is_hidden')) {
            $task->is_hidden = $request->boolean('is_hidden');
        }

        $task->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Task updated.',
                'task' => $task->load(['steps', 'collection']),
            ]);
        }

        return redirect()->back()->with('success', 'Task updated.');
    }

    /**
     * Toggle Task Completion Status
     */
    public function toggleTask($id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->findOrFail($id);

        $newStatus = !$task->is_completed;
        $task->is_completed = $newStatus;
        $task->completed_at = $newStatus ? now() : null;
        $task->save();

        $rolledOver = false;
        // Check if recurrence should advance upon completion
        if ($newStatus && $task->repeat_interval !== 'none' && !empty($task->repeat_interval)) {
            $task->advanceRecurrence();
            $rolledOver = true;
        }

        return response()->json([
            'ok' => 1,
            'is_completed' => $task->is_completed,
            'completed_at' => $task->completed_at ? $task->completed_at->toDateTimeString() : null,
            'rolled_over' => $rolledOver,
            'due_date' => $task->due_date ? $task->due_date->toDateString() : null,
            'due_badge' => $task->due_badge,
            'task' => $task->load(['steps', 'collection']),
        ]);
    }

    /**
     * Toggle Star / Important Status
     */
    public function toggleStar($id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->findOrFail($id);

        $task->is_starred = !$task->is_starred;
        $task->save();

        return response()->json([
            'ok' => 1,
            'is_starred' => $task->is_starred,
        ]);
    }

    /**
     * Toggle Pin to Dashboard Status
     */
    public function toggleDashboardPin($id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->findOrFail($id);

        $task->is_pinned_to_dashboard = !$task->is_pinned_to_dashboard;
        $task->save();

        return response()->json([
            'ok' => 1,
            'is_pinned_to_dashboard' => $task->is_pinned_to_dashboard,
        ]);
    }

    /**
     * Delete a Todo Task
     */
    public function destroyTask($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->findOrFail($id);

        // Clean up any attached files & user storage quota
        if (!empty($task->attachments) && is_array($task->attachments)) {
            foreach ($task->attachments as $att) {
                if (!empty($att['file_modal_id'])) {
                    $file = FileModal::where('user_id', $user->id)->find($att['file_modal_id']);
                    if ($file) {
                        $user->subtractStorageUsage($file->size);
                        $fullPath = Storage::disk('local')->path($file->path);
                        FileEncryptor::cryptoShred($fullPath);
                        @unlink(public_path($file->path));
                        $file->forceDelete();
                    }
                }
            }
        }

        $task->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Task deleted.',
            ]);
        }

        return redirect()->route('panel.todos.index')->with('success', 'Task deleted.');
    }

    /**
     * Add a Sub-step to a Task
     */
    public function storeStep(Request $request, $taskId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->findOrFail($taskId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $step = $task->steps()->create([
            'title' => trim($validated['title']),
            'is_completed' => false,
            'sort_order' => $task->steps()->count(),
        ]);

        return response()->json([
            'ok' => 1,
            'step' => $step,
            'progress' => $task->progress,
        ]);
    }

    /**
     * Toggle Sub-step Completion
     */
    public function toggleStep($stepId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $step = TodoStep::whereHas('task', fn($q) => $q->where('user_id', $user->id))->findOrFail($stepId);

        $step->is_completed = !$step->is_completed;
        $step->save();

        $task = $step->task;

        return response()->json([
            'ok' => 1,
            'is_completed' => $step->is_completed,
            'progress' => $task->progress,
        ]);
    }

    /**
     * Delete a Sub-step
     */
    public function destroyStep($stepId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $step = TodoStep::whereHas('task', fn($q) => $q->where('user_id', $user->id))->findOrFail($stepId);
        $task = $step->task;

        $step->delete();

        return response()->json([
            'ok' => 1,
            'message' => 'Step removed.',
            'progress' => $task->progress,
        ]);
    }

    /**
     * Upload and Attach a File to Task (Encrypted at rest with AES-256-GCM)
     */
    public function uploadAttachment(Request $request, $taskId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->findOrFail($taskId);

        $request->validate([
            'file' => 'required|file|max:102400', // 100MB
        ]);

        $attachmentData = $this->processAndStoreAttachment($request->file('file'), $user);

        if (!$attachmentData) {
            return response()->json([
                'ok' => 0,
                'message' => 'Storage quota exceeded or encryption failed.',
            ], 400);
        }

        $currentAttachments = $task->attachments ?: [];
        $currentAttachments[] = $attachmentData;
        $task->attachments = $currentAttachments;
        $task->save();

        return response()->json([
            'ok' => 1,
            'attachment' => $attachmentData,
            'attachments' => $task->attachments,
        ]);
    }

    /**
     * Delete an Attachment from Task
     */
    public function deleteAttachment($taskId, $index): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $task = TodoTask::where('user_id', $user->id)->findOrFail($taskId);

        $attachments = $task->attachments ?: [];
        $index = (int) $index;

        if (isset($attachments[$index])) {
            $att = $attachments[$index];
            if (!empty($att['file_modal_id'])) {
                $file = FileModal::where('user_id', $user->id)->find($att['file_modal_id']);
                if ($file) {
                    $user->subtractStorageUsage($file->size);
                    $fullPath = Storage::disk('local')->path($file->path);
                    FileEncryptor::cryptoShred($fullPath);
                    @unlink(public_path($file->path));
                    $file->forceDelete();
                }
            }
            array_splice($attachments, $index, 1);
            $task->attachments = $attachments;
            $task->save();
        }

        return response()->json([
            'ok' => 1,
            'attachments' => $task->attachments,
        ]);
    }

    /**
     * Store a new Todo Collection
     */
    public function storeCollection(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'is_hidden' => 'nullable|boolean',
            'cover' => 'nullable|image|max:10240', // 10MB
        ]);

        $coverFileId = null;
        $coverPath = null;

        if ($request->hasFile('cover')) {
            $coverAtt = $this->processAndStoreAttachment($request->file('cover'), $user, 'collection-cover');
            if ($coverAtt) {
                $coverFileId = $coverAtt['file_modal_id'];
                $coverPath = $coverAtt['preview_url'];
            }
        }

        $collection = TodoCollection::create([
            'user_id' => $user->id,
            'name' => trim($validated['name']),
            'color' => $validated['color'] ?? '#6366f1',
            'icon' => $validated['icon'] ?? 'list-todo',
            'cover_image' => $coverPath,
            'cover_file_id' => $coverFileId,
            'is_hidden' => $request->boolean('is_hidden', false),
            'sort_order' => TodoCollection::where('user_id', $user->id)->count(),
        ]);

        // Sync with Category model (Tasks type)
        \App\Models\Category::firstOrCreate(
            ['user_id' => $user->id, 'type' => 'tasks', 'title' => $collection->name],
            [
                'description' => 'Todo & Task list collection',
                'thumbnail' => $collection->cover_image,
                'is_hidden' => $collection->is_hidden,
                'is_new' => false,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => 1,
                'collection' => $collection,
            ]);
        }

        return redirect()->route('panel.todos.index', ['collection' => $collection->id])
            ->with('success', 'Collection created.');
    }

    /**
     * Update a Todo Collection
     */
    public function updateCollection(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();
        $collection = TodoCollection::where('user_id', $user->id)->findOrFail($id);
        $oldName = $collection->name;

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'is_hidden' => 'nullable|boolean',
            'cover' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('cover')) {
            // Remove old cover file if exists
            if ($collection->cover_file_id) {
                $oldFile = FileModal::where('user_id', $user->id)->find($collection->cover_file_id);
                if ($oldFile) {
                    $user->subtractStorageUsage($oldFile->size);
                    $fullPath = Storage::disk('local')->path($oldFile->path);
                    FileEncryptor::cryptoShred($fullPath);
                    @unlink(public_path($oldFile->path));
                    $oldFile->forceDelete();
                }
            }

            $coverAtt = $this->processAndStoreAttachment($request->file('cover'), $user, 'collection-cover');
            if ($coverAtt) {
                $collection->cover_file_id = $coverAtt['file_modal_id'];
                $collection->cover_image = $coverAtt['preview_url'];
            }
        }

        if (array_key_exists('name', $validated)) {
            $collection->name = trim($validated['name']);
        }
        if (array_key_exists('color', $validated)) {
            $collection->color = $validated['color'];
        }
        if (array_key_exists('icon', $validated)) {
            $collection->icon = $validated['icon'];
        }
        if ($request->has('is_hidden')) {
            $collection->is_hidden = $request->boolean('is_hidden');
        }

        $collection->save();

        // Sync with Category
        $cat = \App\Models\Category::where('user_id', $user->id)
            ->where('type', 'tasks')
            ->where('title', $oldName)
            ->first();
        if ($cat) {
            $cat->update([
                'title' => $collection->name,
                'thumbnail' => $collection->cover_image,
                'is_hidden' => $collection->is_hidden,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => 1,
                'collection' => $collection,
            ]);
        }

        return redirect()->back()->with('success', 'Collection updated.');
    }

    /**
     * Delete a Todo Collection
     */
    public function destroyCollection($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $collection = TodoCollection::where('user_id', $user->id)->findOrFail($id);
        $colName = $collection->name;

        if ($collection->cover_file_id) {
            $file = FileModal::where('user_id', $user->id)->find($collection->cover_file_id);
            if ($file) {
                $user->subtractStorageUsage($file->size);
                $fullPath = Storage::disk('local')->path($file->path);
                FileEncryptor::cryptoShred($fullPath);
                @unlink(public_path($file->path));
                $file->forceDelete();
            }
        }

        $collection->delete();

        // Sync deletion with Category
        \App\Models\Category::where('user_id', $user->id)
            ->where('type', 'tasks')
            ->where('title', $colName)
            ->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'ok' => 1,
                'message' => 'Collection deleted.',
            ]);
        }

        return redirect()->route('panel.todos.index')->with('success', 'Collection deleted.');
    }

    /**
     * Export Tasks to JSON, Markdown, or CSV
     */
    public function export(Request $request, string $format): StreamedResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $vaultUnlocked = $this->isVaultUnlocked();

        $collectionId = $request->query('collection');
        $query = TodoTask::where('user_id', $user->id)->with(['collection', 'steps']);

        if (!$vaultUnlocked) {
            $query->where('is_hidden', false);
        }
        if ($collectionId) {
            $query->where('todo_collection_id', $collectionId);
        }

        $tasks = $query->orderBy('is_completed')->orderBy('due_date')->get();
        $filename = 'filefusion_todos_' . now()->format('Y-m-d_His');

        switch (strtolower($format)) {
            case 'json':
                $data = [
                    'exported_at' => now()->toIso8601String(),
                    'user' => $user->name,
                    'total_tasks' => $tasks->count(),
                    'tasks' => $tasks->map(fn($t) => [
                        'title' => $t->title,
                        'collection' => $t->collection->name ?? 'Uncategorized',
                        'is_completed' => $t->is_completed,
                        'is_starred' => $t->is_starred,
                        'is_pinned_to_dashboard' => $t->is_pinned_to_dashboard,
                        'due_date' => $t->due_date ? $t->due_date->toDateString() : null,
                        'repeat_interval' => $t->repeat_interval,
                        'notes' => $t->notes,
                        'steps' => $t->steps->map(fn($s) => [
                            'title' => $s->title,
                            'is_completed' => $s->is_completed,
                        ]),
                    ]),
                ];

                return response()->streamDownload(function () use ($data) {
                    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }, $filename . '.json', ['Content-Type' => 'application/json']);

            case 'csv':
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
                ];

                return response()->stream(function () use ($tasks) {
                    $handle = fopen('php://output', 'w');
                    fputcsv($handle, ['ID', 'Title', 'Collection', 'Status', 'Starred', 'Dashboard Pinned', 'Due Date', 'Repeat', 'Steps Completed', 'Total Steps', 'Notes']);

                    foreach ($tasks as $t) {
                        fputcsv($handle, [
                            $t->id,
                            $t->title,
                            $t->collection->name ?? 'Uncategorized',
                            $t->is_completed ? 'Completed' : 'Pending',
                            $t->is_starred ? 'Yes' : 'No',
                            $t->is_pinned_to_dashboard ? 'Yes' : 'No',
                            $t->due_date ? $t->due_date->toDateString() : '',
                            $t->repeat_interval,
                            $t->progress['completed'],
                            $t->progress['total'],
                            $t->notes ?? '',
                        ]);
                    }
                    fclose($handle);
                }, 200, $headers);

            case 'markdown':
            case 'md':
            default:
                $headers = [
                    'Content-Type' => 'text/markdown',
                    'Content-Disposition' => "attachment; filename=\"{$filename}.md\"",
                ];

                return response()->stream(function () use ($tasks, $user) {
                    echo "# FileFusion Tasks Export\n\n";
                    echo "**User:** {$user->name} ({$user->email})  \n";
                    echo "**Exported Date:** " . now()->format('M j, Y H:i:s') . "  \n\n";
                    echo "---\n\n";

                    foreach ($tasks as $t) {
                        $check = $t->is_completed ? '[x]' : '[ ]';
                        $star = $t->is_starred ? ' ⭐' : '';
                        $col = $t->collection ? " `{$t->collection->name}`" : '';
                        $due = $t->due_date ? " (Due: {$t->due_date->format('M j, Y')})" : '';
                        $repeat = $t->repeat_interval !== 'none' ? " 🔄 {$t->repeat_interval}" : '';

                        echo "- {$check} **{$t->title}**{$star}{$col}{$due}{$repeat}\n";

                        if ($t->notes) {
                            echo "  > " . str_replace("\n", "\n  > ", trim($t->notes)) . "\n";
                        }

                        foreach ($t->steps as $s) {
                            $stepCheck = $s->is_completed ? '[x]' : '[ ]';
                            echo "  - {$stepCheck} {$s->title}\n";
                        }

                        echo "\n";
                    }
                }, 200, $headers);
        }
    }

    /**
     * Shared helper to store file with AES-256-GCM envelope encryption, register in FileModal with tag 'todo', and update user storage quota
     */
    protected function processAndStoreAttachment($file, User $user, string $subfolder = 'todos'): ?array
    {
        $fileSize = $file->getSize();

        // Verify storage quota limit
        if (!$user->hasEnoughStorage($fileSize)) {
            return null;
        }

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $disk = 'local';
        $userFolder = "user_{$user->id}";
        $randomFileName = Str::random(35) . '.' . $extension;
        $relativeEncPath = "{$userFolder}/{$randomFileName}.enc";
        $finalFullPath = Storage::disk($disk)->path($relativeEncPath);

        $dir = dirname($finalFullPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        try {
            $encMetadata = FileEncryptor::encryptFile($file->getRealPath(), $finalFullPath);
            $finalSize = $encMetadata['original_size'] ?? $fileSize;
        } catch (\Exception $e) {
            return null;
        }

        // Create FileModal record tagged as 'todo'
        $fileRecord = FileModal::create([
            'name' => $originalName,
            'path' => $relativeEncPath,
            'size' => $finalSize,
            'type' => $mimeType,
            'user_id' => $user->id,
            'status' => '1',
            'is_trashed' => 0,
        ]);

        // Increment user storage quota
        $user->addStorageUsage($finalSize);

        $encId = encrypt($fileRecord->id);

        return [
            'file_modal_id' => $fileRecord->id,
            'name' => $originalName,
            'path' => $relativeEncPath,
            'size' => $finalSize,
            'formatted_size' => BytetoSize($finalSize),
            'mime' => $mimeType,
            'url' => route('panel.downloadFile', $encId),
            'preview_url' => route('panel.previewFile', $encId),
        ];
    }
}
