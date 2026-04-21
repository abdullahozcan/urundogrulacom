<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('languages.supported', ['tr' => 'Türkçe', 'en' => 'English']);
        $tabs = [];

        foreach ($locales as $code => $label) {
            $tabs[] = \Filament\Schemas\Components\Tabs\Tab::make($label)
                ->schema([
                    TextInput::make("value.{$code}")
                        ->label("Değer ({$code})")
                        ->required(fn () => $code === config('languages.default', 'tr')),
                ]);
        }

        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Anahtar (Key)')
                    ->required()
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Tabs::make('Translations')
                    ->tabs($tabs)
                    ->columnSpanFull(),
            ]);
    }
}
