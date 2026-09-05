<?php

namespace Tests\Feature;

use App\Helpers\SettingHelper;
use App\Models\ActionRule;
use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActionEngineRenderTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Product $product;

    private string $itemUuid;

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
            'quantity' => 1,
        ]);

        $this->itemUuid = $this->product->batches()->first()->items()->first()->uuid;
    }

    public function test_show_content_rule_renders_after_scan(): void
    {
        Setting::create(['key' => 'action_engine_enabled', 'value' => true]);
        SettingHelper::forgetCache();

        ActionRule::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'name' => 'Kampanya kuralı',
            'trigger_type' => 'first_scan',
            'action_type' => 'show_content',
            'action_payload' => [
                'title' => 'Kampanya',
                'body' => 'İlk taramanız için teşekkürler!',
                'style' => 'info',
            ],
        ]);

        Livewire::test(\App\Livewire\CheckProduct::class, ['uuid' => $this->itemUuid])
            ->call('saveScanData', 41.0, 29.0)
            ->assertSee('Kampanya')
            ->assertSee('İlk taramanız için teşekkürler!');
    }

    public function test_redirect_rule_applies_when_product_has_no_redirect(): void
    {
        Setting::create(['key' => 'action_engine_enabled', 'value' => true]);
        SettingHelper::forgetCache();

        ActionRule::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'name' => 'Yönlendirme kuralı',
            'trigger_type' => 'first_scan',
            'action_type' => 'redirect',
            'action_payload' => ['url' => 'https://kampanya.example.com'],
        ]);

        Livewire::test(\App\Livewire\CheckProduct::class, ['uuid' => $this->itemUuid])
            ->call('saveScanData', 41.0, 29.0)
            ->assertSet('redirectUrl', 'https://kampanya.example.com');
    }

    public function test_product_redirect_wins_over_rule_redirect(): void
    {
        Setting::create(['key' => 'action_engine_enabled', 'value' => true]);
        SettingHelper::forgetCache();

        $this->product->update(['redirect_url' => 'https://product.example.com']);

        ActionRule::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'name' => 'Yönlendirme kuralı',
            'trigger_type' => 'first_scan',
            'action_type' => 'redirect',
            'action_payload' => ['url' => 'https://kampanya.example.com'],
        ]);

        Livewire::test(\App\Livewire\CheckProduct::class, ['uuid' => $this->itemUuid])
            ->call('saveScanData', 41.0, 29.0)
            ->assertSet('redirectUrl', 'https://product.example.com');
    }

    public function test_no_banner_when_flag_disabled(): void
    {
        ActionRule::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'name' => 'Kampanya kuralı',
            'trigger_type' => 'first_scan',
            'action_type' => 'show_content',
            'action_payload' => ['title' => 'Kampanya', 'body' => 'Teşekkürler'],
        ]);

        Livewire::test(\App\Livewire\CheckProduct::class, ['uuid' => $this->itemUuid])
            ->call('saveScanData', 41.0, 29.0)
            ->assertDontSee('Kampanya')
            ->assertSet('actionContent', null);
    }
}
