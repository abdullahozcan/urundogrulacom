<?php

namespace App\Filament\Resources\Scans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.id')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->searchable(),
                TextColumn::make('lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lng')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('browser')
                    ->searchable(),
                TextColumn::make('device_type')
                    ->searchable(),
                TextColumn::make('scanned_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Ürün')
                    ->options(fn () => \App\Models\Product::query()
                        ->when(auth()->user()?->isSuperAdmin() ? null : auth()->user()?->getAccessibleCompanyIds(), fn ($q, $ids) => $q->whereIn('company_id', $ids))
                        ->get()
                        ->mapWithKeys(fn ($p) => [$p->id => $p->getName()])
                        ->toArray()
                    )
                    ->query(fn (Builder $q, array $state) =>
                        $q->when($state['value'] ?? null, fn (Builder $q, $value) =>
                            $q->whereHas('item.batch.product', fn (Builder $q) => $q->where('id', $value))
                        )
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('batch_id')
                    ->label('Parti')
                    ->options(fn () => \App\Models\Batch::with('product')
                        ->when(auth()->user()?->isSuperAdmin() ? null : auth()->user()?->getAccessibleCompanyIds(), fn ($q, $ids) => $q->whereHas('product', fn (Builder $q) => $q->whereIn('company_id', $ids)))
                        ->get()
                        ->mapWithKeys(fn ($b) => [$b->id => '#' . $b->batch_number . ' (' . ($b->product?->getName() ?? '?') . ')'])
                        ->toArray()
                    )
                    ->query(fn (Builder $q, array $state) =>
                        $q->when($state['value'] ?? null, fn (Builder $q, $value) =>
                            $q->whereHas('item.batch', fn (Builder $q) => $q->where('id', $value))
                        )
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(fn () => \App\Models\Category::query()
                        ->when(auth()->user()?->isSuperAdmin() ? null : auth()->user()?->getAccessibleCompanyIds(), fn ($q, $ids) => $q->whereIn('company_id', $ids))
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->query(fn (Builder $q, array $state) =>
                        $q->when($state['value'] ?? null, fn (Builder $q, $value) =>
                            $q->whereHas('item.batch.product.category', fn (Builder $q) => $q->where('id', $value))
                        )
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
