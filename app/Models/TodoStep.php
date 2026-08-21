<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoStep extends Model
{
    use HasFactory;

    protected $table = 'todo_steps';

    protected $fillable = [
        'todo_task_id',
        'title',
        'is_completed',
        'sort_order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TodoTask::class, 'todo_task_id');
    }
}
