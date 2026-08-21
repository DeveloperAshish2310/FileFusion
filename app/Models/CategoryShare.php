<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CategoryShare extends Model
{
    use HasFactory;

    protected $table = 'category_shares';

    protected $fillable = [
        'category_id',
        'user_id',
        'share_token',
        'share_type',
        'recipient_user_id',
        'recipient_email',
        'password',
        'expires_at',
        'max_views',
        'view_count',
        'include_files',
        'include_links',
        'include_passwords',
        'is_anonymous',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'include_files' => 'boolean',
        'include_links' => 'boolean',
        'include_passwords' => 'boolean',
        'max_views' => 'integer',
        'view_count' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedViewLimit(): bool
    {
        return $this->max_views !== null && $this->view_count >= $this->max_views;
    }

    public function isPasswordProtected(): bool
    {
        return !empty($this->password);
    }

    public function verifyPassword(?string $passcode): bool
    {
        if (!$this->isPasswordProtected()) return true;
        if (empty($passcode)) return false;
        return Hash::check($passcode, $this->password);
    }
}
