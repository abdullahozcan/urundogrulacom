<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('createSubCompany')
                    ->label('Alt Firma Ekle')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->url(fn (): string => CompanyResource::getUrl('create', [
                        'parent_id' => $this->getOwnerRecord()->id,
                    ]))
                    ->visible(fn () => $this->getOwnerRecord()->canCreateSubCompany()),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->required(),
                        Toggle::make('is_active'),
                    ]),
            ]);
    }
}
