<?php

namespace Tests\Feature;

use App\Filament\Resources\ActionRules\ActionRuleResource;
use App\Models\ActionRule;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActionRuleTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;

    private Company $companyB;

    private ActionRule $ruleA;

    private ActionRule $ruleB;

    private User $companyAdminA;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create();
        $this->companyB = Company::factory()->create();

        $this->ruleA = ActionRule::create([
            'company_id' => $this->companyA->id,
            'name' => 'A-Kuralı',
            'trigger_type' => 'date_range',
            'action_type' => 'show_content',
            'action_payload' => ['body' => 'A içeriği'],
        ]);

        $this->ruleB = ActionRule::create([
            'company_id' => $this->companyB->id,
            'name' => 'B-Kuralı',
            'trigger_type' => 'date_range',
            'action_type' => 'show_content',
            'action_payload' => ['body' => 'B içeriği'],
        ]);

        Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->companyAdminA = User::factory()->create(['company_id' => $this->companyA->id]);
        $this->companyAdminA->assignRole('company_admin');

        $this->superAdmin = User::factory()->create(['company_id' => null]);
        $this->superAdmin->assignRole('super_admin');
    }

    public function test_company_admin_sees_only_own_company_rules(): void
    {
        $this->actingAs($this->companyAdminA);

        $names = ActionRuleResource::getEloquentQuery()->pluck('name')->all();

        $this->assertContains('A-Kuralı', $names);
        $this->assertNotContains('B-Kuralı', $names);
    }

    public function test_super_admin_sees_all_rules(): void
    {
        $this->actingAs($this->superAdmin);

        $names = ActionRuleResource::getEloquentQuery()->pluck('name')->all();

        $this->assertContains('A-Kuralı', $names);
        $this->assertContains('B-Kuralı', $names);
    }

    public function test_company_admin_cannot_view_other_company_rule(): void
    {
        $this->actingAs($this->companyAdminA);

        $this->assertTrue(ActionRuleResource::canView($this->ruleA));
        $this->assertFalse(ActionRuleResource::canView($this->ruleB));
        $this->assertFalse(ActionRuleResource::canEdit($this->ruleB));
        $this->assertFalse(ActionRuleResource::canDelete($this->ruleB));
    }
}
