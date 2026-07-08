<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label(__('filament.product.table.company'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('filament.product.table.category'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('filament.product.table.name'))
                    ->getStateUsing(fn (Product $record): string => $record->getName())
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $locale = app()->getLocale();
                        return $query->where("name->{$locale}", 'like', "%{$search}%");
                    }),
                TextColumn::make('qr_mode')
                    ->label(__('filament.product.table.qr_mode'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'general' ? __('Genel') : __('Tekil')),
                TextColumn::make('general_qr_code')
                    ->label(__('filament.product.table.qr_code'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('serial_number')
                    ->label(__('filament.product.table.serial'))
                    ->searchable(),
                TextColumn::make('expiration_date')
                    ->label(__('filament.product.table.expiry'))
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('qr_png')
                    ->label('QR İndir (PNG)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Product $record) => $record->qr_mode === 'general' && $record->general_qr_code)
                    ->url(fn (Product $record) => route('admin.products.qr-png', $record)),
                Action::make('qr_pdf')
                    ->label('QR İndir (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn (Product $record) => $record->qr_mode === 'general' && $record->general_qr_code)
                    ->url(fn (Product $record) => route('admin.products.qr-pdf', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}