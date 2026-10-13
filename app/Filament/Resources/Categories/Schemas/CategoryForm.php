<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name', modifyQueryUsing: function (Builder $query) {
                        $user = auth()->user();

                        if (!$user || $user->isSuperAdmin()) {
                            return;
                        }

                        $query->whereIn('id', $user->getAccessibleCompanyIds());
                    })
                    ->default(fn () => auth()->user()?->getActiveCompanyId())
                    ->visible(fn () => auth()->user()?->isSuperAdmin())
                    ->dehydrated()
                    ->required(),
                Select::make('parent_id')
                    ->label('Üst Kategori')
                    ->relationship('parent', 'name', modifyQueryUsing: function (Builder $query) {
                        $user = auth()->user();

                        if (!$user || $user->isSuperAdmin()) {
                            $query->whereNull('parent_id');
                            return;
                        }

                        // Must be scoped to the *same* company as the category
                        // being edited, not merely to "any accessible
                        // company", otherwise a parent admin can graft a child
                        // company's root category under its own tree and pull
                        // foreign category names into this tenant's screens.
                        $companyId = auth()->user()?->getActiveCompanyId();

                        if (!$companyId) {
                            $query->whereRaw('0 = 1');
                            return;
                        }

                        $query->where('company_id', $companyId)->whereNull('parent_id');
                    })
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (blank($get('slug')) && filled($state)) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif mi?')
                    ->default(true),
            ]);
    }
}

