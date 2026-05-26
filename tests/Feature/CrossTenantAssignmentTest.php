<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CrossTenantAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_cannot_be_assigned_to_other_company_category(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $categoryOfCompanyB = Category::factory()->create([
            'company_id' => $companyB->id,
        ]);

        $this->expectException(ValidationException::class);

        Product::factory()->create([
            'company_id' => $companyA->id,
            'category_id' => $categoryOfCompanyB->id,
        ]);
    }
}

