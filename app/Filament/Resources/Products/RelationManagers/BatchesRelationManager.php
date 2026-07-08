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

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament.product.batches_table.title');
    }

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
                    ->label(__('filament.product.batches_table.batch_no'))
                    ->required(),
                TextInput::make('quantity')
                    ->label(__('filament.product.batches_table.quantity'))
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
                    ->label(__('filament.product.batches_table.batch_no'))
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(__('filament.product.batches_table.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('filament.product.batches_table.created_items'))
                    ->counts('items')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label(__('filament.product.batches_table.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('filament.actions.add_batch'))
                    ->modalHeading(__('filament.actions.new_batch'))
                    ->createAnother(false),
            ])
            ->recordActions([
                Action::make('viewItems')
                    ->label(__('filament.actions.view_items'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Batch $record): string => ItemResource::getUrl('index', [
                        'batch_id' => $record->id,
                    ])),
                EditAction::make()
                    ->modalHeading(__('filament.actions.edit_batch')),
                DeleteAction::make(),
            ]);
    }
}
