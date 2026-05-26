<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

