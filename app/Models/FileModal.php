<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileModal extends Model
{
    use SoftDeletes;
    public $table = 'files';
    public $fillable = [
        "name",
        "path",
        "size",
        "type",
        "created_at",
        "updated_at",
        "user_id",
        "shared_id",
        "thumbnail",
        "status",
        "is_hidden",
        "is_starred",
        "is_trashed",
        "is_encrypted",
        "is_shared",
        "deleted_at"
    ];

    /**
     * Interact with the file's name attribute (AES-256 encrypted in database).
     */
    protected function name(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }


    /**
     * Owner user relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * FileShares relationship.
     */
    public function shares()
    {
        return $this->hasMany(FileShare::class, 'file_id');
    }

    /**
     * Check if file has active shares or is marked as shared.
     */
    public function isCurrentlyShared(): bool
    {
        if ((int)$this->is_shared === 1) {
            return true;
        }
        return $this->shares()->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->exists();
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo((string)$this->name, PATHINFO_EXTENSION));
    }

    public function getIsEditableAttribute(): bool
    {
        return isFileEditable($this->extension, $this->type);
    }
}


