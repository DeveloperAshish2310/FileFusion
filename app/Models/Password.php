<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Password extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'passwords';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'username',
        'url',
        'password',
        'notes',
        'auth_fields',
        'is_hidden',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    /**
     * Interact with the title attribute (AES-256 encrypted in database).
     */
    protected function title(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }

    /**
     * Interact with the username attribute (AES-256 encrypted in database).
     */
    protected function username(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }

    /**
     * Interact with the url attribute (AES-256 encrypted in database).
     */
    protected function url(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }

    /**
     * Interact with the notes attribute (AES-256 encrypted in database).
     */
    protected function notes(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }

    /**
     * Interact with the password attribute (AES-256 encrypted in database).
     */
    protected function password(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }

    /**
     * Interact with the auth_fields attribute (AES-256 encrypted in database).
     */
    protected function authFields(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') return [];
                $decrypted = \App\Helpers\Encryptor::decrypt($value);
                if (is_array($decrypted)) return $decrypted;
                if (is_string($decrypted) && (str_starts_with($decrypted, '[') || str_starts_with($decrypted, '{'))) {
                    $json = json_decode($decrypted, true);
                    return json_last_error() === JSON_ERROR_NONE ? $json : $decrypted;
                }
                return $decrypted ?: [];
            },
            set: function ($value) {
                if ($value === null || $value === '') return null;
                $toEncrypt = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;
                return \App\Helpers\Encryptor::encrypt($toEncrypt);
            },
        );
    }

    /**
     * Get the user that owns the password credential.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
