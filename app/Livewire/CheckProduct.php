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

        $this->item = Item::with(['batch.product.company'])->where('uuid', $uuid)->first();

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

        $result = app('browser-detect')->detect();

        Scan::create([
            'item_id' => $this->item->id,
            'ip_address' => request()->ip(),
            'lat' => $lat,
            'lng' => $lng,
            'browser' => $result->browserName(),
            'device_type' => $result->isMobile() ? 'Mobile' : ($result->isTablet() ? 'Tablet' : 'Desktop'),
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
