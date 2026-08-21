<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class LinkShare extends Model
{
    use HasFactory;

    protected $table = 'link_shares';

    protected $fillable = [
        'link_id',
        'user_id',
        'share_token',
        'share_type',
        'recipient_user_id',
        'recipient_email',
        'password',
        'expires_at',
        'max_clicks',
        'click_count',
        'is_anonymous',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'max_clicks' => 'integer',
        'click_count' => 'integer',
    ];

    public function link()
    {
        return $this->belongsTo(Links::class, 'link_id');
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

    public function hasReachedClickLimit(): bool
    {
        return $this->max_clicks !== null && $this->click_count >= $this->max_clicks;
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
