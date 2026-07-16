<?php

namespace App\Filament\Resources\Batches\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->options(function () {
                        $user = auth()->user();
                        $query = Product::query();

                        if ($user && !$user->isSuperAdmin()) {
                            $companyIds = $user->getAccessibleCompanyIds();
                            if (empty($companyIds)) {
                                return [];
                            }
                            $query->whereIn('company_id', $companyIds);
                        }

                        return $query->get()->mapWithKeys(fn ($product) => [$product->id => $product->getName()]);
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
