<?php

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Firma / Partner Adı')
                    ->maxLength(255),
                \Filament\Forms\Components\FileUpload::make('logo')
                    ->label('Firma Logosu')
                    ->image()
                    ->disk('public')
                    ->directory('partners')
                    ->required()
                    ->columnSpanFull(),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('Aktif mi?')
                    ->default(true),
                \Filament\Forms\Components\TextInput::make('sort_order')
                    ->label('Sıralama')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
