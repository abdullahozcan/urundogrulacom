<?php

namespace App\Models;

use App\Support\HtmlSanitize;
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

    protected static function booted(): void
    {
        // `content` is rendered with `{!! !!}` on the public /blog route, so
        // it must never be able to carry script or event handlers. Sanitising
        // here (rather than in the form) covers every write path, including
        // seeders, tinker and direct model writes.
        static::saving(function (Post $post): void {
            HtmlSanitize::sanitiseRawTranslatable($post, 'content');
        });
    }
}
