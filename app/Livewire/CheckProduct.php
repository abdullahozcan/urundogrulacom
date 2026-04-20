<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Scan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Hisorange\BrowserDetect\Parser as Browser;

class CheckProduct extends Component
{
    public $uuid;
    public $item;
    public $scanSaved = false;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
        // Find item
        $this->item = Item::with(['batch.product.company'])->where('uuid', $uuid)->first();
        
        if (!$this->item) {
            abort(404, 'Product not found or invalid QR code.');
        }
    }

    public function saveScanData($lat, $lng)
    {
        if ($this->scanSaved) {
            return;
        }

        $browser = Browser::browserName();
        $deviceType = Browser::isMobile() ? 'Mobile' : (Browser::isTablet() ? 'Tablet' : 'Desktop');

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

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.check-product');
    }
}
