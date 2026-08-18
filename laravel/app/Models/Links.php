<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Links extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'url',
        'description',
        'tags',
        'is_hidden',
        'is_trashed',
        'thumbnail',
        'is_starred',
        'is_new',
        'shared_id',
        'is_encrypted',
        'is_shared',
        'expiry_date',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'is_trashed' => 'boolean',
        'is_starred' => 'boolean',
        'is_new' => 'boolean',
        'is_encrypted' => 'boolean',
        'is_shared' => 'boolean',
        'expiry_date' => 'date',
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
     * Interact with the description attribute (AES-256 encrypted in database).
     */
    protected function description(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }

    /**
     * Interact with the tags attribute (AES-256 encrypted in database).
     */
    protected function tags(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
