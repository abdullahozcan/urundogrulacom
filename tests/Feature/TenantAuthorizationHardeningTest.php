<?php

namespace Tests\Feature;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\MapWidget;
use App\Filament\Widgets\ScansMapWidget;
use App\Models\Category;
use App\Models\Company;
use App\Models\Item;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantAuthorizationHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('company_admin', 'web');
        Role::findOrCreate('super_admin', 'web');
    }

    private function company(string $name, ?Company $parent = null): Company
    {
        return Company::create([
            'name' => $name,
            'is_active' => true,
            'parent_id' => $parent?->id,
        ]);
    }

    private function admin(Company $company, string $role = 'company_admin'): User
    {
        $user = User::create([
            'name' => $role,
            'email' => \Illuminate\Support\Str::uuid().'@example.test',
            'password' => 'secret-password',
            'company_id' => $company->id,
        ]);
        $user->assignRole($role);

        return $user->fresh();
    }

    /** A company admin must not be able to edit or delete any user record. */
    public function test_company_admin_cannot_delete_users(): void
    {
        $parent = $this->company('Parent');
        $child = $this->company('Child', $parent);
        $admin = $this->admin($parent);
        $victim = $this->admin($child, 'company_admin');

        $this->actingAs($admin);

        $this->assertFalse(UserResource::canDelete($victim));
        $this->assertFalse(UserResource::canDeleteAny());
    }

    public function test_company_admin_cannot_create_users(): void
    {
        $parent = $this->company('Parent');
        $admin = $this->admin($parent);

        $this->actingAs($admin);

        $this->assertFalse(UserResource::canCreate());
    }

    public function test_super_admin_can_still_manage_users(): void
    {
        $parent = $this->company('Parent');
        $admin = $this->admin($parent);
        $admin->assignRole('super_admin');
        $victim = $this->admin($parent, 'company_admin');

        $this->actingAs($admin->fresh());

        $this->assertTrue(UserResource::canCreate());
        $this->assertTrue(UserResource::canEdit($victim));
        $this->assertTrue(UserResource::canDelete($victim));
    }

    /** A root company admin must never be able to delete a company row. */
    public function test_company_admin_cannot_delete_companies(): void
    {
        $parent = $this->company('Parent');
        $child = $this->company('Child', $parent);
        $admin = $this->admin($parent);

        $this->actingAs($admin);

        $this->assertFalse(CompanyResource::canDelete($parent));
        $this->assertFalse(CompanyResource::canDelete($child));
        $this->assertFalse(CompanyResource::canDeleteAny());
    }

    private function scanFor(Company $company, string $suffix): Scan
    {
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'C'.$suffix,
            'slug' => 'c'.$suffix,
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => ['tr' => 'P'.$suffix],
            'description' => ['tr' => 'D'.$suffix],
            'type' => 'physical',
            'qr_mode' => 'unique',
        ]);

        $batch = \App\Models\Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'B'.$suffix,
            'quantity' => 1,
        ]);

        $item = Item::create(['batch_id' => $batch->id, 'uuid' => (string) \Illuminate\Support\Str::uuid()]);

        return Scan::create([
            'item_id' => $item->id,
            'lat' => 41.0,
            'lng' => 29.0,
            'browser' => 'Test',
            'device_type' => 'Desktop',
            'ip_address' => '127.0.0.1',
            'scanned_at' => now(),
        ]);
    }

    /**
     * The map widgets used `&& $user->company_id`, so a user with a NULL
     * company skipped the filter entirely and saw every tenant's scans
     * (coordinates, IP, browser, product name).
     */
    public function test_map_widgets_fail_closed_for_user_without_company(): void
    {
        $companyA = $this->company('A');
        $companyB = $this->company('B');

        $orphan = User::create([
            'name' => 'No Company',
            'email' => 'orphan@example.test',
            'password' => 'secret-password',
            'company_id' => null,
        ]);
        $orphan->assignRole('company_admin');

        $this->scanFor($companyA, 'a');
        $this->scanFor($companyB, 'b');

        $this->actingAs($orphan->fresh());

        $this->assertCount(0, (new MapWidget)->getScansProperty(), 'MapWidget leaked cross-tenant scans');
        $this->assertCount(0, (new ScansMapWidget)->getScansProperty(), 'ScansMapWidget leaked cross-tenant scans');
    }

    public function test_map_widgets_are_scoped_for_company_admin(): void
    {
        $mine = $this->company('Mine');
        $other = $this->company('Other');
        $admin = $this->admin($mine);

        $this->scanFor($mine, 'a');
        $this->scanFor($other, 'b');

        $this->actingAs($admin);

        $scans = (new ScansMapWidget)->getScansProperty();

        $this->assertCount(1, $scans);
        $this->assertStringContainsString('Pa', $scans[0]['product_name']);
    }

    /** Popup HTML is built by string concatenation in the Leaflet widget. */
    public function test_map_widget_escapes_product_name(): void
    {
        $mine = $this->company('Mine');
        $admin = $this->admin($mine);

        $scan = $this->scanFor($mine, 'a');
        $scan->item->batch->product->update([
            'name' => ['tr' => '<img src=x onerror=alert(1)>'],
        ]);

        $this->actingAs($admin);

        $scans = (new ScansMapWidget)->getScansProperty();

        $this->assertStringNotContainsString('<img', $scans[0]['product_name']);
        $this->assertStringContainsString('&lt;img', $scans[0]['product_name']);
    }

    /**
     * `getActiveCompanyId()` trusted the session blindly. A stale or forged
     * value is now rejected in favour of the user's own company.
     */
    public function test_active_company_id_session_value_is_revalidated(): void
    {
        $mine = $this->company('Mine');
        $foreign = $this->company('Foreign');
        $admin = $this->admin($mine);

        session(['active_company_id' => $foreign->id]);

        $this->assertSame($mine->id, $admin->fresh()->getActiveCompanyId());
    }

    public function test_active_company_id_still_allows_accessible_child(): void
    {
        $parent = $this->company('Parent');
        $child = $this->company('Child', $parent);
        $admin = $this->admin($parent);

        session(['active_company_id' => $child->id]);

        $this->assertSame($child->id, $admin->fresh()->getActiveCompanyId());
    }

    /** A user with no company must resolve to an empty scope, not [null]. */
    public function test_accessible_company_ids_is_empty_without_company(): void
    {
        $orphan = User::create([
            'name' => 'Orphan',
            'email' => 'orphan2@example.test',
            'password' => 'secret-password',
            'company_id' => null,
        ]);

        $this->assertSame([], $orphan->getAccessibleCompanyIds());
    }
}