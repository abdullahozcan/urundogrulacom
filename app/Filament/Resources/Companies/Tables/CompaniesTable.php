<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('parent.name')
                    ->label('Bağlı Olduğu Firma')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('children_count')
                    ->label('Alt Firma Sayısı')
                    ->counts('children')
                    ->sortable(),
                TextColumn::make('max_sub_companies')
                    ->label('Alt Firma Limiti')
                    ->formatStateUsing(fn ($state) => $state === null ? 'Sınırsız' : $state)
                    ->visible(fn () => $isSuperAdmin),
                IconColumn::make('is_active')
                    ->boolean(),
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
