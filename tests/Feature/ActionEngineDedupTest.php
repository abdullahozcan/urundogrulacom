<?php

namespace Tests\Feature;

use App\Models\ActionLog;
use App\Models\ActionRule;
use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Scan;
use App\Helpers\SettingHelper;
use App\Services\ActionEngine\ActionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActionEngineDedupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SettingHelper::forgetCache();
    }

    public function test_same_scan_is_never_evaluated_twice(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'name' => ['tr' => 'Test Ürün'],
        ]);

        Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'B1',
            'quantity' => 1,
        ]);

        $rule = ActionRule::create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'name' => 'Tek seferlik kural',
            'trigger_type' => 'date_range',
            'action_type' => 'show_content',
            'action_payload' => ['title' => 'Merhaba', 'body' => 'Test'],
        ]);

        $scan = Scan::create([
            'item_id' => $product->batches()->first()->items()->first()->id,
            'scanned_at' => now(),
        ]);

        $engine = app(ActionEngine::class);

        $first = $engine->evaluate($scan);
        $second = $engine->evaluate($scan);

        $this->assertSame(ActionLog::RESULT_SUCCESS, $first[0]['result']);
        $this->assertSame([], $second);

        $this->assertSame(1, ActionLog::where('action_rule_id', $rule->id)->count());
        $this->assertSame(1, $rule->fresh()->times_executed);
    }

    public function test_company_wide_rules_apply_to_all_products(): void
    {
        $company = Company::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'name' => ['tr' => 'Test Ürün'],
        ]);

        Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'B1',
            'quantity' => 1,
        ]);

        ActionRule::create([
            'company_id' => $company->id,
            'product_id' => null,
            'name' => 'Firma geneli kural',
            'trigger_type' => 'date_range',
            'action_type' => 'show_content',
            'action_payload' => ['body' => 'Firma geneli'],
        ]);

        $scan = Scan::create([
            'item_id' => $product->batches()->first()->items()->first()->id,
            'scanned_at' => now(),
        ]);

        $results = app(ActionEngine::class)->evaluate($scan);

        $this->assertCount(1, $results);
        $this->assertSame('Firma geneli', $results[0]['payload']['content']['body']);
    }
}
