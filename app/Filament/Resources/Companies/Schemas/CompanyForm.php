<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('parent_id')
                    ->label('Bağlı Olduğu Firma')
                    ->relationship('parent', 'name', modifyQueryUsing: function (Builder $query) use ($isSuperAdmin, $user) {
                        if ($isSuperAdmin) {
                            return $query->whereNull('parent_id');
                        }

                        $query->whereKey($user->company_id);
                    })
                    ->searchable()
                    ->preload()
                    ->hidden(fn () => !$isSuperAdmin)
                    ->placeholder('Üst firma yok (ana firma)'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('max_sub_companies')
                    ->label('Maksimum Alt Firma Sayısı')
                    ->placeholder('Boş = sınırsız')
                    ->numeric()
                    ->minValue(0)
                    ->visible(fn () => $isSuperAdmin),
            ]);
    }
}
