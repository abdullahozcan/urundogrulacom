<?php

namespace App\Livewire;

use App\Models\ActionLog;
use App\Models\ActionRule;
use App\Models\Item;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Scan;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Str;

class CheckProduct extends Component
{
    public $uuid;
    public $item;
    public $scanSaved = false;
    public $redirectUrl = null;
    public $scanRedirected = false;
    public $actionContent = null;

    public $survey = null;
    public $surveyAnswers = [];
    public $surveySubmitted = false;
    public $lastScanId = null;

    public $deviceToken = '';
    public $existingReview = null;
    public $rating = 5;
    public $comment = '';
    public $reviewSaved = false;

    public function mount($uuid)
    {
        $this->uuid = $uuid;

        $browserLang = substr(request()->header('Accept-Language', 'en'), 0, 2);
        $supported = array_keys(config('languages.supported', ['en' => 'English']));
        app()->setLocale(in_array($browserLang, $supported) ? $browserLang : config('languages.fallback', 'en'));

        $this->item = Item::with([
            'batch.product.company',
            'batch.product.documents',
            'batch.product.reviews',
        ])->where('uuid', $uuid)->first();

        if (!$this->item) {
            $generalProduct = Product::query()
                ->where('general_qr_code', $uuid)
                ->first();

            if (!$generalProduct || !$generalProduct->redirect_url) {
                abort(404, 'Product not found or invalid QR code.');
            }

            return redirect()->away($generalProduct->redirect_url);
        }

        $product = $this->item->batch?->product;

        if ($product && $product->redirect_url) {
            $this->redirectUrl = $product->redirect_url;
        }

        $this->deviceToken = request()->cookie('device_token', (string) Str::uuid());

        $productId = $this->item->batch->product_id;
        $cookieToken = hash('sha256', $this->deviceToken . '|' . $productId);

        $this->existingReview = ProductReview::where('product_id', $productId)
            ->where('cookie_token', $cookieToken)
            ->first();
    }

    #[On('saveScan')]
    public function saveScanData($lat, $lng)
    {
        if ($this->scanSaved) {
            return;
        }

        $validated = validator(['lat' => $lat, 'lng' => $lng], [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ])->validate();

        $browser = '-';
        $deviceType = 'Desktop';

        try {
            $result = app('browser-detect')->detect();
            $browser = $result->browserName();
            $deviceType = $result->isMobile() ? 'Mobile' : ($result->isTablet() ? 'Tablet' : 'Desktop');
        } catch (\Throwable $e) {
            $agent = request()->userAgent();
            $browser = $agent ? substr($agent, 0, 255) : '-';
            $deviceType = preg_match('/mobile|android|iphone|ipad/i', $agent ?? '') ? 'Mobile' : 'Desktop';
        }

        $scan = Scan::create([
            'item_id' => $this->item->id,
            'ip_address' => request()->ip(),
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'browser' => $browser,
            'device_type' => $deviceType,
        ]);

        $this->item->update(['is_scanned' => true]);
        $this->scanSaved = true;
        $this->lastScanId = $scan->id;

        $this->loadActionResults($scan->id);

        if ($this->redirectUrl && !$this->scanRedirected) {
            $this->scanRedirected = true;

            return redirect()->away($this->redirectUrl);
        }
    }

    private function loadActionResults(int $scanId): void
    {
        $logs = ActionLog::query()
            ->where('scan_id', $scanId)
            ->where('result', ActionLog::RESULT_SUCCESS)
            ->whereHas('rule', function ($query) {
                $query->whereIn('action_type', [
                    ActionRule::ACTION_SHOW_CONTENT,
                    ActionRule::ACTION_REDIRECT,
                    ActionRule::ACTION_SHOW_SURVEY,
                ]);
            })
            ->with('rule')
            ->orderBy('id')
            ->get();

        foreach ($logs as $log) {
            $rule = $log->rule;

            if (!$rule) {
                continue;
            }

            if ($rule->action_type === ActionRule::ACTION_SHOW_CONTENT) {
                $payload = $rule->action_payload ?? [];

                $this->actionContent = [
                    'title' => $this->resolveLocaleValue($payload['title'] ?? null),
                    'body' => $this->resolveLocaleValue($payload['body'] ?? null),
                    'style' => ($payload['style'] ?? 'info') === 'warning' ? 'warning' : 'info',
                ];
            } elseif ($rule->action_type === ActionRule::ACTION_REDIRECT && !$this->redirectUrl) {
                $url = $rule->action_payload['url'] ?? null;

                if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
                    $this->redirectUrl = $url;
                }
            } elseif ($rule->action_type === ActionRule::ACTION_SHOW_SURVEY) {
                $surveyId = $rule->action_payload['survey_id'] ?? null;

                if ($surveyId && !$this->survey) {
                    $this->survey = Survey::query()
                        ->where('company_id', $rule->company_id)
                        ->where('is_active', true)
                        ->with('questions')
                        ->find($surveyId);
                }
            }
        }
    }

    public function submitSurvey()
    {
        if (!$this->survey || !$this->lastScanId || $this->surveySubmitted) {
            return;
        }

        foreach ($this->survey->questions as $question) {
            $answer = $this->surveyAnswers[$question->id] ?? null;

            $isEmpty = $answer === null || $answer === '' || (is_array($answer) && empty($answer));

            if ($question->is_required && $isEmpty) {
                $this->addError("surveyAnswers.{$question->id}", 'Bu soru zorunludur.');
                return;
            }
        }

        foreach ($this->survey->questions as $question) {
            $answer = $this->surveyAnswers[$question->id] ?? null;

            if ($answer === null || $answer === '' || (is_array($answer) && empty($answer))) {
                continue;
            }

            $value = is_array($answer)
                ? json_encode(array_map(fn ($item) => strip_tags(trim((string) $item)), array_slice($answer, 0, 10)), JSON_UNESCAPED_UNICODE)
                : strip_tags(trim((string) $answer));

            SurveyResponse::updateOrCreate(
                [
                    'question_id' => $question->id,
                    'scan_id' => $this->lastScanId,
                ],
                [
                    'survey_id' => $this->survey->id,
                    'value' => mb_substr($value, 0, 1000),
                ]
            );
        }

        $this->surveySubmitted = true;
    }

    private function resolveLocaleValue($value)
    {
        if (is_array($value)) {
            return $value[app()->getLocale()]
                ?? $value[config('languages.default', 'tr')]
                ?? reset($value);
        }

        return $value;
    }

    public function submitReview()
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $productId = $this->item->batch->product_id;
        $cookieToken = hash('sha256', $this->deviceToken . '|' . $productId);

        ProductReview::create([
            'product_id' => $productId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'cookie_token' => $cookieToken,
        ]);

        $this->reviewSaved = true;

        $this->existingReview = ProductReview::where('product_id', $productId)
            ->where('cookie_token', $cookieToken)
            ->first();

        $this->item->batch->product->load('reviews');

        $this->dispatch('reviewSubmitted');
    }

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.check-product');
    }
}
