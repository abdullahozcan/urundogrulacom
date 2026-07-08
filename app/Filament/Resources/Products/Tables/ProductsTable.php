<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('qr_mode')
                    ->label('QR Modu')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'general' ? 'Genel' : 'Tekil'),
                TextColumn::make('general_qr_code')
                    ->label('Genel QR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('serial_number')
                    ->label('Seri No')
                    ->searchable(),
                TextColumn::make('expiration_date')
                    ->label('Son Kullanma')
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
