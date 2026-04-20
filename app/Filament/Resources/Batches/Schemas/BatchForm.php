<?php

namespace App\Filament\Resources\Batches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                TextInput::make('batch_number')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
            ]);
    }
}
