<?php

namespace Tests\Feature;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Partners\PartnerResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NavigationVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_resources_are_visible_for_super_admin_only(): void
    {
        $superRole = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $companyRole = Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        $company = Company::factory()->create();

        $superAdmin = User::factory()->create(['company_id' => null]);
        $superAdmin->assignRole($superRole);

        $companyAdmin = User::factory()->create(['company_id' => $company->id]);
        $companyAdmin->assignRole($companyRole);

        $this->actingAs($superAdmin);
        $this->assertTrue(CompanyResource::shouldRegisterNavigation());
        $this->assertTrue(UserResource::shouldRegisterNavigation());
        $this->assertTrue(PageResource::shouldRegisterNavigation());
        $this->assertTrue(PostResource::shouldRegisterNavigation());
        $this->assertTrue(PartnerResource::shouldRegisterNavigation());
        $this->assertTrue(SettingResource::shouldRegisterNavigation());

        $this->actingAs($companyAdmin);
        $this->assertFalse(CompanyResource::shouldRegisterNavigation());
        $this->assertTrue(UserResource::shouldRegisterNavigation());
        $this->assertFalse(PageResource::shouldRegisterNavigation());
        $this->assertFalse(PostResource::shouldRegisterNavigation());
        $this->assertFalse(PartnerResource::shouldRegisterNavigation());
        $this->assertFalse(SettingResource::shouldRegisterNavigation());
    }
}

