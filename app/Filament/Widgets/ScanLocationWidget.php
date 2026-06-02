<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ScanLocationWidget extends Widget
{
    protected static bool $isLazy = false;

    public ?\Illuminate\Database\Eloquent\Model $record = null;

    protected string $view = 'filament.widgets.scan-location-widget';

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Konum';
}
