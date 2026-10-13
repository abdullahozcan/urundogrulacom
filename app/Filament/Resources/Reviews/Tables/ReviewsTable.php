<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\ProductReview;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Ürün')
                    ->formatStateUsing(fn ($state, ProductReview $record) => $record->product?->getName() ?? '-')
                    ->limit(30)
                    ->searchable(query: fn ($query, $search) => $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"))),
                TextColumn::make('rating')
                    ->label('Puan')
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state))
                    ->alignCenter(),
                TextColumn::make('comment')
                    ->label('Yorum')
                    ->limit(60)
                    ->tooltip(fn (ProductReview $record): ?string => $record->comment)
                    ->wrap(),
                IconColumn::make('is_approved')
                    ->label('Durum')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Onay Durumu')
                    ->placeholder('Tümü')
                    ->trueLabel('Onaylı')
                    ->falseLabel('Onay bekliyor'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(fn (ProductReview $record): string => $record->is_approved ? 'Onayı Kaldır' : 'Onayla')
                    ->icon(fn (ProductReview $record): string => $record->is_approved ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (ProductReview $record): string => $record->is_approved ? 'warning' : 'success')
                    ->requiresConfirmation(false)
                    // Custom actions are not auto-authorized by Filament.
                    // `ReviewResource::canEdit()` is hard-coded false, yet
                    // this mutates `is_approved`, so it needs its own gate:
                    // moderation is a delete-class permission, not an edit.
                    ->authorize(fn (): bool => ReviewResource::canModerate())
                    ->visible(fn (): bool => ReviewResource::canModerate())
                    ->action(function (ProductReview $record): void {
                        $record->update(['is_approved' => !$record->is_approved]);

                        Notification::make()
                            ->title($record->is_approved ? 'Değerlendirme onaylandı' : 'Onay kaldırıldı')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkApprove')
                        ->label('Seçilenleri Onayla')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Seçilen değerlendirmeler onaylanacak. Devam edilsin mi?')
                        ->authorize(fn (): bool => ReviewResource::canModerate())
                        ->visible(fn (): bool => ReviewResource::canModerate())
                        ->action(function (Collection $records): void {
                            $records->each(fn (ProductReview $record) => $record->update(['is_approved' => true]));

                            Notification::make()
                                ->title(count($records) . ' değerlendirme onaylandı')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('bulkReject')
                        ->label('Seçilenleri Reddet')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Seçilen değerlendirmelerin onayı kaldırılacak. Devam edilsin mi?')
                        ->authorize(fn (): bool => ReviewResource::canModerate())
                        ->visible(fn (): bool => ReviewResource::canModerate())
                        ->action(function (Collection $records): void {
                            $records->each(fn (ProductReview $record) => $record->update(['is_approved' => false]));

                            Notification::make()
                                ->title(count($records) . ' değerlendirme reddedildi')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
