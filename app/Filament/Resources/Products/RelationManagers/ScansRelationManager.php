<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\Scan;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScansRelationManager extends RelationManager
{
    protected static string $relationship = 'scans';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Taramalar';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function getRelationship(): \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Builder
    {
        $ownerRecord = $this->getOwnerRecord();

        return Scan::query()
            ->whereHas('item.batch', fn (Builder $q) => $q->where('product_id', $ownerRecord->id))
            ->orderByDesc('created_at');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scanned_at')
                    ->label('Zaman')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('item.uuid')
                    ->label('Item')
                    ->limit(13)
                    ->tooltip(fn ($record) => $record->item?->uuid),
                TextColumn::make('device_type')
                    ->label('Cihaz')
                    ->badge(),
                TextColumn::make('browser')
                    ->label('Tarayıcı')
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lat')
                    ->label('Enlem')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lng')
                    ->label('Boylam')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
