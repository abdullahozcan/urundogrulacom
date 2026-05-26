<?php

namespace App\Filament\Resources\Scans\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;

class ScanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->relationship('item', 'id', modifyQueryUsing: function (Builder $query) {
                        $user = auth()->user();

                        if (!$user || $user->isSuperAdmin()) {
                            return;
                        }

                        $query->whereHas('batch.product', function (Builder $q) use ($user) {
                            $q->where('company_id', $user->company_id);
                        });
                    })
                    ->required(),
                TextInput::make('ip_address'),
                TextInput::make('lat')
                    ->numeric(),
                TextInput::make('lng')
                    ->numeric(),
                TextInput::make('browser'),
                TextInput::make('device_type'),
                DateTimePicker::make('scanned_at')
                    ->required(),
            ]);
    }
}
