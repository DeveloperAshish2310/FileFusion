<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class FileShare extends Model
{
    use HasFactory;

    protected $table = 'file_shares';

    protected $fillable = [
        'file_id',
        'user_id',
        'share_token',
        'share_type',
        'recipient_user_id',
        'recipient_email',
        'password',
        'expires_at',
        'max_downloads',
        'download_count',
        'is_anonymous',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'max_downloads' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * File relation.
     */
    public function file()
    {
        return $this->belongsTo(FileModal::class, 'file_id');
    }

    /**
     * Owner user relation.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Recipient user relation for private shares.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Scope for active/valid shares.
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })->where(function ($q) {
            $q->whereNull('max_downloads')
              ->orWhereColumn('download_count', '<', 'max_downloads');
        });
    }

    /**
     * Check if the share has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Check if download limit has been reached.
     */
    public function hasReachedDownloadLimit(): bool
    {
        return $this->max_downloads !== null && $this->download_count >= $this->max_downloads;
    }

    /**
     * Check if the share is protected with a passcode.
     */
    public function isPasswordProtected(): bool
    {
        return !empty($this->password);
    }

    /**
     * Verify provided passcode.
     */
    public function verifyPasscode($passcode): bool
    {
        if (empty($this->password)) {
            return true;
        }
        return Hash::check($passcode, $this->password);
    }

    /**
     * Increment download count.
     */
    public function recordDownload()
    {
        $this->increment('download_count');
    }
}
