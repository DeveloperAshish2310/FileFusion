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
     * Get the user that owns the password credential.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
