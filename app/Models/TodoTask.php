<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TodoTask extends Model
{
    use HasFactory;

    protected $table = 'todo_tasks';

    protected $fillable = [
        'user_id',
        'todo_collection_id',
        'title',
        'notes',
        'is_completed',
        'completed_at',
        'is_starred',
        'is_pinned_to_dashboard',
        'is_hidden',
        'due_date',
        'remind_at',
        'last_notified_at',
        'is_notified',
        'repeat_interval',
        'repeat_custom_days',
        'attachments',
        'sort_order',
    ];

    protected $casts = [
        'notes' => 'encrypted',
        'is_completed' => 'boolean',
        'is_starred' => 'boolean',
        'is_pinned_to_dashboard' => 'boolean',
        'is_hidden' => 'boolean',
        'due_date' => 'datetime',
        'remind_at' => 'datetime',
        'last_notified_at' => 'datetime',
        'is_notified' => 'boolean',
        'completed_at' => 'datetime',
        'repeat_custom_days' => 'array',
        'attachments' => 'array',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'progress',
        'is_overdue',
        'due_badge',
        'has_due_time',
        'formatted_due_time',
        'due_date_formatted',
        'due_time_formatted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(TodoCollection::class, 'todo_collection_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(TodoStep::class, 'todo_task_id')->orderBy('sort_order')->orderBy('id');
    }

    public function getProgressAttribute(): array
    {
        $total = $this->steps()->count();
        $completed = $this->steps()->where('is_completed', true)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    public function getHasDueTimeAttribute(): bool
    {
        if (!$this->due_date) return false;
        return $this->due_date->format('H:i:s') !== '00:00:00';
    }

    public function getFormattedDueTimeAttribute(): ?string
    {
        if (!$this->due_date || !$this->has_due_time) return null;
        return $this->due_date->format('g:i A');
    }

    public function getDueDateFormattedAttribute(): string
    {
        return $this->due_date ? $this->due_date->format('Y-m-d') : '';
    }

    public function getDueTimeFormattedAttribute(): string
    {
        return ($this->has_due_time && $this->due_date) ? $this->due_date->format('H:i') : '';
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->is_completed || !$this->due_date) {
            return false;
        }

        if ($this->has_due_time) {
            return now()->isAfter($this->due_date);
        }

        return $this->due_date->isPast() && !$this->due_date->isToday();
    }

    public function getDueBadgeAttribute(): ?string
    {
        if (!$this->due_date) {
            return null;
        }

        $timeSuffix = $this->has_due_time ? ' at ' . $this->due_date->format('g:i A') : '';

        if ($this->due_date->isToday()) {
            return 'Today' . $timeSuffix;
        }

        if ($this->due_date->isTomorrow()) {
            return 'Tomorrow' . $timeSuffix;
        }

        if ($this->due_date->isYesterday()) {
            return 'Yesterday' . $timeSuffix;
        }

        return $this->due_date->format('M j') . $timeSuffix;
    }

    /**
     * Advance repeat interval to next scheduled date
     */
    public function advanceRecurrence(): void
    {
        if ($this->repeat_interval === 'none' || empty($this->repeat_interval)) {
            return;
        }

        $baseDate = $this->due_date ? Carbon::parse($this->due_date) : Carbon::today();

        switch ($this->repeat_interval) {
            case 'daily':
                $nextDate = $baseDate->addDay();
                break;
            case 'weekdays':
                $nextDate = $baseDate->addWeekday();
                break;
            case 'weekly':
                $nextDate = $baseDate->addWeek();
                break;
            case 'monthly':
                $nextDate = $baseDate->addMonth();
                break;
            case 'yearly':
                $nextDate = $baseDate->addYear();
                break;
            case 'custom':
                $nextDate = $baseDate->addDays(2);
                break;
            default:
                $nextDate = $baseDate->addDay();
                break;
        }

        $this->update([
            'is_completed' => false,
            'completed_at' => null,
            'due_date' => $nextDate->toDateString(),
        ]);

        // Uncheck all sub-steps for the next recurrence
        $this->steps()->update(['is_completed' => false]);
    }
}
