<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Support\SafeUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use App\Livewire\CheckProduct;
use Tests\TestCase;

class OutboundUrlSafetyTest extends TestCase
{
    use RefreshDatabase;

    /** Cloud metadata endpoints are the classic SSRF target. */
    public function test_cloud_metadata_endpoints_are_blocked(): void
    {
        $this->assertFalse(SafeUrl::isSafe('http://169.254.169.254/latest/meta-data/'));
        $this->assertFalse(SafeUrl::isSafe('http://metadata.google.internal/computeMetadata/v1/'));
    }

    public function test_loopback_and_private_ranges_are_blocked(): void
    {
        $this->assertFalse(SafeUrl::isSafe('http://127.0.0.1:6379/'));
        $this->assertFalse(SafeUrl::isSafe('http://localhost/admin'));
        $this->assertFalse(SafeUrl::isSafe('http://10.0.0.5/'));
        $this->assertFalse(SafeUrl::isSafe('http://192.168.1.1/'));
        $this->assertFalse(SafeUrl::isSafe('http://172.16.0.1/'));
        $this->assertFalse(SafeUrl::isSafe('http://[::1]/'));
        $this->assertFalse(SafeUrl::isSafe('http://redis.internal/'));
        $this->assertFalse(SafeUrl::isSafe('http://printer.local/'));
    }

    /** Integer and hex encodings of 127.0.0.1 must not slip through. */
    public function test_obfuscated_loopback_is_blocked(): void
    {
        $this->assertFalse(SafeUrl::isSafe('http://2130706433/'));
        $this->assertFalse(SafeUrl::isSafe('http://0x7f000001/'));
        $this->assertFalse(SafeUrl::isSafe('http://0177.0.0.1/'));
        $this->assertFalse(SafeUrl::isSafe('http://127.1/'));
    }

    public function test_non_http_schemes_are_blocked(): void
    {
        $this->assertFalse(SafeUrl::isSafe('file:///etc/passwd'));
        $this->assertFalse(SafeUrl::isSafe('gopher://127.0.0.1:6379/_POST'));
        $this->assertFalse(SafeUrl::isSafe('javascript:alert(1)'));
        $this->assertFalse(SafeUrl::isSafe('data:text/html,<script>alert(1)</script>'));
    }

    public function test_public_http_urls_are_allowed(): void
    {
        $this->assertTrue(SafeUrl::isSafe('https://example.com/webhook'));
        $this->assertTrue(SafeUrl::isSafe('http://example.com'));
        $this->assertTrue(SafeUrl::isSafe('https://8.8.8.8/hook'));
    }

    public function test_blank_and_malformed_urls_are_blocked(): void
    {
        $this->assertFalse(SafeUrl::isSafe(null));
        $this->assertFalse(SafeUrl::isSafe(''));
        $this->assertFalse(SafeUrl::isSafe('not a url'));
        $this->assertFalse(SafeUrl::isSafe('https://'.str_repeat('a', 3000)));
    }

    public function test_violation_messages_are_human_readable(): void
    {
        $this->assertStringContainsString('http', (string) SafeUrl::violation('gopher://example.com/'));
        $this->assertStringContainsString('dahili', (string) SafeUrl::violation('http://127.0.0.1/'));
        $this->assertNull(SafeUrl::violation('https://example.com'));
    }

    /**
     * The general-QR branch of the check page redirected with no validation
     * at all, unlike the action-rule branch.
     */
    public function test_general_qr_with_unsafe_redirect_is_not_followed(): void
    {
        $company = Company::create(['name' => 'F', 'is_active' => true]);
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'K',
            'slug' => 'k'.Str::random(5),
            'is_active' => true,
        ]);

        $code = (string) Str::uuid();

        Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => ['tr' => 'Ürün'],
            'description' => ['tr' => 'd'],
            'type' => 'physical',
            'qr_mode' => 'general',
            'general_qr_code' => $code,
            'redirect_url' => 'javascript:alert(1)',
        ]);

        $this->get('/check/'.$code)->assertNotFound();
    }

    /** A safe general QR redirect must keep working. */
    public function test_general_qr_with_safe_redirect_still_redirects(): void
    {
        $company = Company::create(['name' => 'F', 'is_active' => true]);
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'K',
            'slug' => 'k'.Str::random(5),
            'is_active' => true,
        ]);

        $code = (string) Str::uuid();

        Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => ['tr' => 'Ürün'],
            'description' => ['tr' => 'd'],
            'type' => 'physical',
            'qr_mode' => 'general',
            'general_qr_code' => $code,
            'redirect_url' => 'https://example.com/landing',
        ]);

        $this->get('/check/'.$code)->assertRedirect('https://example.com/landing');
    }

    /**
     * `expiration_date` was rendered on the check page but never enforced, so
     * a lapsed digital product still handed out its payload.
     */
    public function test_expired_product_hides_digital_content_on_check_page(): void
    {
        $company = Company::create(['name' => 'F', 'is_active' => true]);
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'K',
            'slug' => 'k'.Str::random(5),
            'is_active' => true,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => ['tr' => 'Dijital Ürün'],
            'description' => ['tr' => 'd'],
            'type' => 'digital',
            'digital_content_type' => 'pdf',
            'digital_file_path' => 'digital_products/x.pdf',
            'expiration_date' => now()->subMonth(),
            'qr_mode' => 'unique',
        ]);

        $batch = Batch::create(['product_id' => $product->id, 'batch_number' => 'B1', 'quantity' => 1]);
        $item = $batch->items()->first();

        Livewire::test(CheckProduct::class, ['uuid' => $item->uuid])
            ->assertOk()
            ->assertSee('Süresi Dolmuş');
    }
}