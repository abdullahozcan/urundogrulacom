<?php

namespace Tests\Feature;

use App\Helpers\UploadRules;
use App\Http\Controllers\PublicAssetController;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecureFileDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $overrides = []): Product
    {
        $company = Company::create(['name' => 'Firma', 'is_active' => true]);
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Kategori',
            'slug' => 'k-'.Str::random(6),
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => ['tr' => 'Ürün'],
            'description' => ['tr' => 'Açıklama'],
            'type' => 'digital',
            'digital_content_type' => 'pdf',
            'qr_mode' => 'unique',
        ], $overrides));
    }

    /**
     * The `local` disk used `serve => true`, which registered an
     * unauthenticated `GET /storage/{path}` route over storage/app/private.
     * That made the "private" disk public.
     */
    public function test_private_disk_is_not_served_unauthenticated(): void
    {
        Storage::disk('local')->put('digital_products/secret.pdf', 'TOP SECRET');

        $response = $this->get('/storage/digital_products/secret.pdf');

        $this->assertNotSame(200, $response->getStatusCode());
    }

    public function test_digital_content_requires_a_valid_signature(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital_products/licence.pdf', 'PDF BODY');

        $product = $this->product(['digital_file_path' => 'digital_products/licence.pdf']);

        $this->get('/assets/digital/'.$product->getKey())->assertForbidden();
    }

    public function test_signed_digital_content_is_served(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital_products/licence.pdf', 'PDF BODY');

        $product = $this->product(['digital_file_path' => 'digital_products/licence.pdf']);

        $url = PublicAssetController::signedUrl('assets.digital', ['product' => $product->getKey()]);

        $response = $this->get($url);

        $response->assertOk();
        $this->assertSame('PDF BODY', $response->streamedContent());
    }

    /**
     * `expiration_date` used to be displayed on the check page but never
     * enforced, so a lapsed licence stayed downloadable forever.
     */
    public function test_expired_product_does_not_serve_digital_content(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital_products/old.pdf', 'OLD');

        $product = $this->product([
            'digital_file_path' => 'digital_products/old.pdf',
            'expiration_date' => now()->subDay(),
        ]);

        $url = PublicAssetController::signedUrl('assets.digital', ['product' => $product->getKey()]);

        $this->get($url)->assertStatus(410);
    }

    public function test_product_document_requires_a_valid_signature(): void
    {
        Storage::fake('local');

        $product = $this->product(['type' => 'physical']);
        $document = ProductDocument::create([
            'product_id' => $product->getKey(),
            'file_path' => 'product_documents/cert.pdf',
            'original_name' => 'Sertifika',
            'sort_order' => 0,
        ]);

        $this->get('/assets/documents/'.$document->getKey())->assertForbidden();
    }

    public function test_signed_product_document_is_served(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('product_documents/cert.pdf', 'CERT');

        $product = $this->product(['type' => 'physical']);
        $document = ProductDocument::create([
            'product_id' => $product->getKey(),
            'file_path' => 'product_documents/cert.pdf',
            'original_name' => 'Sertifika',
            'sort_order' => 0,
        ]);

        $url = PublicAssetController::signedUrl('assets.document', ['document' => $document->getKey()]);

        $this->get($url)->assertOk();
    }

    public function test_expired_product_does_not_serve_documents(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('product_documents/cert.pdf', 'CERT');

        $product = $this->product(['type' => 'physical', 'expiration_date' => now()->subDay()]);
        $document = ProductDocument::create([
            'product_id' => $product->getKey(),
            'file_path' => 'product_documents/cert.pdf',
            'original_name' => 'Sertifika',
            'sort_order' => 0,
        ]);

        $url = PublicAssetController::signedUrl('assets.document', ['document' => $document->getKey()]);

        $this->get($url)->assertStatus(410);
    }

    /** A tampered signature must not be honoured. */
    public function test_tampered_signature_is_rejected(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital_products/licence.pdf', 'PDF BODY');

        $product = $this->product(['digital_file_path' => 'digital_products/licence.pdf']);

        $url = PublicAssetController::signedUrl('assets.digital', ['product' => $product->getKey()]);
        $tampered = str_replace('signature=', 'signature=x', $url);

        $this->get($tampered)->assertForbidden();
    }

    /**
     * `->image()` expands to `mimetypes:image/*`, which accepts SVG. SVG is
     * inert inside <img> but executes script when the stored URL is opened
     * directly on the application's own origin.
     */
    public function test_svg_is_excluded_from_the_image_allowlist(): void
    {
        $this->assertNotContains('image/*', UploadRules::IMAGE_MIMES);
        $this->assertContains('image/jpeg', UploadRules::IMAGE_MIMES);
        $this->assertContains('image/png', UploadRules::IMAGE_MIMES);
        $this->assertContains('image/webp', UploadRules::IMAGE_MIMES);
    }

    public function test_every_upload_field_has_a_size_limit(): void
    {
        $this->assertGreaterThan(0, UploadRules::IMAGE_MAX_KB);
        $this->assertGreaterThan(0, UploadRules::DOCUMENT_MAX_KB);
        $this->assertGreaterThan(0, UploadRules::DIGITAL_MAX_KB);
    }

    /** Documents must not widen the allowlist back to generic images. */
    public function test_document_allowlist_is_restricted(): void
    {
        $this->assertContains('application/pdf', UploadRules::DOCUMENT_MIMES);
        $this->assertNotContains('image/gif', UploadRules::DOCUMENT_MIMES);
        $this->assertNotContains('image/*', UploadRules::DOCUMENT_MIMES);
    }

    public function test_security_headers_are_present_on_public_pages(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString(
            "default-src 'self'",
            (string) $response->headers->get('Content-Security-Policy')
        );
        $this->assertStringContainsString(
            "object-src 'none'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    /** HSTS only makes sense over TLS. */
    public function test_hsts_is_not_emitted_over_plain_http(): void
    {
        $response = $this->get('/');

        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }
}