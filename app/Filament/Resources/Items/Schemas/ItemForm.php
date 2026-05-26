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
                    ->relationship('batch', 'id')
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
