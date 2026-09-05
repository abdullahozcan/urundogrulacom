<?php

namespace Tests\Feature;

use App\Helpers\SettingHelper;
use App\Models\ActionLog;
use App\Models\ActionRule;
use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SurveyActionTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Product $product;

    private Survey $survey;

    private string $itemUuid;

    protected function setUp(): void
    {
        parent::setUp();

        SettingHelper::forgetCache();

        $this->company = Company::factory()->create();

        $this->product = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => ['tr' => 'Test Ürün'],
        ]);

        Batch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'B1',
            'quantity' => 1,
        ]);

        $this->itemUuid = $this->product->batches()->first()->items()->first()->uuid;

        $this->survey = Survey::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'name' => 'Memnuniyet Anketi',
        ]);

        SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'type' => 'single',
            'label' => 'Ürünü nasıl buldunuz?',
            'options' => ['Çok iyi', 'İyi', 'Kötü'],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'type' => 'text',
            'label' => 'Yorumunuz',
            'is_required' => false,
            'sort_order' => 2,
        ]);

        Setting::create(['key' => 'action_engine_enabled', 'value' => true]);
        SettingHelper::forgetCache();
    }

    private function makeRule(array $attributes = []): ActionRule
    {
        return ActionRule::create(array_merge([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'name' => 'Anket kuralı',
            'trigger_type' => 'first_scan',
            'action_type' => 'show_survey',
            'action_payload' => ['survey_id' => $this->survey->id],
        ], $attributes));
    }

    public function test_show_survey_rule_executes_and_renders_on_check_page(): void
    {
        $this->makeRule();

        Livewire::test(\App\Livewire\CheckProduct::class, ['uuid' => $this->itemUuid])
            ->call('saveScanData', 41.0, 29.0)
            ->assertSee('Memnuniyet Anketi')
            ->assertSee('Ürünü nasıl buldunuz?');

        $this->assertSame(
            1,
            ActionLog::where('result', 'success')->whereHas('rule', fn ($q) => $q->where('action_type', 'show_survey'))->count()
        );
    }

    public function test_survey_responses_are_persisted_per_scan(): void
    {
        $this->makeRule();

        $choiceQuestion = $this->survey->questions()->where('sort_order', 1)->first();
        $textQuestion = $this->survey->questions()->where('sort_order', 2)->first();

        Livewire::test(\App\Livewire\CheckProduct::class, ['uuid' => $this->itemUuid])
            ->call('saveScanData', 41.0, 29.0)
            ->set("surveyAnswers.{$choiceQuestion->id}", 'Çok iyi')
            ->set("surveyAnswers.{$textQuestion->id}", '<b>Güzel</b> ürün')
            ->call('submitSurvey')
            ->assertSet('surveySubmitted', true);

        $this->assertSame(2, SurveyResponse::count());
        $this->assertSame('Çok iyi', SurveyResponse::where('question_id', $choiceQuestion->id)->first()->value);
        $this->assertSame('Güzel ürün', SurveyResponse::where('question_id', $textQuestion->id)->first()->value);
    }

    public function test_required_question_blocks_submission(): void
    {
        $this->makeRule();

        $choiceQuestion = $this->survey->questions()->where('sort_order', 1)->first();

        Livewire::test(\App\Livewire\CheckProduct::class, ['uuid' => $this->itemUuid])
            ->call('saveScanData', 41.0, 29.0)
            ->call('submitSurvey')
            ->assertHasErrors(["surveyAnswers.{$choiceQuestion->id}"]);

        $this->assertSame(0, SurveyResponse::count());
    }

    public function test_invalid_survey_payload_logs_failed(): void
    {
        $this->makeRule(['action_payload' => ['survey_id' => 999999]]);

        \App\Models\Scan::create([
            'item_id' => $this->product->batches()->first()->items()->first()->id,
            'scanned_at' => now(),
        ]);

        $this->assertSame(1, ActionLog::where('result', 'failed')->count());
        $this->assertSame(0, ActionLog::where('result', 'success')->count());
    }
}
