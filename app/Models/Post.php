<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use \App\Traits\HasTranslations;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'is_published',
        'show_on_homepage',
    ];

    public array $translatable = ['title', 'slug', 'content'];

    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'content' => 'array',
        'is_published' => 'boolean',
        'show_on_homepage' => 'boolean',
    ];
}
