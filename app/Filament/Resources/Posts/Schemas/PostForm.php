<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Helpers\UploadRules;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('languages.supported', ['tr' => 'Türkçe', 'en' => 'English']);
        $tabs = [];

        foreach ($locales as $code => $label) {
            $tabs[] = \Filament\Schemas\Components\Tabs\Tab::make($label)
                ->schema([
                    TextInput::make("title.{$code}")
                        ->label("Başlık ({$code})")
                        ->required(fn () => $code === config('languages.default', 'tr')),
                    TextInput::make("slug.{$code}")
                        ->label("Slug ({$code})")
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
                UploadRules::hardenImage(
                    FileUpload::make('image')
                        ->label('Kapak Görseli')
                        ->directory('blog')
                        ->disk('public'),
                )->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('Yayınlandı mı?')
                    ->default(false),
                Toggle::make('show_on_homepage')
                    ->label('Anasayfada Göster')
                    ->default(false),
            ]);
    }
}
