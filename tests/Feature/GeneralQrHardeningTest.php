<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralQrHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_general_qr_code_is_rejected_at_db_level(): void
    {
        $company = Company::factory()->create();

        Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'general',
            'general_qr_code' => 'DUPE-QR-CODE-0001',
        ]);

        $this->expectException(QueryException::class);

        Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'general',
            'general_qr_code' => 'DUPE-QR-CODE-0001',
        ]);
    }

    public function test_multiple_products_with_null_general_qr_code_are_allowed(): void
    {
        $company = Company::factory()->create();

        Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'unique',
        ]);

        Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'unique',
        ]);

        $this->assertSame(2, Product::count());
    }

    public function test_general_product_without_redirect_still_404s(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'qr_mode' => 'general',
            'general_qr_code' => 'NOREDIRECT-QR-0001',
            'redirect_url' => null,
        ]);

        $this->get(route('product.check', $product->general_qr_code))
            ->assertNotFound();
    }
}
