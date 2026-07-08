<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductReview;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament.product.reviews_table.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rating')
                    ->label(__('filament.product.reviews_table.rating'))
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state)),
                TextColumn::make('comment')
                    ->label(__('filament.product.reviews_table.comment'))
                    ->limit(50)
                    ->tooltip(function (ProductReview $record): ?string {
                        return $record->comment;
                    }),
                IconColumn::make('is_approved')
                    ->label(__('filament.product.reviews_table.approval'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('filament.product.reviews_table.date'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('toggleApproval')
                    ->label(fn (ProductReview $record): string => $record->is_approved ? __('filament.actions.unapprove') : __('filament.actions.approve'))
                    ->icon(fn (ProductReview $record): string => $record->is_approved ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (ProductReview $record): string => $record->is_approved ? 'warning' : 'success')
                    ->action(function (ProductReview $record): void {
                        $record->update(['is_approved' => !$record->is_approved]);

                        Notification::make()
                            ->title($record->is_approved ? __('filament.actions.approved') : __('filament.actions.unapproved'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ]);
    }
}
