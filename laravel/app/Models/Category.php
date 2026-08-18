<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = [
        'title',
        'description',
        'type',
        'thumbnail',
        'categories',
        'is_new',
        'is_hidden',
        'user_id'
    ];

    protected $casts = [
        'categories' => 'array',
        'is_new' => 'boolean',
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
     * Interact with the description attribute (AES-256 encrypted in database).
     */
    protected function description(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => \App\Helpers\Encryptor::decrypt($value),
            set: fn ($value) => \App\Helpers\Encryptor::encrypt($value),
        );
    }


    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getCategoriesStringAttribute()
    {
        return is_array($this->categories) ? implode(', ', $this->categories) : '';
    }

    // Mutators
    public function setCategoriesAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['categories'] = json_encode(
                array_map('trim', explode(',', $value))
            );
        } else {
            $this->attributes['categories'] = json_encode($value);
        }
    }
}
