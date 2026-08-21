<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PasswordShare extends Model
{
    use HasFactory;

    protected $table = 'password_shares';

    protected $fillable = [
        'password_id',
        'user_id',
        'share_token',
        'share_type',
        'encrypted_payload',
        'recipient_user_id',
        'recipient_email',
        'password',
        'expires_at',
        'max_reveals',
        'reveal_count',
        'burn_after_reading',
        'is_anonymous',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'burn_after_reading' => 'boolean',
        'max_reveals' => 'integer',
        'reveal_count' => 'integer',
    ];

    public function credential()
    {
        return $this->belongsTo(Password::class, 'password_id');
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

    public function hasReachedRevealLimit(): bool
    {
        return $this->max_reveals !== null && $this->reveal_count >= $this->max_reveals;
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
