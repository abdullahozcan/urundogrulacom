<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ItemScansChart extends ChartWidget
{
    public ?\Illuminate\Database\Eloquent\Model $record = null;

    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Son 7 Gün Okutulma Grafiği';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            if ($this->record) {
                $count = $this->record->scans()->whereDate('created_at', $date)->count();
            } else {
                $query = \App\Models\Scan::query()->whereDate('created_at', $date);
                $user = auth()->user();

                if ($user && !$user->isSuperAdmin()) {
                    $query->whereHas('item.batch.product', function ($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    });
                }

                $count = $query->count();
            }
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Okutulma Sayısı',
                    'data' => $data,
                    'borderColor' => '#0e42ff',
                    'backgroundColor' => 'rgba(14, 66, 255, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
