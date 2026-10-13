<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductDocument;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Belgeler';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_name')
                    ->label('Belge')
                    ->placeholder(fn ($record) => basename($record->file_path))
                    ->searchable(),
                TextColumn::make('mime_type')
                    ->label('Tip')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Yüklenme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                \Filament\Actions\Action::make('download')
                    ->label('İndir')
                    ->icon('heroicon-o-arrow-down-tray')
                    // Not a raw Storage::url(): documents live on a private
                    // disk and are only reachable through the signed route.
                    ->authorize(fn (ProductDocument $record): bool => ProductResource::canEdit($record->product))
                    ->url(fn (ProductDocument $record): string => \App\Http\Controllers\PublicAssetController::signedUrl(
                        'assets.document',
                        ['document' => $record->getKey()],
                    ), shouldOpenInNewTab: true),
            ]);
    }
}
