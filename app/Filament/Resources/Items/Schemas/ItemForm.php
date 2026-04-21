<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('batch_id')
                    ->relationship('batch', 'id')
                    ->required()
                    ->disabled(),
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->disabled(),
                Toggle::make('is_scanned')
                    ->label('Okutuldu mu?')
                    ->disabled(),
                \Filament\Forms\Components\Placeholder::make('scan_count')
                    ->label('Toplam Okutulma Sayısı')
                    ->content(fn ($record) => $record ? $record->scans()->count() . ' kez' : '-'),
            ]);
    }
}
