<?php

namespace App\Models;

use App\Support\HtmlSanitize;
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

    protected static function booted(): void
    {
        // See Post::booted(): `content` is rendered raw on the public /p route.
        static::saving(function (Page $page): void {
            HtmlSanitize::sanitiseRawTranslatable($page, 'content');
        });
    }
}
