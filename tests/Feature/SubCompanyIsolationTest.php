<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubCompanyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_company_admin_sees_sub_company_categories(): void
    {
        $parent = Company::factory()->create();
        $child = Company::factory()->create(['parent_id' => $parent->id]);

        Category::factory()->create(['company_id' => $parent->id, 'name' => 'Parent Kategori']);
        Category::factory()->create(['company_id' => $child->id, 'name' => 'Child Kategori']);

        $role = Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['company_id' => $parent->id]);
        $user->assignRole($role);

        $this->actingAs($user);

        $companyIds = $user->getAccessibleCompanyIds();
        $this->assertContains($parent->id, $companyIds);
        $this->assertContains($child->id, $companyIds);

        $categoryNames = \App\Filament\Resources\Categories\CategoryResource::getEloquentQuery()
            ->pluck('name')
            ->all();

        $this->assertContains('Parent Kategori', $categoryNames);
        $this->assertContains('Child Kategori', $categoryNames);
    }

    public function test_child_company_admin_does_not_see_parent_categories(): void
    {
        $parent = Company::factory()->create();
        $child = Company::factory()->create(['parent_id' => $parent->id]);

        Category::factory()->create(['company_id' => $parent->id, 'name' => 'Parent Kategori']);
        Category::factory()->create(['company_id' => $child->id, 'name' => 'Child Kategori']);

        $role = Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['company_id' => $child->id]);
        $user->assignRole($role);

        $this->actingAs($user);

        $companyIds = $user->getAccessibleCompanyIds();
        $this->assertContains($child->id, $companyIds);
        $this->assertNotContains($parent->id, $companyIds);
    }

    public function test_sibling_companies_do_not_see_each_others_data(): void
    {
        $parent = Company::factory()->create();
        $childA = Company::factory()->create(['parent_id' => $parent->id]);
        $childB = Company::factory()->create(['parent_id' => $parent->id]);

        Category::factory()->create(['company_id' => $childA->id, 'name' => 'A Kategori']);
        Category::factory()->create(['company_id' => $childB->id, 'name' => 'B Kategori']);

        $role = Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['company_id' => $childA->id]);
        $user->assignRole($role);

        $this->actingAs($user);

        $categoryNames = \App\Filament\Resources\Categories\CategoryResource::getEloquentQuery()
            ->pluck('name')
            ->all();

        $this->assertContains('A Kategori', $categoryNames);
        $this->assertNotContains('B Kategori', $categoryNames);
    }

    public function test_parent_company_admin_can_access_child_company(): void
    {
        $parent = Company::factory()->create();
        $child = Company::factory()->create(['parent_id' => $parent->id]);

        $role = Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['company_id' => $parent->id]);
        $user->assignRole($role);

        $this->actingAs($user);

        $this->assertTrue($user->canAccessCompany($parent));
        $this->assertTrue($user->canAccessCompany($child));
    }

    public function test_child_company_admin_cannot_access_parent_company(): void
    {
        $parent = Company::factory()->create();
        $child = Company::factory()->create(['parent_id' => $parent->id]);

        $role = Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['company_id' => $child->id]);
        $user->assignRole($role);

        $this->actingAs($user);

        $this->assertTrue($user->canAccessCompany($child));
        $this->assertFalse($user->canAccessCompany($parent));
    }
}
