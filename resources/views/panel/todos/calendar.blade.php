@extends('layout.backend')
@push('title', 'Tasks Calendar')

@section('css')
<style>
    .cal-container {
        background: var(--ff-card, #ffffff);
        border: 1px solid var(--ff-border, #e2e8f0);
        border-radius: 18px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }
    .cal-header-day {
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--ff-muted, #94a3b8);
        padding: 8px 0;
    }
    .cal-day-cell {
        min-height: 110px;
        background: var(--ff-bg-2, #f8fafc);
        border: 1px solid var(--ff-border, #e2e8f0);
        border-radius: 12px;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        transition: border-color 0.15s ease, background 0.15s ease;
        cursor: pointer;
    }
    .cal-day-cell:hover {
        border-color: var(--ff-accent, #6366f1);
        background: var(--ff-card, #ffffff);
    }
    .cal-day-cell.is-today {
        border-color: var(--ff-accent, #6366f1);
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }
    .cal-day-cell.is-other-month {
        opacity: 0.4;
    }
    .cal-day-num {
        font-size: 13px;
        font-weight: 700;
        color: var(--ff-text, #0f172a);
        align-self: flex-start;
        display: inline-flex;
        width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .cal-day-cell.is-today .cal-day-num {
        background: var(--ff-accent, #6366f1);
        color: #ffffff;
    }

    .cal-task-chip {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        background: rgba(99, 102, 241, 0.12);
        color: var(--ff-text, #0f172a);
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        transition: transform 0.1s ease;
    }
    .cal-task-chip:hover {
        transform: translateY(-1px);
        filter: brightness(1.08);
    }
    .cal-task-chip.is-done {
        text-decoration: line-through;
        opacity: 0.6;
    }
</style>
@endsection

@section('content')
<div class="cal-container">

    {{-- Calendar Navigation Bar --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('panel.todos.index') }}" class="ff-btn" style="padding:8px 12px; font-size:13px; gap:6px;">
                <i data-lucide="arrow-left" style="width:14px; height:14px;"></i> Workspace
            </a>
            <h1 style="font-family:'Outfit',sans-serif; font-size:24px; font-weight:800; margin:0; letter-spacing:-0.5px;">
                {{ $currentDate->format('F Y') }}
            </h1>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            @php
                $prevMonth = $currentDate->copy()->subMonth();
                $nextMonth = $currentDate->copy()->addMonth();
            @endphp
            <a href="{{ route('panel.todos.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="ff-btn" style="padding:8px 12px;">
                <i data-lucide="chevron-left" style="width:16px; height:16px;"></i>
            </a>
            <a href="{{ route('panel.todos.calendar', ['month' => now()->month, 'year' => now()->year]) }}" class="ff-btn" style="padding:8px 16px; font-size:13px; font-weight:600;">
                Today
            </a>
            <a href="{{ route('panel.todos.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="ff-btn" style="padding:8px 12px;">
                <i data-lucide="chevron-right" style="width:16px; height:16px;"></i>
            </a>
        </div>
    </div>

    {{-- Calendar Days Grid --}}
    <div class="cal-grid">
        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
            <div class="cal-header-day">{{ $dayName }}</div>
        @endforeach

        @php
            $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)
            $daysInMonth = $currentDate->daysInMonth;
            $startCalendarDate = $startOfMonth->copy()->subDays($startDayOfWeek);
            $totalCells = 35; // 5 weeks or 42 for 6 weeks
            if (($startDayOfWeek + $daysInMonth) > 35) {
                $totalCells = 42;
            }
        @endphp

        @for ($i = 0; $i < $totalCells; $i++)
            @php
                $cellDate = $startCalendarDate->copy()->addDays($i);
                $dateKey = $cellDate->toDateString();
                $isToday = $cellDate->isToday();
                $isOtherMonth = $cellDate->month !== $month;
                $dayTasks = $tasksByDate[$dateKey] ?? [];
            @endphp

            <div class="cal-day-cell {{ $isToday ? 'is-today' : '' }} {{ $isOtherMonth ? 'is-other-month' : '' }}"
                 onclick="openQuickAddForDate('{{ $dateKey }}')">
                <span class="cal-day-num">{{ $cellDate->day }}</span>

                {{-- Task Chips for Day --}}
                <div style="display:flex; flex-direction:column; gap:3px; overflow:hidden;">
                    @foreach ($dayTasks as $t)
                        <a href="{{ route('panel.todos.index', ['task' => $t->id, 'filter' => 'planned']) }}"
                           class="cal-task-chip {{ $t->is_completed ? 'is-done' : '' }}"
                           style="border-left: 2px solid {{ $t->collection?->color ?? '#6366f1' }};"
                           onclick="event.stopPropagation();">
                            @if ($t->is_starred)
                                <i data-lucide="star" style="width:10px; height:10px; color:#f59e0b; flex-shrink:0;"></i>
                            @endif
                            <span class="ff-truncate">{{ $t->title }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endfor
    </div>

</div>

{{-- Quick Task Add Modal on Date Click --}}
<div id="modalDateTask" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.65); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:16px;">
    <div class="ff-form-card" style="max-width:400px; width:100%;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div class="ff-modal-title">Schedule Task on <span id="modalDateLabel" style="color:var(--ff-accent);"></span></div>
            <button type="button" onclick="document.getElementById('modalDateTask').style.display = 'none'" class="ff-hint" style="background:none; border:none; cursor:pointer;">
                <i data-lucide="x" style="width:16px; height:16px;"></i>
            </button>
        </div>

        <form action="{{ route('panel.todos.tasks.store') }}" method="POST" class="ff-stack-sm">
            @csrf
            <input type="hidden" id="modalDueDateInp" name="due_date" value="">

            <div class="ff-field">
                <label class="ff-label" for="modalTaskTitle">Task Title</label>
                <input type="text" id="modalTaskTitle" name="title" class="ff-input" placeholder="What needs to be done?" required autofocus>
            </div>

            <div class="ff-field">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <label class="ff-label" for="modalTaskCol" style="margin:0;">Collection / List</label>
                    <a href="{{ route('panel.categories.create', ['type' => 'tasks']) }}" target="_blank" style="font-size:11.5px; color:var(--ff-accent); text-decoration:none; font-weight:600;">+ New List in Categories</a>
                </div>
                <select id="modalTaskCol" name="todo_collection_id" class="ff-select">
                    <option value="">Uncategorized</option>
                    @foreach ($collections as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:8px; margin-top:10px;">
                <button type="button" onclick="document.getElementById('modalDateTask').style.display = 'none'" class="ff-btn" style="flex:1;">Cancel</button>
                <button type="submit" class="ff-btn ff-btn-primary" style="flex:1;">Add to Calendar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openQuickAddForDate(dateStr) {
        document.getElementById('modalDueDateInp').value = dateStr;
        document.getElementById('modalDateLabel').textContent = dateStr;
        document.getElementById('modalTaskTitle').value = '';
        document.getElementById('modalDateTask').style.display = 'flex';
        setTimeout(() => document.getElementById('modalTaskTitle').focus(), 50);
    }
</script>
@endsection
