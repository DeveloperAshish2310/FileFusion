@extends('layout.backend')
@push('title', 'Tasks & Todos')

@section('css')
<style>
    .todo-workspace {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr) 340px;
        gap: 20px;
        min-height: calc(100vh - 120px);
        align-items: start;
        position: relative;
    }
    @media (max-width: 1200px) {
        .todo-workspace {
            grid-template-columns: 240px minmax(0, 1fr);
        }
        .todo-drawer {
            position: fixed !important;
            top: 0;
            right: 0;
            bottom: 0;
            width: 380px !important;
            max-width: 90vw !important;
            z-index: 99999 !important;
            box-shadow: -10px 0 40px rgba(0,0,0,0.5) !important;
        }
    }
    @media (max-width: 768px) {
        .todo-workspace {
            grid-template-columns: 1fr;
        }
        .todo-sidebar {
            display: none;
        }
        .todo-sidebar.is-mobile-open {
            display: block;
        }
    }

    /* Left Sidebar */
    .todo-sidebar {
        background: var(--ff-card, #ffffff);
        border: 1px solid var(--ff-border, #e2e8f0);
        border-radius: 16px;
        padding: 18px 14px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .todo-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border-radius: 10px;
        color: var(--ff-text-2, #64748b);
        font-size: 13.5px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
        position: relative;
    }
    .todo-nav-item:hover {
        background: var(--ff-bg-2, #f8fafc);
        color: var(--ff-text, #0f172a);
    }
    .todo-nav-item.is-active {
        background: rgba(99, 102, 241, 0.12);
        color: var(--ff-accent, #6366f1);
        font-weight: 600;
    }
    .todo-badge {
        margin-left: auto;
        font-size: 11.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 999px;
        background: var(--ff-bg-2, #e2e8f0);
        color: var(--ff-text-2, #64748b);
    }
    .todo-nav-item.is-active .todo-badge {
        background: var(--ff-accent, #6366f1);
        color: #ffffff;
    }

    /* Center Feed */
    .todo-feed {
        background: var(--ff-card, #ffffff);
        border: 1px solid var(--ff-border, #e2e8f0);
        border-radius: 18px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 600px;
    }
    .todo-cover-header {
        position: relative;
        padding: 28px 28px 20px;
        background-size: cover;
        background-position: center;
        border-bottom: 1px solid var(--ff-border, #e2e8f0);
    }
    .todo-cover-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, rgba(15,23,42,0.4), rgba(15,23,42,0.85));
        z-index: 1;
    }
    .todo-header-content {
        position: relative;
        z-index: 2;
    }

    /* Task Card */
    .todo-task-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 20px;
        border-bottom: 1px solid var(--ff-border, #f1f5f9);
        transition: background 0.15s ease;
        cursor: pointer;
    }
    .todo-task-item:hover {
        background: var(--ff-bg-2, #f8fafc);
    }
    .todo-task-item.is-selected {
        background: rgba(99, 102, 241, 0.08);
        border-left: 3px solid var(--ff-accent, #6366f1);
    }
    .todo-task-title {
        font-size: 14.5px;
        color: var(--ff-text, #0f172a);
        font-weight: 500;
        transition: color 0.15s ease;
    }
    .todo-task-item.is-done .todo-task-title {
        text-decoration: line-through;
        color: var(--ff-muted, #94a3b8);
    }

    /* Custom Checkbox */
    .todo-check {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid var(--ff-border, #cbd5e1);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--ff-card, #ffffff);
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        position: relative;
    }
    .todo-check:hover {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        transform: scale(1.08);
    }
    .todo-check:active {
        transform: scale(0.92);
    }
    .todo-check.is-checked {
        background: #10b981;
        border-color: #10b981;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
    }

    /* Star Button */
    .btn-star {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        color: var(--ff-muted, #cbd5e1);
        transition: transform 0.15s ease, color 0.15s ease;
    }
    .btn-star:hover {
        transform: scale(1.15);
        color: #f59e0b;
    }
    .btn-star.is-starred {
        color: #f59e0b;
    }

    /* Right Detail Drawer */
    .todo-drawer {
        background: var(--ff-card, #ffffff);
        border: 1px solid var(--ff-border, #e2e8f0);
        border-radius: 18px;
        padding: 24px 20px;
        display: flex;
        flex-direction: column;
        gap: 18px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }
    .drawer-section {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .drawer-label {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--ff-muted, #94a3b8);
    }

    /* Chips */
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .chip-due {
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
    }
    .chip-overdue {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    .chip-repeat {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .chip-substep {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }
    .chip-pinned {
        background: rgba(168, 85, 247, 0.12);
        color: #a855f7;
    }
</style>
@endsection

@section('content')
<div class="todo-workspace">

    {{-- ============================== 1. LEFT SIDEBAR ============================== --}}
    <aside class="todo-sidebar">
        {{-- Workspace Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding-bottom:12px; border-bottom:1px solid var(--ff-border, #e2e8f0);">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="ff-tile-icon" style="width:32px; height:32px; border-radius:8px; background:linear-gradient(135deg, #6366f1, #a855f7); color:#fff;">
                    <i data-lucide="check-square" style="width:16px; height:16px;"></i>
                </span>
                <span style="font-family:'Outfit',sans-serif; font-size:16px; font-weight:700; color:var(--ff-text);">Tasks &amp; Todos</span>
            </div>
            <a href="{{ route('panel.todos.calendar') }}" class="ff-hint" title="Open Visual Calendar" style="color:var(--ff-accent); display:flex; align-items:center; padding:4px;">
                <i data-lucide="calendar" style="width:16px; height:16px;"></i>
            </a>
        </div>

        {{-- Smart Lists --}}
        <div style="display:flex; flex-direction:column; gap:2px;">
            <a href="{{ route('panel.todos.index', ['filter' => 'my_day']) }}" class="todo-nav-item {{ $filter === 'my_day' && !$activeCollection ? 'is-active' : '' }}">
                <i data-lucide="sun" style="width:16px; height:16px; color:#f59e0b;"></i>
                <span>My Day</span>
                <span class="todo-badge">{{ $counts['my_day'] }}</span>
            </a>
            <a href="{{ route('panel.todos.index', ['filter' => 'important']) }}" class="todo-nav-item {{ $filter === 'important' && !$activeCollection ? 'is-active' : '' }}">
                <i data-lucide="star" style="width:16px; height:16px; color:#eab308;"></i>
                <span>Important</span>
                <span class="todo-badge">{{ $counts['important'] }}</span>
            </a>
            <a href="{{ route('panel.todos.index', ['filter' => 'planned']) }}" class="todo-nav-item {{ $filter === 'planned' && !$activeCollection ? 'is-active' : '' }}">
                <i data-lucide="calendar-clock" style="width:16px; height:16px; color:#6366f1;"></i>
                <span>Planned</span>
                <span class="todo-badge">{{ $counts['planned'] }}</span>
            </a>
            <a href="{{ route('panel.todos.index', ['filter' => 'dashboard']) }}" class="todo-nav-item {{ $filter === 'dashboard' && !$activeCollection ? 'is-active' : '' }}">
                <i data-lucide="pin" style="width:16px; height:16px; color:#a855f7;"></i>
                <span>Dashboard Pinned</span>
                <span class="todo-badge">{{ $counts['dashboard'] }}</span>
            </a>
            <a href="{{ route('panel.todos.index', ['filter' => 'all']) }}" class="todo-nav-item {{ $filter === 'all' && !$activeCollection ? 'is-active' : '' }}">
                <i data-lucide="layers" style="width:16px; height:16px; color:#06b6d4;"></i>
                <span>All Tasks</span>
                <span class="todo-badge">{{ $counts['all'] }}</span>
            </a>
            @if ($vaultUnlocked)
                <a href="{{ route('panel.todos.index', ['filter' => 'secret']) }}" class="todo-nav-item {{ $filter === 'secret' && !$activeCollection ? 'is-active' : '' }}">
                    <i data-lucide="lock" style="width:16px; height:16px; color:#ec4899;"></i>
                    <span>Secret Vault</span>
                    <span class="todo-badge">{{ $counts['secret'] }}</span>
                </a>
            @endif
        </div>

        {{-- Custom Collections --}}
        <div style="margin-top:6px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; padding:0 4px;">
                <span class="drawer-label">Collections</span>
                <button type="button" onclick="openCollectionModal()" class="ff-hint" style="background:none; border:none; cursor:pointer; color:var(--ff-accent); font-weight:700; font-size:11.5px; display:flex; align-items:center; gap:2px;">
                    <i data-lucide="plus" style="width:13px; height:13px;"></i> New
                </button>
            </div>

            <div style="display:flex; flex-direction:column; gap:2px; max-height:240px; overflow-y:auto;">
                @forelse ($collections as $col)
                    <a href="{{ route('panel.todos.index', ['collection' => $col->id]) }}" class="todo-nav-item {{ $activeCollection && $activeCollection->id == $col->id ? 'is-active' : '' }}">
                        <span style="width:10px; height:10px; border-radius:50%; background:{{ $col->color }}; flex-shrink:0;"></span>
                        <span class="ff-truncate">{{ $col->name }}</span>
                        @if ($col->is_hidden)
                            <i data-lucide="lock" style="width:12px; height:12px; color:#ec4899;"></i>
                        @endif
                        <span class="todo-badge">{{ $col->pending_tasks_count }}</span>
                    </a>
                @empty
                    <div class="ff-hint" style="font-size:12px; text-align:center; padding:12px 0;">No custom lists yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Export Tools --}}
        <div style="margin-top:auto; padding-top:12px; border-top:1px solid var(--ff-border, #e2e8f0);">
            <div class="ff-dropdown-wrap" style="width:100%;">
                <button type="button" class="ff-btn" style="width:100%; justify-content:center; gap:6px; font-size:12.5px;" onclick="document.getElementById('exportMenu').classList.toggle('is-open')">
                    <i data-lucide="download" style="width:14px; height:14px;"></i> Export Tasks
                </button>
                <div id="exportMenu" class="ff-dropdown-menu" style="left:0; right:0; bottom:100%; top:auto; margin-bottom:6px;">
                    <a href="{{ route('panel.todos.export', ['format' => 'json', 'collection' => $activeCollection?->id]) }}" class="ff-dropdown-item">
                        <i data-lucide="file-code" class="w-4 h-4"></i> Export JSON
                    </a>
                    <a href="{{ route('panel.todos.export', ['format' => 'md', 'collection' => $activeCollection?->id]) }}" class="ff-dropdown-item">
                        <i data-lucide="file-text" class="w-4 h-4"></i> Export Markdown (.md)
                    </a>
                    <a href="{{ route('panel.todos.export', ['format' => 'csv', 'collection' => $activeCollection?->id]) }}" class="ff-dropdown-item">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </aside>


    {{-- ============================== 2. CENTER TASK FEED ============================== --}}
    <main class="todo-feed">
        {{-- Collection / Smart List Banner Header --}}
        <div class="todo-cover-header" style="{{ $activeCollection && $activeCollection->cover_image ? 'background-image:url(' . $activeCollection->cover_image . '); color:#fff;' : '' }}">
            @if ($activeCollection && $activeCollection->cover_image)
                <div class="todo-cover-overlay"></div>
            @endif

            <div class="todo-header-content">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <h1 style="font-family:'Outfit',sans-serif; font-size:24px; font-weight:800; margin:0; letter-spacing:-0.5px;">
                                @if ($activeCollection)
                                    <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:{{ $activeCollection->color }}; margin-right:4px;"></span>
                                    {{ $activeCollection->name }}
                                @else
                                    @switch($filter)
                                        @case('my_day') ☀️ My Day @break
                                        @case('important') ⭐ Important Tasks @break
                                        @case('planned') 📅 Planned Schedule @break
                                        @case('dashboard') 📌 Pinned to Dashboard @break
                                        @case('secret') 🔒 Secret Vault Tasks @break
                                        @default 📋 All Tasks
                                    @endswitch
                                @endif
                            </h1>
                        </div>
                        <p class="ff-hint" style="margin-top:4px; font-size:13px; {{ $activeCollection && $activeCollection->cover_image ? 'color:rgba(255,255,255,0.8);' : '' }}">
                            {{ now()->format('l, F j') }} · {{ $tasks->where('is_completed', false)->count() }} pending tasks
                        </p>
                    </div>

                    {{-- Actions for Collection --}}
                    @if ($activeCollection)
                        <div style="display:flex; gap:6px;">
                            <button type="button" onclick="editActiveCollection()" class="ff-btn" style="padding:6px 10px; font-size:12px;">
                                <i data-lucide="edit-3" style="width:13px; height:13px;"></i> Edit
                            </button>
                            <form action="{{ route('panel.todos.collections.destroy', $activeCollection->id) }}" method="POST" onsubmit="return confirm('Delete this collection? Tasks will become uncategorized.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ff-btn is-danger" style="padding:6px 10px; font-size:12px;">
                                    <i data-lucide="trash-2" style="width:13px; height:13px;"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Status Tabs (Active / Completed / All) --}}
                <div style="display:flex; gap:8px; margin-top:16px;">
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}" class="chip {{ $status === 'active' ? 'chip-due' : '' }}" style="text-decoration:none;">Active</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}" class="chip {{ $status === 'completed' ? 'chip-due' : '' }}" style="text-decoration:none;">Completed</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="chip {{ $status === 'all' ? 'chip-due' : '' }}" style="text-decoration:none;">All</a>
                </div>
            </div>
        </div>

        {{-- Fast Inline Task Adder --}}
        <div style="padding:14px 20px; border-bottom:1px solid var(--ff-border, #e2e8f0); background:var(--ff-bg-2, #f8fafc);">
            <form id="formQuickTask" onsubmit="event.preventDefault(); submitQuickTask();" style="display:flex; align-items:center; gap:10px;">
                @csrf
                <input type="hidden" name="todo_collection_id" id="quickTaskColId" value="{{ $activeCollection?->id }}">
                <input type="hidden" name="is_starred" id="quickTaskStarred" value="{{ $filter === 'important' ? 1 : 0 }}">
                <input type="hidden" name="is_pinned_to_dashboard" id="quickTaskPinned" value="{{ $filter === 'dashboard' ? 1 : 0 }}">
                <input type="hidden" name="due_date" id="quickTaskDueDate" value="{{ $filter === 'my_day' ? now()->toDateString() : '' }}">

                <div class="todo-check" style="opacity:0.4; cursor:default;">
                    <i data-lucide="plus" style="width:12px; height:12px;"></i>
                </div>
                <input type="text" name="title" id="quickTaskTitle" class="ff-input" placeholder="+ Add a task to this list, press Enter..." style="border:none; background:transparent; font-size:14.5px; padding:6px 0;" required autocomplete="off">

                <button type="submit" class="ff-btn ff-btn-primary" style="padding:7px 14px; font-size:13px; border-radius:8px;">Add</button>
            </form>
        </div>

        {{-- Tasks Feed List --}}
        <div id="tasksFeedList" style="flex:1; overflow-y:auto;">
            @forelse ($tasks as $task)
                <div class="todo-task-item {{ $task->is_completed ? 'is-done' : '' }} {{ $selectedTask && $selectedTask->id == $task->id ? 'is-selected' : '' }}"
                     id="task-card-{{ $task->id }}"
                     data-task-id="{{ $task->id }}"
                     onclick="openTaskDetails('{{ $task->id }}')">
                    
                    {{-- Checkbox --}}
                    <div class="todo-check {{ $task->is_completed ? 'is-checked' : '' }}"
                         id="task-check-{{ $task->id }}"
                         onclick="event.stopPropagation(); toggleTaskCompletion('{{ $task->id }}')">
                        @if ($task->is_completed)
                            <i data-lucide="check" style="width:12px; height:12px;"></i>
                        @endif
                    </div>

                    {{-- Title & Badges --}}
                    <div style="flex:1; min-width:0;">
                        <div class="todo-task-title ff-truncate" id="task-title-{{ $task->id }}">{{ $task->title }}</div>

                        {{-- Task Metadata Badges --}}
                        <div class="task-badges-row" id="task-badges-{{ $task->id }}" style="display:flex; align-items:center; gap:6px; margin-top:4px; flex-wrap:wrap;">
                            @if ($task->collection && (!$activeCollection || $activeCollection->id != $task->collection->id))
                                <span class="chip chip-collection" style="background:rgba(99,102,241,0.08); color:{{ $task->collection->color }}; font-size:11px;">
                                    {{ $task->collection->name }}
                                </span>
                            @endif

                            @if ($task->progress['total'] > 0)
                                <span class="chip chip-substep" id="task-step-badge-{{ $task->id }}">
                                    <i data-lucide="check-circle-2" style="width:11px; height:11px;"></i>
                                    {{ $task->progress['completed'] }}/{{ $task->progress['total'] }}
                                </span>
                            @endif

                            @if ($task->due_date)
                                <span class="chip {{ $task->is_overdue ? 'chip-overdue' : 'chip-due' }}" id="task-due-badge-{{ $task->id }}">
                                    <i data-lucide="calendar" style="width:11px; height:11px;"></i>
                                    {{ $task->due_badge }}
                                </span>
                            @endif

                            @if ($task->repeat_interval !== 'none')
                                <span class="chip chip-repeat" id="task-repeat-badge-{{ $task->id }}">
                                    <i data-lucide="repeat" style="width:11px; height:11px;"></i>
                                    {{ ucfirst($task->repeat_interval) }}
                                </span>
                            @endif

                            @if (!empty($task->attachments))
                                <span class="chip chip-attachment" id="task-att-badge-{{ $task->id }}" style="background:rgba(6,182,212,0.1); color:#06b6d4;">
                                    <i data-lucide="paperclip" style="width:11px; height:11px;"></i>
                                    {{ count($task->attachments) }}
                                </span>
                            @endif

                            @if ($task->is_pinned_to_dashboard)
                                <span class="chip chip-pinned" id="task-pin-badge-{{ $task->id }}" title="Pinned to Dashboard">
                                    <i data-lucide="pin" style="width:10px; height:10px;"></i>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Star Toggle --}}
                    <button type="button" class="btn-star {{ $task->is_starred ? 'is-starred' : '' }}"
                            id="star-btn-{{ $task->id }}"
                            onclick="event.stopPropagation(); toggleTaskStar('{{ $task->id }}')"
                            title="{{ $task->is_starred ? 'Starred' : 'Mark as important' }}">
                        <i data-lucide="star" style="width:16px; height:16px;"></i>
                    </button>
                </div>
            @empty
                <div class="ff-empty" id="tasksFeedEmpty" style="padding:60px 20px; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:18px; display:inline-flex; align-items:center; justify-content:center; background:var(--ff-icon-bg, rgba(99,102,241,0.1)); color:var(--ff-accent, #6366f1); margin-bottom:14px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </span>
                    <div style="font-size:16px; font-weight:700; color:var(--ff-text);">No tasks found</div>
                    <p class="ff-hint" style="font-size:13px; margin-top:4px; max-width:320px;">All caught up! Add a new task using the input bar above.</p>
                </div>
            @endforelse
        </div>
    </main>


    {{-- ============================== 3. RIGHT DETAIL DRAWER ============================== --}}
    <aside class="todo-drawer" id="taskDetailDrawer" style="{{ $selectedTask ? 'display:flex;' : 'display:none;' }}">
        {{-- Drawer Header / Close --}}
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <span class="drawer-label">Task Details</span>
            <button type="button" onclick="closeTaskDrawer()" class="ff-hint" style="background:none; border:none; cursor:pointer;" title="Close drawer">
                <i data-lucide="x" style="width:16px; height:16px;"></i>
            </button>
        </div>

        {{-- Task Title Input --}}
        <div class="drawer-section">
            <input type="text" id="drawerTaskTitle" class="ff-input" value="{{ $selectedTask?->title }}" placeholder="Task title..." style="font-size:16px; font-weight:700;" onchange="saveTaskField('title', this.value)">
        </div>

        {{-- Checklist Sub-Steps --}}
        <div class="drawer-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span class="drawer-label">Checklist Steps (<span id="drawerStepProgress">{{ $selectedTask ? $selectedTask->progress['completed'] . '/' . $selectedTask->progress['total'] : '0/0' }}</span>)</span>
            </div>
            <div id="drawerStepsList" style="display:flex; flex-direction:column; gap:6px;">
                @if ($selectedTask)
                    @foreach ($selectedTask->steps as $step)
                        <div class="ff-row" style="gap:8px; font-size:13.5px;" id="step-row-{{ $step->id }}">
                            <input type="checkbox" {{ $step->is_completed ? 'checked' : '' }} onchange="toggleStepItem('{{ $step->id }}')" style="accent-color:#10b981; width:16px; height:16px; cursor:pointer;">
                            <span class="step-text" style="flex:1; {{ $step->is_completed ? 'text-decoration:line-through; color:var(--ff-muted);' : '' }}">{{ $step->title }}</span>
                            <button type="button" onclick="deleteStepItem('{{ $step->id }}')" class="ff-hint" style="background:none; border:none; cursor:pointer; color:#ef4444; padding:2px;">
                                <i data-lucide="x" style="width:13px; height:13px;"></i>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
            <form id="formAddStep" onsubmit="event.preventDefault(); addStepItem();" style="display:flex; gap:6px; margin-top:4px;">
                <input type="text" id="inpNewStep" class="ff-input" placeholder="+ Add a step..." style="font-size:13px; padding:6px 10px;">
                <button type="submit" class="ff-btn" style="padding:6px 10px; font-size:12px;">Add</button>
            </form>
        </div>

        {{-- Due Date Picker --}}
        <div class="drawer-section">
            <span class="drawer-label">Due Date</span>
            <input type="date" id="drawerDueDate" class="ff-input" value="{{ $selectedTask?->due_date?->toDateString() }}" onchange="saveTaskField('due_date', this.value)">
        </div>

        {{-- Repeat Schedule --}}
        <div class="drawer-section">
            <span class="drawer-label">Repeat Schedule</span>
            <select id="drawerRepeatSchedule" class="ff-select" onchange="saveTaskField('repeat_interval', this.value)">
                <option value="none" {{ ($selectedTask?->repeat_interval ?? 'none') === 'none' ? 'selected' : '' }}>Never (No Repeat)</option>
                <option value="daily" {{ ($selectedTask?->repeat_interval ?? '') === 'daily' ? 'selected' : '' }}>📅 Daily</option>
                <option value="weekdays" {{ ($selectedTask?->repeat_interval ?? '') === 'weekdays' ? 'selected' : '' }}>🏢 Weekdays (Mon - Fri)</option>
                <option value="weekly" {{ ($selectedTask?->repeat_interval ?? '') === 'weekly' ? 'selected' : '' }}>🔄 Weekly</option>
                <option value="monthly" {{ ($selectedTask?->repeat_interval ?? '') === 'monthly' ? 'selected' : '' }}>🗓️ Monthly</option>
                <option value="yearly" {{ ($selectedTask?->repeat_interval ?? '') === 'yearly' ? 'selected' : '' }}>🎉 Yearly</option>
                <option value="custom" {{ ($selectedTask?->repeat_interval ?? '') === 'custom' ? 'selected' : '' }}>⚙️ Custom</option>
            </select>
        </div>

        {{-- Dashboard Pin Toggle --}}
        <div class="drawer-section" style="padding:10px 12px; background:var(--ff-bg-2, #f8fafc); border-radius:10px;">
            <label style="display:flex; align-items:center; justify-content:space-between; cursor:pointer; font-size:13px; font-weight:600;">
                <span style="display:flex; align-items:center; gap:6px;">
                    <i data-lucide="pin" style="width:14px; height:14px; color:#a855f7;"></i>
                    Show on Dashboard
                </span>
                <input type="checkbox" id="drawerPinToggle" {{ $selectedTask?->is_pinned_to_dashboard ? 'checked' : '' }} onchange="saveTaskField('is_pinned_to_dashboard', this.checked ? 1 : 0)" style="accent-color:#a855f7; width:16px; height:16px;">
            </label>
            <div class="ff-hint" style="font-size:11.5px; margin-top:2px;">Shows in your dashboard widget until completed.</div>
        </div>

        {{-- File Attachments --}}
        <div class="drawer-section">
            <span class="drawer-label">File Attachments (Saved to Files with tag 'todo')</span>
            <div id="drawerAttachmentsList" style="display:flex; flex-direction:column; gap:6px;">
                @if ($selectedTask && !empty($selectedTask->attachments))
                    @foreach ($selectedTask->attachments as $idx => $att)
                        <div class="ff-row" style="gap:8px; padding:6px 10px; background:var(--ff-bg-2, #f8fafc); border-radius:8px; font-size:12.5px;" id="drawer-att-{{ $idx }}">
                            <i data-lucide="paperclip" style="width:13px; height:13px; color:var(--ff-accent);"></i>
                            <a href="{{ $att['url'] }}" target="_blank" class="ff-truncate" style="flex:1; color:var(--ff-text); font-weight:500;">{{ $att['name'] }}</a>
                            <span class="ff-hint">{{ $att['formatted_size'] ?? '' }}</span>
                            <button type="button" onclick="deleteTaskAttachment('{{ $idx }}')" class="ff-hint" style="background:none; border:none; cursor:pointer; color:#ef4444;">
                                <i data-lucide="trash-2" style="width:12px; height:12px;"></i>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>

            <form id="formUploadAttachment" enctype="multipart/form-data" style="margin-top:6px;">
                <label class="ff-btn" style="width:100%; justify-content:center; gap:6px; font-size:12.5px; cursor:pointer;">
                    <i data-lucide="upload" style="width:14px; height:14px;"></i> Add File Attachment
                    <input type="file" id="inpAttachmentFile" style="display:none;" onchange="uploadTaskAttachment(this)">
                </label>
            </form>
        </div>

        {{-- Notes --}}
        <div class="drawer-section">
            <span class="drawer-label">Notes &amp; Details</span>
            <textarea id="drawerTaskNotes" class="ff-input" rows="4" placeholder="Add rich notes or details here..." style="font-size:13px; resize:vertical;" onchange="saveTaskField('notes', this.value)">{{ $selectedTask?->notes }}</textarea>
        </div>

        {{-- Footer Delete --}}
        <div style="margin-top:auto; padding-top:14px; border-top:1px solid var(--ff-border, #e2e8f0); display:flex; justify-content:space-between; align-items:center;">
            <span class="ff-hint" id="drawerCreatedDate" style="font-size:11.5px;">{{ $selectedTask ? 'Created ' . $selectedTask->created_at->format('M j, Y') : '' }}</span>
            <button type="button" onclick="deleteCurrentTask()" class="ff-btn is-danger" style="padding:6px 12px; font-size:12px; gap:4px;">
                <i data-lucide="trash-2" style="width:13px; height:13px;"></i> Delete Task
            </button>
        </div>
    </aside>

</div>

{{-- Collection Modal --}}
<div id="modalCollection" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.65); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:16px;">
    <div class="ff-form-card" style="max-width:440px; width:100%; box-shadow:0 20px 40px rgba(0,0,0,0.4);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
            <div class="ff-modal-title" id="collectionModalTitle">Create Collection</div>
            <button type="button" onclick="closeCollectionModal()" class="ff-hint" style="background:none; border:none; cursor:pointer;">
                <i data-lucide="x" style="width:16px; height:16px;"></i>
            </button>
        </div>

        <form id="formCollection" action="{{ route('panel.todos.collections.store') }}" method="POST" enctype="multipart/form-data" class="ff-stack-sm">
            @csrf
            <input type="hidden" id="colEditId" name="_edit_id" value="">

            <div class="ff-field">
                <label class="ff-label" for="colName">Collection Name</label>
                <input type="text" id="colName" name="name" class="ff-input" placeholder="e.g. Project Alpha, Work Sprint" required>
            </div>

            <div class="ff-split-even">
                <div class="ff-field">
                    <label class="ff-label" for="colColor">Color Theme</label>
                    <input type="color" id="colColor" name="color" value="#6366f1" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--ff-border); cursor:pointer; background:transparent;">
                </div>
                <div class="ff-field">
                    <label class="ff-label" for="colIcon">Icon</label>
                    <select id="colIcon" name="icon" class="ff-select">
                        <option value="list-todo">📋 Checklist</option>
                        <option value="briefcase">💼 Work</option>
                        <option value="user">👤 Personal</option>
                        <option value="code">💻 Development</option>
                        <option value="shopping-cart">🛒 Shopping</option>
                        <option value="rocket">🚀 Launch</option>
                    </select>
                </div>
            </div>

            <div class="ff-field">
                <label class="ff-label" for="colCover">Cover Image / Wallpaper (Consumes Drive Storage)</label>
                <input type="file" id="colCover" name="cover" class="ff-input" accept="image/*">
            </div>

            @if ($vaultUnlocked)
                <div class="ff-toggle-row">
                    <div>
                        <div class="ff-toggle-label">🔒 Secret Vault Collection</div>
                        <div class="ff-toggle-sub">Hidden from public view behind your vault PIN</div>
                    </div>
                    <label class="ff-switch">
                        <input type="checkbox" id="colIsHidden" name="is_hidden" value="1">
                    </label>
                </div>
            @endif

            <div style="display:flex; gap:8px; margin-top:14px;">
                <button type="button" onclick="closeCollectionModal()" class="ff-btn" style="flex:1;">Cancel</button>
                <button type="submit" class="ff-btn ff-btn-primary" style="flex:1;">Save Collection</button>
            </div>
        </form>
    </div>
</div>

<script>
    let activeTaskId = "{{ $selectedTask?->id }}";
    const currentStatusFilter = "{{ $status }}";
    const currentListFilter = "{{ $filter }}";

    // 1. Submit Quick Task via Safe AJAX
    async function submitQuickTask() {
        const inp = document.getElementById('quickTaskTitle');
        const title = inp.value.trim();
        if (!title) return;

        const payload = {
            title: title,
            todo_collection_id: document.getElementById('quickTaskColId')?.value || null,
            is_starred: document.getElementById('quickTaskStarred')?.value === '1',
            is_pinned_to_dashboard: document.getElementById('quickTaskPinned')?.value === '1',
            due_date: document.getElementById('quickTaskDueDate')?.value || null,
        };

        try {
            const res = await fetch("{{ route('panel.todos.tasks.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.ok && data.task) {
                inp.value = '';
                // Hide empty state if present
                const emptyEl = document.getElementById('tasksFeedEmpty');
                if (emptyEl) emptyEl.style.display = 'none';

                // Prepend new task card into feed
                const feed = document.getElementById('tasksFeedList');
                if (feed) {
                    const temp = document.createElement('div');
                    temp.innerHTML = renderTaskCardHtml(data.task);
                    const newEl = temp.firstElementChild;
                    feed.insertBefore(newEl, feed.firstChild);
                }

                if (window.lucide) window.lucide.createIcons();
                if (window.ff?.toast) window.ff.toast('Task added successfully', 'success', 2000);
            }
        } catch (err) {
            console.error(err);
            if (window.ff?.toast) window.ff.toast('Failed to add task', 'error');
        }
    }

    // 2. Open Task Details in Drawer via AJAX
    async function openTaskDetails(taskId) {
        activeTaskId = taskId;
        
        // Highlight active card in feed
        document.querySelectorAll('.todo-task-item').forEach(el => el.classList.remove('is-selected'));
        const card = document.getElementById(`task-card-${taskId}`);
        if (card) card.classList.add('is-selected');

        const drawer = document.getElementById('taskDetailDrawer');
        if (drawer) drawer.style.display = 'flex';

        try {
            const res = await fetch(`/panel/todos/tasks/${taskId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.ok && data.task) {
                populateDrawer(data.task);
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 3. Populate Task Drawer Elements
    function populateDrawer(task) {
        document.getElementById('drawerTaskTitle').value = task.title || '';
        document.getElementById('drawerDueDate').value = task.due_date || '';
        document.getElementById('drawerRepeatSchedule').value = task.repeat_interval || 'none';
        document.getElementById('drawerPinToggle').checked = !!task.is_pinned_to_dashboard;
        document.getElementById('drawerTaskNotes').value = task.notes || '';
        document.getElementById('drawerCreatedDate').textContent = task.created_at_formatted ? `Created ${task.created_at_formatted}` : '';
        
        // Steps progress & list
        const prog = task.progress || { completed: 0, total: 0 };
        document.getElementById('drawerStepProgress').textContent = `${prog.completed}/${prog.total}`;
        
        const stepsList = document.getElementById('drawerStepsList');
        if (stepsList) {
            stepsList.innerHTML = '';
            (task.steps || []).forEach(step => {
                const sRow = document.createElement('div');
                sRow.className = 'ff-row';
                sRow.id = `step-row-${step.id}`;
                sRow.style.cssText = 'gap:8px; font-size:13.5px;';
                sRow.innerHTML = `
                    <input type="checkbox" ${step.is_completed ? 'checked' : ''} onchange="toggleStepItem('${step.id}')" style="accent-color:#10b981; width:16px; height:16px; cursor:pointer;">
                    <span class="step-text" style="flex:1; ${step.is_completed ? 'text-decoration:line-through; color:var(--ff-muted);' : ''}">${escapeHtml(step.title)}</span>
                    <button type="button" onclick="deleteStepItem('${step.id}')" class="ff-hint" style="background:none; border:none; cursor:pointer; color:#ef4444; padding:2px;">
                        <i data-lucide="x" style="width:13px; height:13px;"></i>
                    </button>
                `;
                stepsList.appendChild(sRow);
            });
        }

        // Attachments list
        const attList = document.getElementById('drawerAttachmentsList');
        if (attList) {
            attList.innerHTML = '';
            (task.attachments || []).forEach((att, idx) => {
                const aRow = document.createElement('div');
                aRow.className = 'ff-row';
                aRow.id = `drawer-att-${idx}`;
                aRow.style.cssText = 'gap:8px; padding:6px 10px; background:var(--ff-bg-2, #f8fafc); border-radius:8px; font-size:12.5px;';
                aRow.innerHTML = `
                    <i data-lucide="paperclip" style="width:13px; height:13px; color:var(--ff-accent);"></i>
                    <a href="${att.url}" target="_blank" class="ff-truncate" style="flex:1; color:var(--ff-text); font-weight:500;">${escapeHtml(att.name)}</a>
                    <span class="ff-hint">${att.formatted_size || ''}</span>
                    <button type="button" onclick="deleteTaskAttachment('${idx}')" class="ff-hint" style="background:none; border:none; cursor:pointer; color:#ef4444;">
                        <i data-lucide="trash-2" style="width:12px; height:12px;"></i>
                    </button>
                `;
                attList.appendChild(aRow);
            });
        }

        if (window.lucide) window.lucide.createIcons();
    }

    function closeTaskDrawer() {
        const drawer = document.getElementById('taskDetailDrawer');
        if (drawer) drawer.style.display = 'none';
        document.querySelectorAll('.todo-task-item').forEach(el => el.classList.remove('is-selected'));
        activeTaskId = null;
    }

    // 4. Toggle Task Completion via Safe AJAX
    async function toggleTaskCompletion(taskId) {
        const card = document.getElementById(`task-card-${taskId}`);
        const checkEl = document.getElementById(`task-check-${taskId}`);
        
        let isNowDone = false;
        if (card) {
            isNowDone = !card.classList.contains('is-done');
            card.classList.toggle('is-done', isNowDone);
        }
        if (checkEl) {
            checkEl.classList.toggle('is-checked', isNowDone);
            checkEl.innerHTML = isNowDone ? '<i data-lucide="check" style="width:12px; height:12px;"></i>' : '';
            if (window.lucide) window.lucide.createIcons();
        }

        try {
            const res = await fetch(`/panel/todos/tasks/${taskId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.ok) {
                if (data.rolled_over && window.ff?.toast) {
                    window.ff.toast(`Completed! Repeating task advanced to ${data.due_date}`, 'success');
                } else if (window.ff?.toast) {
                    window.ff.toast(data.is_completed ? 'Task marked completed' : 'Task marked active', 'info', 1500);
                }

                // If current view filters only active tasks and this task was completed, fade it out
                if (currentStatusFilter === 'active' && data.is_completed && card) {
                    setTimeout(() => {
                        card.style.transition = 'all 0.3s ease';
                        card.style.transform = 'translateX(20px)';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            checkEmptyFeed();
                        }, 300);
                    }, 350);
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 5. Toggle Task Star via Safe AJAX
    async function toggleTaskStar(taskId) {
        const btn = document.getElementById(`star-btn-${taskId}`);
        let isStarred = false;
        if (btn) {
            isStarred = !btn.classList.contains('is-starred');
            btn.classList.toggle('is-starred', isStarred);
        }

        try {
            const res = await fetch(`/panel/todos/tasks/${taskId}/star`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.ok) {
                if (currentListFilter === 'important' && !data.is_starred) {
                    const card = document.getElementById(`task-card-${taskId}`);
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            checkEmptyFeed();
                        }, 300);
                    }
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 6. Live Save Task Fields via Safe AJAX
    async function saveTaskField(field, value) {
        if (!activeTaskId) return;
        const payload = {};
        payload[field] = value;

        try {
            const res = await fetch(`/panel/todos/tasks/${activeTaskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.ok) {
                // Sync feed card live
                if (field === 'title') {
                    const titleEl = document.getElementById(`task-title-${activeTaskId}`);
                    if (titleEl) titleEl.textContent = value;
                }
                if (window.ff?.toast) window.ff.toast('Changes saved', 'success', 1200);
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 7. Add Sub-Step via Safe AJAX
    async function addStepItem() {
        const inp = document.getElementById('inpNewStep');
        const title = inp.value.trim();
        if (!title || !activeTaskId) return;

        try {
            const res = await fetch(`/panel/todos/tasks/${activeTaskId}/steps`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: title })
            });
            const data = await res.json();
            if (data.ok && data.step) {
                inp.value = '';
                const stepsList = document.getElementById('drawerStepsList');
                if (stepsList) {
                    const sRow = document.createElement('div');
                    sRow.className = 'ff-row';
                    sRow.id = `step-row-${data.step.id}`;
                    sRow.style.cssText = 'gap:8px; font-size:13.5px;';
                    sRow.innerHTML = `
                        <input type="checkbox" onchange="toggleStepItem('${data.step.id}')" style="accent-color:#10b981; width:16px; height:16px; cursor:pointer;">
                        <span class="step-text" style="flex:1;">${escapeHtml(data.step.title)}</span>
                        <button type="button" onclick="deleteStepItem('${data.step.id}')" class="ff-hint" style="background:none; border:none; cursor:pointer; color:#ef4444; padding:2px;">
                            <i data-lucide="x" style="width:13px; height:13px;"></i>
                        </button>
                    `;
                    stepsList.appendChild(sRow);
                }
                if (data.progress) {
                    document.getElementById('drawerStepProgress').textContent = `${data.progress.completed}/${data.progress.total}`;
                    syncCardStepBadge(activeTaskId, data.progress);
                }
                if (window.lucide) window.lucide.createIcons();
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 8. Toggle Sub-Step via Safe AJAX
    async function toggleStepItem(stepId) {
        try {
            const res = await fetch(`/panel/todos/steps/${stepId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.ok) {
                const sRow = document.getElementById(`step-row-${stepId}`);
                if (sRow) {
                    const span = sRow.querySelector('.step-text');
                    if (span) {
                        span.style.textDecoration = data.step?.is_completed ? 'line-through' : 'none';
                        span.style.color = data.step?.is_completed ? 'var(--ff-muted)' : '';
                    }
                }
                if (data.progress) {
                    document.getElementById('drawerStepProgress').textContent = `${data.progress.completed}/${data.progress.total}`;
                    syncCardStepBadge(activeTaskId, data.progress);
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 9. Delete Sub-Step via Safe AJAX
    async function deleteStepItem(stepId) {
        try {
            const res = await fetch(`/panel/todos/steps/${stepId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.ok) {
                document.getElementById(`step-row-${stepId}`)?.remove();
                if (data.progress) {
                    document.getElementById('drawerStepProgress').textContent = `${data.progress.completed}/${data.progress.total}`;
                    syncCardStepBadge(activeTaskId, data.progress);
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 10. Upload Task Attachment via Safe AJAX
    async function uploadTaskAttachment(input) {
        if (!input.files || !input.files[0] || !activeTaskId) return;
        const formData = new FormData();
        formData.append('file', input.files[0]);

        if (window.ff?.toast) window.ff.toast('Uploading attachment...', 'info');

        try {
            const res = await fetch(`/panel/todos/tasks/${activeTaskId}/attachments`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            if (data.ok) {
                input.value = '';
                // Refresh drawer attachments
                const taskRes = await fetch(`/panel/todos/tasks/${activeTaskId}`, { headers: { 'Accept': 'application/json' } });
                const taskData = await taskRes.json();
                if (taskData.ok && taskData.task) {
                    populateDrawer(taskData.task);
                }
                if (window.ff?.toast) window.ff.toast('File attached to task and saved to Drive!', 'success');
            } else {
                alert(data.message || 'Upload failed');
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 11. Delete Task Attachment via Safe AJAX
    async function deleteTaskAttachment(index) {
        if (!confirm('Remove this attachment and delete it from drive?') || !activeTaskId) return;
        try {
            const res = await fetch(`/panel/todos/tasks/${activeTaskId}/attachments/${index}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.ok) {
                document.getElementById(`drawer-att-${index}`)?.remove();
                if (window.ff?.toast) window.ff.toast('Attachment removed', 'info');
            }
        } catch (err) {
            console.error(err);
        }
    }

    // 12. Delete Task via Safe AJAX
    async function deleteCurrentTask() {
        if (!confirm('Are you sure you want to delete this task?') || !activeTaskId) return;
        const deletingId = activeTaskId;
        try {
            const res = await fetch(`/panel/todos/tasks/${deletingId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.ok) {
                closeTaskDrawer();
                const card = document.getElementById(`task-card-${deletingId}`);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.transform = 'scale(0.95)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        checkEmptyFeed();
                    }, 300);
                }
                if (window.ff?.toast) window.ff.toast('Task deleted', 'success');
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Helper: HTML card template generator
    function renderTaskCardHtml(task) {
        const isDone = !!task.is_completed;
        const isStarred = !!task.is_starred;
        const col = task.collection;
        const prog = task.progress || { total: 0, completed: 0 };
        const atts = task.attachments || [];

        return `
            <div class="todo-task-item ${isDone ? 'is-done' : ''}"
                 id="task-card-${task.id}"
                 data-task-id="${task.id}"
                 onclick="openTaskDetails('${task.id}')">
                <div class="todo-check ${isDone ? 'is-checked' : ''}"
                     id="task-check-${task.id}"
                     onclick="event.stopPropagation(); toggleTaskCompletion('${task.id}')">
                    ${isDone ? '<i data-lucide="check" style="width:12px; height:12px;"></i>' : ''}
                </div>
                <div style="flex:1; min-width:0;">
                    <div class="todo-task-title ff-truncate" id="task-title-${task.id}">${escapeHtml(task.title)}</div>
                    <div class="task-badges-row" id="task-badges-${task.id}" style="display:flex; align-items:center; gap:6px; margin-top:4px; flex-wrap:wrap;">
                        ${col ? `<span class="chip chip-collection" style="background:rgba(99,102,241,0.08); color:${col.color || '#6366f1'}; font-size:11px;">${escapeHtml(col.name)}</span>` : ''}
                        ${prog.total > 0 ? `<span class="chip chip-substep" id="task-step-badge-${task.id}"><i data-lucide="check-circle-2" style="width:11px; height:11px;"></i> ${prog.completed}/${prog.total}</span>` : ''}
                        ${task.due_date ? `<span class="chip chip-due" id="task-due-badge-${task.id}"><i data-lucide="calendar" style="width:11px; height:11px;"></i> ${escapeHtml(task.due_badge || task.due_date)}</span>` : ''}
                        ${task.repeat_interval && task.repeat_interval !== 'none' ? `<span class="chip chip-repeat" id="task-repeat-badge-${task.id}"><i data-lucide="repeat" style="width:11px; height:11px;"></i> ${escapeHtml(task.repeat_interval)}</span>` : ''}
                        ${atts.length > 0 ? `<span class="chip chip-attachment" id="task-att-badge-${task.id}" style="background:rgba(6,182,212,0.1); color:#06b6d4;"><i data-lucide="paperclip" style="width:11px; height:11px;"></i> ${atts.length}</span>` : ''}
                        ${task.is_pinned_to_dashboard ? `<span class="chip chip-pinned" id="task-pin-badge-${task.id}" title="Pinned to Dashboard"><i data-lucide="pin" style="width:10px; height:10px;"></i></span>` : ''}
                    </div>
                </div>
                <button type="button" class="btn-star ${isStarred ? 'is-starred' : ''}"
                        id="star-btn-${task.id}"
                        onclick="event.stopPropagation(); toggleTaskStar('${task.id}')"
                        title="${isStarred ? 'Starred' : 'Mark as important'}">
                    <i data-lucide="star" style="width:16px; height:16px;"></i>
                </button>
            </div>
        `;
    }

    function syncCardStepBadge(taskId, progress) {
        let badge = document.getElementById(`task-step-badge-${taskId}`);
        if (progress.total > 0) {
            if (!badge) {
                const badgesRow = document.getElementById(`task-badges-${taskId}`);
                if (badgesRow) {
                    badge = document.createElement('span');
                    badge.className = 'chip chip-substep';
                    badge.id = `task-step-badge-${taskId}`;
                    badgesRow.appendChild(badge);
                }
            }
            if (badge) {
                badge.innerHTML = `<i data-lucide="check-circle-2" style="width:11px; height:11px;"></i> ${progress.completed}/${progress.total}`;
                if (window.lucide) window.lucide.createIcons();
            }
        } else if (badge) {
            badge.remove();
        }
    }

    function checkEmptyFeed() {
        const feed = document.getElementById('tasksFeedList');
        if (feed && feed.querySelectorAll('.todo-task-item').length === 0) {
            let emptyEl = document.getElementById('tasksFeedEmpty');
            if (!emptyEl) {
                emptyEl = document.createElement('div');
                emptyEl.className = 'ff-empty';
                emptyEl.id = 'tasksFeedEmpty';
                emptyEl.style.cssText = 'padding:60px 20px; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center;';
                emptyEl.innerHTML = `
                    <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:18px; display:inline-flex; align-items:center; justify-content:center; background:var(--ff-icon-bg, rgba(99,102,241,0.1)); color:var(--ff-accent, #6366f1); margin-bottom:14px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </span>
                    <div style="font-size:16px; font-weight:700; color:var(--ff-text);">No tasks found</div>
                    <p class="ff-hint" style="font-size:13px; margin-top:4px; max-width:320px;">All caught up! Add a new task using the input bar above.</p>
                `;
                feed.appendChild(emptyEl);
            } else {
                emptyEl.style.display = 'flex';
            }
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openCollectionModal() {
        document.getElementById('colEditId').value = '';
        document.getElementById('collectionModalTitle').textContent = 'Create Collection';
        document.getElementById('colName').value = '';
        document.getElementById('modalCollection').style.display = 'flex';
    }

    function editActiveCollection() {
        @if ($activeCollection)
            document.getElementById('colEditId').value = "{{ $activeCollection->id }}";
            document.getElementById('collectionModalTitle').textContent = 'Edit Collection';
            document.getElementById('colName').value = "{{ $activeCollection->name }}";
            document.getElementById('colColor').value = "{{ $activeCollection->color }}";
            document.getElementById('formCollection').action = "/panel/todos/collections/{{ $activeCollection->id }}";
            document.getElementById('modalCollection').style.display = 'flex';
        @endif
    }

    function closeCollectionModal() {
        document.getElementById('modalCollection').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) window.lucide.createIcons();
    });
</script>
@endsection
