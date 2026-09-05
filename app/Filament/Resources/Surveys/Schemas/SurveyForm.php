<?php

namespace App\Filament\Resources\Surveys\Schemas;

use App\Models\SurveyQuestion;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SurveyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Kuruluş')
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
                    ->live()
                    ->required(),
                Select::make('product_id')
                    ->label('Ürün')
                    ->placeholder('Firma geneli (tüm ürünler)')
                    ->relationship(
                        'product',
                        'name',
                        modifyQueryUsing: fn (Builder $query, $get) => $query->where(
                            'company_id',
                            $get('company_id') ?? auth()->user()?->getActiveCompanyId()
                        )
                    )
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label('Anket Adı')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->columnSpanFull(),
                Repeater::make('questions')
                    ->label('Sorular')
                    ->relationship('questions')
                    ->schema([
                        Select::make('type')
                            ->label('Soru Tipi')
                            ->options([
                                SurveyQuestion::TYPE_SINGLE => 'Tek Seçim',
                                SurveyQuestion::TYPE_MULTIPLE => 'Çok Seçim',
                                SurveyQuestion::TYPE_TEXT => 'Serbest Metin',
                            ])
                            ->default(SurveyQuestion::TYPE_SINGLE)
                            ->live()
                            ->required(),
                        TextInput::make('label')
                            ->label('Soru')
                            ->required()
                            ->maxLength(500),
                        Textarea::make('options')
                            ->label('Seçenekler')
                            ->helperText('Her satıra bir seçenek yazın.')
                            ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : $state)
                            ->dehydrateStateUsing(fn ($state) => array_values(
                                array_filter(array_map('trim', explode("\n", (string) $state)))
                            ))
                            ->visible(fn ($get) => in_array($get('type'), [SurveyQuestion::TYPE_SINGLE, SurveyQuestion::TYPE_MULTIPLE], true)),
                        Toggle::make('is_required')
                            ->label('Zorunlu')
                            ->default(false),
                        TextInput::make('sort_order')
                            ->label('Sıra')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2)
                    ->defaultSort('sort_order')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
