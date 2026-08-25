<?php

namespace Tests\Feature;

use App\Livewire\CheckProduct;
use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductQrModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_qr_code_is_cleared_when_mode_is_unique(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'unique',
            'general_qr_code' => 'EXISTING-GENERAL-CODE',
        ]);

        $this->assertNull($product->fresh()->general_qr_code);
    }

    public function test_redirect_url_is_preserved_for_general_mode(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'general',
            'general_qr_code' => 'GENERAL-CODE',
            'redirect_url' => 'https://example.com',
        ]);

        $this->assertSame('https://example.com', $product->fresh()->redirect_url);
    }

    public function test_general_product_with_redirect_url_redirects_immediately(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'general',
            'general_qr_code' => 'GENERAL-REDIRECT-CODE',
            'redirect_url' => 'https://example.com',
        ]);

        $this->get(route('product.check', $product->general_qr_code))
            ->assertRedirect('https://example.com');
    }

    public function test_general_product_without_redirect_url_still_404s(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'general',
            'general_qr_code' => 'GENERAL-NO-REDIRECT-CODE',
            'redirect_url' => null,
        ]);

        $this->get(route('product.check', $product->general_qr_code))
            ->assertNotFound();
    }

    public function test_unique_product_with_redirect_url_redirects_after_scan(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'unique',
            'redirect_url' => 'https://example.com',
        ]);

        $batch = Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-1',
            'quantity' => 1,
        ]);

        $item = $batch->items()->first();

        Livewire::test(CheckProduct::class, ['uuid' => $item->uuid])
            ->assertSet('redirectUrl', 'https://example.com')
            ->call('saveScanData', 41.0, 29.0)
            ->assertRedirect('https://example.com');

        $this->assertDatabaseHas('scans', ['item_id' => $item->id]);
        $this->assertSame(1, $item->fresh()->is_scanned);
    }

    public function test_unique_product_without_redirect_url_does_not_redirect(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'unique',
            'redirect_url' => null,
        ]);

        $batch = Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-2',
            'quantity' => 1,
        ]);

        $item = $batch->items()->first();

        Livewire::test(CheckProduct::class, ['uuid' => $item->uuid])
            ->assertSet('redirectUrl', null)
            ->call('saveScanData', 41.0, 29.0)
            ->assertSet('scanSaved', true)
            ->assertNoRedirect();

        $this->assertDatabaseHas('scans', ['item_id' => $item->id]);
    }
}