<?php

namespace Tests\Feature;

use App\Events\ScanRecorded;
use App\Listeners\EvaluateActionRules;
use App\Models\ActionLog;
use App\Models\ActionRule;
use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Scan;
use App\Models\Setting;
use App\Helpers\SettingHelper;
use App\Services\ActionEngine\ActionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ActionEngineFailSafeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SettingHelper::forgetCache();
    }

    private function makeScenario(array $ruleAttributes = []): Scan
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

        ActionRule::create(array_merge([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'name' => 'Webhook kuralı',
            'trigger_type' => 'date_range',
            'action_type' => 'webhook',
            'action_payload' => ['url' => 'https://hooks.example.com/scan'],
        ], $ruleAttributes));

        return Scan::create([
            'item_id' => $product->batches()->first()->items()->first()->id,
            'scanned_at' => now(),
        ]);
    }

    public function test_failing_webhook_is_logged_and_does_not_throw(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);

        $scan = $this->makeScenario();
        $engine = app(ActionEngine::class);

        $results = $engine->evaluate($scan);

        $this->assertSame(ActionLog::RESULT_FAILED, $results[0]['result']);
        $this->assertNotNull($results[0]['error']);
        $this->assertSame(1, ActionLog::where('result', 'failed')->count());

        $rule = ActionRule::first();
        $this->assertSame(0, $rule->times_executed);
    }

    public function test_engine_never_breaks_the_listener(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);

        Setting::create([
            'key' => 'action_engine_enabled',
            'value' => true,
        ]);

        $scan = $this->makeScenario();

        $listener = new EvaluateActionRules();

        $listener->handle(new ScanRecorded($scan));

        $this->assertSame(1, ActionLog::where('result', 'failed')->count());

        $scan2 = Scan::create([
            'item_id' => $scan->item_id,
            'scanned_at' => now(),
        ]);

        $this->assertDatabaseHas('scans', ['id' => $scan2->id]);
    }

    public function test_disabled_flag_makes_engine_a_noop(): void
    {
        $scan = $this->makeScenario();

        (new EvaluateActionRules())->handle(new ScanRecorded($scan));

        $this->assertSame(0, ActionLog::count());
    }

    public function test_invalid_redirect_url_is_logged_as_failed(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);

        $scan = $this->makeScenario([
            'name' => 'Bozuk yönlendirme kuralı',
            'action_type' => 'redirect',
            'action_payload' => ['url' => 'not-a-url'],
        ]);

        $results = app(ActionEngine::class)->evaluate($scan);

        $this->assertSame(ActionLog::RESULT_FAILED, $results[0]['result']);
        $this->assertStringContainsString('redirect URL', $results[0]['error']);
    }
}
