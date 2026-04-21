<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('languages.supported', ['tr' => 'Türkçe', 'en' => 'English']);
        $tabs = [];

        foreach ($locales as $code => $label) {
            $tabs[] = \Filament\Schemas\Components\Tabs\Tab::make($label)
                ->schema([
                    \Filament\Forms\Components\TextInput::make("title.{$code}")
                        ->label("Başlık ({$code})")
                        ->required(fn () => $code === config('languages.default', 'tr')),
                    \Filament\Forms\Components\TextInput::make("slug.{$code}")
                        ->label("URL/Slug ({$code})")
                        ->required(fn () => $code === config('languages.default', 'tr')),
                    \Filament\Forms\Components\RichEditor::make("content.{$code}")
                        ->label("İçerik ({$code})")
                        ->required(fn () => $code === config('languages.default', 'tr')),
                ]);
        }

        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('Translations')
                    ->tabs($tabs)
                    ->columnSpanFull(),
                \Filament\Forms\Components\Toggle::make('is_published')
                    ->label('Yayınlandı mı?')
                    ->default(false),
            ]);
    }
}
