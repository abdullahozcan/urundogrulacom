<?php

namespace App\Filament\Widgets;

use App\Models\Scan;
use Filament\Widgets\Widget;

class MapWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.map-widget';
    
    protected int | string | array $columnSpan = [
        'lg' => 6,
        'xl' => 6,
    ];
    
    protected static ?int $sort = 2;

    public function getScansProperty()
    {
        // Get latest scans that have coordinates, restricted by company if necessary
        $query = Scan::with('item.batch.product.company')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->latest('scanned_at')
            ->take(10);
            
        // Only company-scoped users are restricted to their own company data.
        // This check must fail CLOSED: if the tenant scope cannot be resolved,
        // no rows are returned instead of every tenant's rows.
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            $companyIds = $user?->getAccessibleCompanyIds() ?? [];

            if ($companyIds === []) {
                return collect();
            }

            $query->whereHas('item.batch.product', function ($q) use ($companyIds) {
                $q->whereIn('company_id', $companyIds);
            });
        }

        return $query->get()->map(function ($scan) {
            return [
                'lat' => $scan->lat,
                'lng' => $scan->lng,
                'product_name' => htmlspecialchars($scan->item?->batch?->product?->getName() ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'),
                'batch_number' => htmlspecialchars($scan->item?->batch?->batch_number ?? '-', ENT_QUOTES, 'UTF-8'),
                'date' => $scan->scanned_at->format('d.m.Y H:i'),
                'device' => htmlspecialchars($scan->device_type ?? 'Bilinmiyor', ENT_QUOTES, 'UTF-8'),
            ];
        });
    }
}
