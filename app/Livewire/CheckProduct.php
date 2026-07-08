<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Scan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class CheckProduct extends Component
{
    public $uuid;
    public $item;
    public $scanSaved = false;

    public function mount($uuid)
    {
        $this->uuid = $uuid;

        $browserLang = substr(request()->header('Accept-Language', 'en'), 0, 2);
        $supported = array_keys(config('languages.supported', ['en' => 'English']));
        app()->setLocale(in_array($browserLang, $supported) ? $browserLang : config('languages.fallback', 'en'));

        $this->item = Item::with(['batch.product.company', 'batch.product.documents'])->where('uuid', $uuid)->first();

        if (!$this->item) {
            abort(404, 'Product not found or invalid QR code.');
        }
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

    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.check-product');
    }
}
