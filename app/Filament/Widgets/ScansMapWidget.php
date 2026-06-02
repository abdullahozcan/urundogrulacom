<?php

namespace App\Filament\Widgets;

use App\Models\Scan;
use Filament\Widgets\Widget;

class ScansMapWidget extends Widget
{
    protected string $view = 'filament.widgets.scans-map-widget';

    protected int | string | array $columnSpan = 'full';

    public function getScansProperty(): array
    {
        $query = Scan::with('item.batch.product.company')
            ->whereNotNull('lat')
            ->whereNotNull('lng');

        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $user->company_id) {
            $query->whereHas('item.batch.product', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        return $query->latest('scanned_at')->take(100)->get()->map(function ($scan) {
            return [
                'lat' => (float) $scan->lat,
                'lng' => (float) $scan->lng,
                'product_name' => $scan->item?->batch?->product?->name ?? 'Bilinmiyor',
                'batch_number' => $scan->item?->batch?->batch_number ?? '-',
                'date' => $scan->scanned_at?->format('d.m.Y H:i') ?? $scan->created_at->format('d.m.Y H:i'),
                'device' => $scan->device_type ?? 'Bilinmiyor',
                'browser' => $scan->browser ?? '-',
            ];
        })->toArray();
    }
}
