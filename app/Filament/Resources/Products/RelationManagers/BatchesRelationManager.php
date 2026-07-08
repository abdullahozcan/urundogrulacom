<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\Items\ItemResource;
use App\Models\Batch;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'Partiler';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('batch_number')
                    ->label('Parti Numarası')
                    ->required(),
                TextInput::make('quantity')
                    ->label('Adet')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_number')
                    ->label('Parti No')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Adet')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Oluşturulan Adet')
                    ->counts('items')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Parti Ekle')
                    ->modalHeading('Yeni Parti Oluştur')
                    ->createAnother(false),
            ])
            ->recordActions([
                Action::make('viewItems')
                    ->label('Öğeleri Gör')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Batch $record): string => ItemResource::getUrl('index', [
                        'batch_id' => $record->id,
                    ])),
                EditAction::make()
                    ->modalHeading('Partiyi Düzenle'),
                DeleteAction::make(),
            ]);
    }
}
