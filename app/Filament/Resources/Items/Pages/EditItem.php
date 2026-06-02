<?php

namespace App\Filament\Resources\Items\Pages;

use App\Filament\Resources\Items\ItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\ItemScansChart::class,
            \App\Filament\Widgets\ScanHistoryWidget::class,
        ];
    }
}
