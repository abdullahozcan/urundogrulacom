<?php

namespace App\Filament\Resources\ActionRules\Tables;

use App\Models\ActionLog;
use App\Models\ActionRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActionRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Kuruluş')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('product.name')
                    ->label('Ürün')
                    ->placeholder('Firma geneli')
                    ->limit(30),
                TextColumn::make('name')
                    ->label('Kural')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trigger_type')
                    ->label('Tetikleyici')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        ActionRule::TRIGGER_SCAN_COUNT => 'Tarama sayısı',
                        ActionRule::TRIGGER_FIRST_SCAN => 'İlk tarama',
                        ActionRule::TRIGGER_DATE_RANGE => 'Tarih aralığı',
                        ActionRule::TRIGGER_ITEM_REVISIT => 'Yeniden okutma',
                        default => $state,
                    }),
                TextColumn::make('action_type')
                    ->label('Aksiyon')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ActionRule::ACTION_SHOW_CONTENT => 'info',
                        ActionRule::ACTION_REDIRECT => 'warning',
                        ActionRule::ACTION_WEBHOOK => 'gray',
                        ActionRule::ACTION_NOTIFY_ADMIN => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        ActionRule::ACTION_SHOW_CONTENT => 'İçerik göster',
                        ActionRule::ACTION_REDIRECT => 'Yönlendir',
                        ActionRule::ACTION_WEBHOOK => 'Webhook',
                        ActionRule::ACTION_NOTIFY_ADMIN => 'Bildirim',
                        default => $state,
                    }),
                TextColumn::make('priority')
                    ->label('Öncelik')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('times_executed')
                    ->label('Yürütme')
                    ->alignCenter(),
                TextColumn::make('max_executions')
                    ->label('Limit')
                    ->placeholder('∞')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
