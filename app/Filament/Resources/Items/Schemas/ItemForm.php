<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('batch_id')
                    ->relationship('batch', 'id', modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query) {
                        // Without this the dropdown enumerated every batch in
                        // every tenant. It currently only stays safe because
                        // the field is disabled and therefore not dehydrated;
                        // scoping it explicitly makes that structural rather
                        // than incidental.
                        $user = auth()->user();

                        if (!$user || $user->isSuperAdmin()) {
                            return;
                        }

                        $query->whereHas('product', function ($q) use ($user) {
                            $q->whereIn('company_id', $user->getAccessibleCompanyIds());
                        });
                    })
                    ->required()
                    ->disabled(),
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->uuid()
                    ->unique(ignoreRecord: true)
                    ->rule(function () {
                        return function (string $attribute, $value, \Closure $fail): void {
                            if (Product::query()->where('general_qr_code', $value)->exists()) {
                                $fail('Bu UUID zaten kullaniliyor.');
                            }
                        };
                    })
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->suffixAction(
                        Action::make('generate_uuid')
                            ->label('Otomatik Oluştur')
                            ->action(fn (callable $set) => $set('uuid', (string) Str::uuid()))
                            ->visible(fn (string $operation): bool => $operation === 'create')
                    ),
                Toggle::make('is_scanned')
                    ->label('Okutuldu mu?')
                    ->disabled(),
                \Filament\Forms\Components\Placeholder::make('scan_count')
                    ->label('Toplam Okutulma Sayısı')
                    ->content(fn ($record) => $record ? $record->scans()->count() . ' kez' : '-'),
            ]);
    }
}
