<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Products\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\Products\RelationManagers\ScansRelationManager;
use App\Models\Company;
use App\Models\Scan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductDetailTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_page_is_registered_with_detail_relations(): void
    {
        $pages = ProductResource::getPages();

        $this->assertArrayHasKey('view', $pages);
        $this->assertContains(ScansRelationManager::class, ProductResource::getRelations());
        $this->assertContains(DocumentsRelationManager::class, ProductResource::getRelations());
        $this->assertContains(ReviewsRelationManager::class, ProductResource::getRelations());
    }

    public function test_scans_relation_manager_scopes_to_owner_product(): void
    {
        $company = Company::factory()->create();

        $productA = Product::factory()->create(['company_id' => $company->id, 'name' => ['tr' => 'A']]);
        $productB = Product::factory()->create(['company_id' => $company->id, 'name' => ['tr' => 'B']]);

        foreach ([$productA, $productA, $productB] as $product) {
            $item = $product->batches()->create([
                'batch_number' => 'B-' . uniqid(),
                'quantity' => 1,
            ])->items()->first();

            Scan::create([
                'item_id' => $item->id,
                'ip_address' => '127.0.0.1',
                'scanned_at' => now(),
            ]);
        }

        $manager = new ScansRelationManager();
        $manager->ownerRecord = $productA;

        $this->assertSame(2, $manager->getRelationship()->count());

        $manager = new ScansRelationManager();
        $manager->ownerRecord = $productB;

        $this->assertSame(1, $manager->getRelationship()->count());
    }

    public function test_company_admin_cannot_view_other_company_product_detail(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $productB = Product::factory()->create(['company_id' => $companyB->id]);

        Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        $adminA = User::factory()->create(['company_id' => $companyA->id]);
        $adminA->assignRole('company_admin');

        $this->actingAs($adminA);

        $this->assertFalse(ProductResource::canView($productB));
    }
}
