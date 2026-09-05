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

class ActionRuleTriggerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Product $product;

    private function makeRule(array $attributes): ActionRule
    {
        return ActionRule::create(array_merge([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'name' => 'Test kuralı',
            'trigger_type' => 'scan_count',
            'trigger_config' => ['count' => 3, 'scope' => 'item'],
            'action_type' => 'show_content',
            'action_payload' => [
                'title' => 'Kampanya',
                'body' => 'Teşekkürler!',
                'style' => 'info',
            ],
        ], $attributes));
    }

    private function makeScan(array $attributes = []): Scan
    {
        return Scan::create(array_merge([
            'item_id' => $this->product->batches()->first()->items()->first()->id,
            'scanned_at' => now(),
        ], $attributes));
    }

    protected function setUp(): void
    {
        parent::setUp();

        SettingHelper::forgetCache();

        $this->company = Company::factory()->create();

        $this->product = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => ['tr' => 'Test Ürün', 'en' => 'Test Product'],
        ]);

        Batch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'B1',
            'quantity' => 3,
        ]);
    }

    public function test_scan_count_trigger_fires_only_at_nth_scan(): void
    {
        $rule = $this->makeRule([]);

        $scan1 = $this->makeScan();
        $scan2 = $this->makeScan();
        $scan3 = $this->makeScan();
        $engine = app(ActionEngine::class);

        $this->assertSame([], $engine->evaluate($scan1));
        $this->assertSame([], $engine->evaluate($scan2));
        $this->assertSame(0, ActionLog::count());

        $results = $engine->evaluate($scan3);

        $this->assertSame(ActionLog::RESULT_SUCCESS, $results[0]['result']);
        $this->assertSame('Kampanya', $results[0]['payload']['content']['title']);
        $this->assertSame(1, ActionLog::where('action_rule_id', $rule->id)->where('result', 'success')->count());
        $this->assertSame(1, $rule->fresh()->times_executed);
    }

    public function test_scan_count_scope_product_fires_at_nth_product_scan(): void
    {
        $rule = $this->makeRule([
            'trigger_config' => ['count' => 3, 'scope' => 'product'],
        ]);

        $items = $this->product->batches()->first()->items()->get();
        $engine = app(ActionEngine::class);

        $engine->evaluate($this->makeScan(['item_id' => $items[0]->id]));
        $engine->evaluate($this->makeScan(['item_id' => $items[1]->id]));
        $this->assertSame(0, ActionLog::count());

        $results = $engine->evaluate($this->makeScan(['item_id' => $items[2]->id]));

        $this->assertSame(ActionLog::RESULT_SUCCESS, $results[0]['result']);
        $this->assertSame(1, $rule->fresh()->times_executed);
    }

    public function test_first_scan_trigger_fires_only_on_first_scan(): void
    {
        $this->makeRule(['trigger_type' => 'first_scan', 'trigger_config' => null]);

        $engine = app(ActionEngine::class);
        $scan1 = $this->makeScan();
        $scan2 = $this->makeScan();

        $results = $engine->evaluate($scan1);
        $this->assertSame(ActionLog::RESULT_SUCCESS, $results[0]['result']);

        $this->assertSame([], $engine->evaluate($scan2));
        $this->assertSame(1, ActionLog::count());
    }

    public function test_item_revisit_trigger_matches_after_configured_days(): void
    {
        $this->makeRule([
            'trigger_type' => 'item_revisit',
            'trigger_config' => ['after_days' => 7],
        ]);

        $engine = app(ActionEngine::class);

        $previous = $this->makeScan(['scanned_at' => now()->subDays(8)]);
        $this->assertSame([], $engine->evaluate($previous));

        $results = $engine->evaluate($this->makeScan(['scanned_at' => now()]));
        $this->assertSame(ActionLog::RESULT_SUCCESS, $results[0]['result']);
    }

    public function test_item_revisit_trigger_does_not_match_within_days(): void
    {
        $this->makeRule([
            'trigger_type' => 'item_revisit',
            'trigger_config' => ['after_days' => 7],
        ]);

        $engine = app(ActionEngine::class);

        $this->makeScan(['scanned_at' => now()->subDays(1)]);
        $engine->evaluate($this->makeScan(['scanned_at' => now()->subDays(1)]));

        $this->assertSame([], $engine->evaluate($this->makeScan(['scanned_at' => now()])));
        $this->assertSame(0, ActionLog::count());
    }

    public function test_date_range_trigger_skips_outside_window(): void
    {
        $rule = $this->makeRule([
            'trigger_type' => 'date_range',
            'trigger_config' => null,
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeeks(2),
        ]);

        $results = app(ActionEngine::class)->evaluate($this->makeScan());

        $this->assertSame([], $results);
        $this->assertSame(
            1,
            ActionLog::where('action_rule_id', $rule->id)->where('result', 'skipped')->count()
        );
    }

    public function test_max_executions_limit_skips_rule(): void
    {
        $rule = $this->makeRule([
            'trigger_type' => 'date_range',
            'trigger_config' => null,
            'max_executions' => 1,
        ]);

        $engine = app(ActionEngine::class);

        $engine->evaluate($this->makeScan());
        $this->assertSame(1, $rule->fresh()->times_executed);

        $engine->evaluate($this->makeScan());

        $this->assertSame(1, $rule->fresh()->times_executed);
        $this->assertSame(
            1,
            ActionLog::where('action_rule_id', $rule->id)->where('result', 'skipped')->count()
        );
    }
}
