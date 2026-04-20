<?php

namespace App\Filament\Widgets;

use App\Models\Scan;
use Filament\Widgets\Widget;

class MapWidget extends Widget
{
    protected string $view = 'filament.widgets.map-widget';
    
    // Make sure it displays full width
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 1; // Put it at the top

    public function getScansProperty()
    {
        // Get latest scans that have coordinates, restricted by company if necessary
        $query = Scan::with('item.batch.product.company')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->latest('scanned_at')
            ->take(50);
            
        // If user is not superadmin and is attached to a company, filter by company
        $user = auth()->user();
        if ($user && $user->company_id) {
            $query->whereHas('item.batch.product', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        
        return $query->get()->map(function ($scan) {
            return [
                'lat' => $scan->lat,
                'lng' => $scan->lng,
                'product_name' => $scan->item->batch->product->name,
                'batch_number' => $scan->item->batch->batch_number,
                'date' => $scan->scanned_at->format('d.m.Y H:i'),
                'device' => $scan->device_type,
            ];
        });
    }
}
