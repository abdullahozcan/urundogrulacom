<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                Select::make('type')
                    ->label('Ürün Tipi')
                    ->options([
                        'physical' => 'Fiziksel',
                        'digital' => 'Dijital',
                    ])
                    ->default('physical')
                    ->required()
                    ->live(),
                Select::make('digital_content_type')
                    ->label('İçerik Tipi')
                    ->options([
                        'pdf' => 'PDF Belgesi',
                        'video' => 'Video',
                    ])
                    ->visible(fn (\Filament\Forms\Get $get) => $get('type') === 'digital')
                    ->required(fn (\Filament\Forms\Get $get) => $get('type') === 'digital')
                    ->live(),
                FileUpload::make('digital_file_path')
                    ->label('Dijital Dosya')
                    ->directory('digital_products')
                    ->acceptedFileTypes(['application/pdf', 'video/mp4', 'video/quicktime'])
                    ->maxSize(51200) // 50MB
                    ->visible(fn (\Filament\Forms\Get $get) => $get('type') === 'digital')
                    ->required(fn (\Filament\Forms\Get $get) => $get('type') === 'digital'),
            ]);
    }
}
