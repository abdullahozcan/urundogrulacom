<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\ProductReview;
use App\Models\Scan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Str;

class CheckProduct extends Component
{
    public $uuid;
    public $item;
    public $scanSaved = false;

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
            abort(404, 'Product not found or invalid QR code.');
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

        Scan::create([
            'item_id' => $this->item->id,
            'ip_address' => request()->ip(),
            'lat' => $lat,
            'lng' => $lng,
            'browser' => $browser,
            'device_type' => $deviceType,
        ]);

        $this->item->update(['is_scanned' => true]);
        $this->scanSaved = true;
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
