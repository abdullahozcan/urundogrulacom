<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Server-side HTML sanitisation for rich-text content.
 *
 * Filament's RichEditor is a Trix editor. Trix's restrictions are client side
 * only, so a crafted Livewire/Filament request can persist `<script>` or
 * `onerror=` straight into the JSON column. That content is then rendered with
 * `{!! !!}` on the public blog and page routes, which makes it stored XSS for
 * every visitor. Sanitising on the way in is the only reliable fix.
 *
 * `symfony/html-sanitizer` already ships as a transitive dependency, so this
 * adds no new package.
 */
final class HtmlSanitize
{
    /**
     * Trix output allowlist. Deliberately excludes `script`, `style`,
     * `iframe`, `object`, `embed`, `form` and every `on*` handler.
     */
    private static function config(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeMedias()
            ->allowRelativeLinks()
            ->allowAttribute('class', '*')
            ->allowAttribute('id', ['div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'])
            ->allowAttribute('align', ['div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'img'])
            ->allowAttribute('target', ['a'])
            ->allowAttribute('rel', ['a'])
            ->withMaxInputLength(-1);
    }

    private static ?HtmlSanitizer $sanitizer = null;

    /**
     * @param  array<string, string|null>|string|null  $value
     * @return array<string, string|null>|string|null
     */
    public static function translatable(array|string|null $value): array|string|null
    {
        if (is_array($value)) {
            return array_map(
                static fn ($item): ?string => self::html($item),
                $value
            );
        }

        return self::html($value);
    }

    public static function html(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        self::$sanitizer ??= new HtmlSanitizer(self::config());

        return self::$sanitizer->sanitize($html);
    }

    /**
     * Sanitise a translatable JSON attribute without going through the
     * model's magic accessors.
     *
     * `HasTranslations::getAttribute()` intercepts translatable keys and
     * returns the value for the *current locale* (a string). Reading
     * `$model->content` inside a `saving` hook therefore hands back a plain
     * string, which would then be written back over the whole `{tr, en}` map
     * and destroy the other locales. This works on the raw attribute instead.
     */
    public static function sanitiseRawTranslatable(Model $model, string $key): void
    {
        $raw = $model->getAttributes()[$key] ?? null;

        if ($raw === null || $raw === '') {
            return;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

        if (! is_array($decoded)) {
            // Already a scalar (or undecodable): sanitise it as plain HTML.
            $model->setAttribute($key, self::html(is_string($decoded) ? $decoded : null));

            return;
        }

        $model->setAttribute($key, self::translatable($decoded));
    }
}