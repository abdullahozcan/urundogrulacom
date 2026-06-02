<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ScanHistoryWidget extends Widget
{
    public ?\Illuminate\Database\Eloquent\Model $record = null;

    protected string $view = 'filament.widgets.scan-history';

    protected int | string | array $columnSpan = 2;

    protected ?string $heading = 'Son 10 Okutulma';

    public function getScans()
    {
        if (!$this->record) {
            return collect();
        }

        return $this->record->scans()
            ->latest('scanned_at')
            ->take(10)
            ->get();
    }
}
