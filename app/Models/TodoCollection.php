<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TodoCollection extends Model
{
    use HasFactory;

    protected $table = 'todo_collections';

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'icon',
        'cover_image',
        'cover_file_id',
        'is_hidden',
        'sort_order',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TodoTask::class, 'todo_collection_id')->orderBy('sort_order')->orderByDesc('id');
    }

    public function pendingTasks(): HasMany
    {
        return $this->hasMany(TodoTask::class, 'todo_collection_id')->where('is_completed', false)->orderBy('sort_order')->orderByDesc('id');
    }

    public function completedTasks(): HasMany
    {
        return $this->hasMany(TodoTask::class, 'todo_collection_id')->where('is_completed', true)->orderByDesc('completed_at');
    }

    public function coverFile(): BelongsTo
    {
        return $this->belongsTo(FileModal::class, 'cover_file_id');
    }
}
