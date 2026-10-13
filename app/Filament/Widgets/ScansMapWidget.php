<?php

namespace App\Filament\Widgets;

use App\Models\Scan;
use Filament\Widgets\Widget;

class ScansMapWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.scans-map-widget';

    protected int | string | array $columnSpan = 'full';

    public function getScansProperty(): array
    {
        $query = Scan::with('item.batch.product.company')
            ->whereNotNull('lat')
            ->whereNotNull('lng');

        // Fail CLOSED: an unresolvable tenant scope returns no rows instead of
        // every tenant's rows. A null `company_id` must never mean "all".
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            $companyIds = $user?->getAccessibleCompanyIds() ?? [];

            if ($companyIds === []) {
                return [];
            }

            $query->whereHas('item.batch.product', function ($q) use ($companyIds) {
                $q->whereIn('company_id', $companyIds);
            });
        }

        return $query->latest('scanned_at')->take(100)->get()->map(function ($scan) {
            return [
                'lat' => (float) $scan->lat,
                'lng' => (float) $scan->lng,
                'product_name' => htmlspecialchars($scan->item?->batch?->product?->getName() ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'),
                'batch_number' => htmlspecialchars($scan->item?->batch?->batch_number ?? '-', ENT_QUOTES, 'UTF-8'),
                'date' => $scan->scanned_at?->format('d.m.Y H:i') ?? $scan->created_at->format('d.m.Y H:i'),
                'device' => htmlspecialchars($scan->device_type ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'),
                'browser' => htmlspecialchars($scan->browser ?? '-', ENT_QUOTES, 'UTF-8'),
            ];
        })->toArray();
    }
}
