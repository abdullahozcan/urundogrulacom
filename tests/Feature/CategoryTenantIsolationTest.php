<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_sees_only_own_company_categories(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Category::factory()->create(['company_id' => $companyA->id, 'name' => 'A-Kategori']);
        Category::factory()->create(['company_id' => $companyB->id, 'name' => 'B-Kategori']);

        $role = Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $user->assignRole($role);

        $this->actingAs($user);

        $categoryNames = CategoryResource::getEloquentQuery()->pluck('name')->all();

        $this->assertContains('A-Kategori', $categoryNames);
        $this->assertNotContains('B-Kategori', $categoryNames);
    }
}

