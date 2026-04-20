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
                    ->required(),
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Toggle::make('is_scanned')
                    ->required(),
            ]);
    }
}
