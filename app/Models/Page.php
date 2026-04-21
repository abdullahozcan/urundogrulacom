<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use \App\Traits\HasTranslations;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
    ];

    public array $translatable = ['title', 'slug', 'content'];

    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'content' => 'array',
        'is_published' => 'boolean',
    ];
}
