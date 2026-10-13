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

    /**
     * A root company admin may manage its own children. Relation managers do
     * not inherit the owner resource's authorization, so this is declared
     * explicitly rather than assumed.
     */
    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return CompanyResource::canView($ownerRecord);
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
                    ->authorize(fn (): bool => CompanyResource::canCreate()
                        && $this->getOwnerRecord()->canCreateSubCompany())
                    ->visible(fn (): bool => CompanyResource::canCreate()
                        && $this->getOwnerRecord()->canCreateSubCompany()),
            ])
            ->recordActions([
                EditAction::make()
                    ->authorize(fn (\Illuminate\Database\Eloquent\Model $record): bool => CompanyResource::canView($record))
                    ->visible(fn (\Illuminate\Database\Eloquent\Model $record): bool => CompanyResource::canView($record))
                    ->form([
                        TextInput::make('name')
                            ->required(),
                        Toggle::make('is_active'),
                    ]),
            ]);
    }
}
