<?php

namespace App\Filament\Widgets;

use App\Models\Scan;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class CategoryScansChart extends ChartWidget
{
    protected int | string | array $columnSpan = [
        'lg' => 6,
        'xl' => 6,
    ];

    protected ?string $maxHeight = '300';

    protected static ?int $sort = 4;

    protected ?string $heading = 'Son 30 Gün Kategori Kırılımı';

    protected function getData(): array
    {
        $user = auth()->user();

        $rows = Scan::query()
            ->select('categories.id', 'categories.name', new Expression('count(*) as scan_count'))
            ->where('scans.created_at', '>=', now()->subDays(30))
            ->join('items', 'items.id', '=', 'scans.item_id')
            ->join('batches', 'batches.id', '=', 'items.batch_id')
            ->join('products', 'products.id', '=', 'batches.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->when($user && !$user->isSuperAdmin(), function ($query) use ($user) {
                $query->whereIn('products.company_id', $user->getAccessibleCompanyIds());
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('scan_count')
            ->limit(8)
            ->get();

        $labels = $rows->pluck('name')->map(function ($name) {
            if (is_array($name)) {
                $name = $name[app()->getLocale()] ?? reset($name);
            }

            return $name;
        })->all();

        return [
            'datasets' => [
                [
                    'label' => 'Tarama Sayısı',
                    'data' => $rows->pluck('scan_count')->all(),
                    'backgroundColor' => '#0e42ff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
