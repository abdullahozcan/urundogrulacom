<?php

namespace App\Filament\Resources\Batches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name', modifyQueryUsing: function (Builder $query) {
                        $user = auth()->user();
                        $companyId = $user?->company_id;

                        if (!$user || $user->isSuperAdmin()) {
                            return;
                        }

                        if (!$companyId) {
                            $query->whereRaw('0 = 1');
                            return;
                        }

                        $query->where('company_id', $companyId);
                    })
                    ->required(),
                TextInput::make('batch_number')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
            ]);
    }
}
