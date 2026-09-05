<?php

namespace App\Filament\Resources\ActionRules\Schemas;

use App\Models\ActionRule;
use App\Models\Survey;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ActionRuleForm
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
                        modifyQueryUsing: function (Builder $query, $get) {
                            $companyId = $get('company_id') ?? auth()->user()?->getActiveCompanyId();

                            $query->where('company_id', $companyId);
                        }
                    )
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label('Kural Adı')
                    ->required()
                    ->maxLength(255),
                Select::make('trigger_type')
                    ->label('Tetikleyici')
                    ->options([
                        ActionRule::TRIGGER_SCAN_COUNT => 'Tarama sayısı',
                        ActionRule::TRIGGER_FIRST_SCAN => 'İlk tarama',
                        ActionRule::TRIGGER_DATE_RANGE => 'Tarih aralığı',
                        ActionRule::TRIGGER_ITEM_REVISIT => 'Yeniden okutma',
                    ])
                    ->live()
                    ->default(ActionRule::TRIGGER_SCAN_COUNT)
                    ->required(),
                TextInput::make('config.count')
                    ->label('Tarama Sayısı')
                    ->numeric()
                    ->minValue(1)
                    ->visible(fn ($get) => $get('trigger_type') === ActionRule::TRIGGER_SCAN_COUNT)
                    ->required(fn ($get) => $get('trigger_type') === ActionRule::TRIGGER_SCAN_COUNT),
                Select::make('config.scope')
                    ->label('Kapsam')
                    ->options([
                        'item' => 'Tekil ürün (item)',
                        'product' => 'Ürünün tüm taramaları',
                        'company' => 'Firmanın tüm taramaları',
                    ])
                    ->default('item')
                    ->visible(fn ($get) => $get('trigger_type') === ActionRule::TRIGGER_SCAN_COUNT),
                TextInput::make('config.after_days')
                    ->label('Kaç gün sonra')
                    ->numeric()
                    ->minValue(1)
                    ->default(7)
                    ->visible(fn ($get) => $get('trigger_type') === ActionRule::TRIGGER_ITEM_REVISIT)
                    ->required(fn ($get) => $get('trigger_type') === ActionRule::TRIGGER_ITEM_REVISIT),
                Select::make('action_type')
                    ->label('Aksiyon')
                    ->options([
                        ActionRule::ACTION_SHOW_CONTENT => 'İçerik göster',
                        ActionRule::ACTION_REDIRECT => 'Yönlendir',
                        ActionRule::ACTION_WEBHOOK => 'Webhook çağır',
                        ActionRule::ACTION_NOTIFY_ADMIN => 'Yöneticiye bildir',
                        ActionRule::ACTION_SHOW_SURVEY => 'Anket göster',
                    ])
                    ->live()
                    ->default(ActionRule::ACTION_SHOW_CONTENT)
                    ->required(),
                Select::make('payload.survey_id')
                    ->label('Anket')
                    ->options(function ($get) {
                        $companyId = $get('company_id') ?? auth()->user()?->getActiveCompanyId();

                        if (!$companyId) {
                            return [];
                        }

                        return Survey::query()
                            ->where('company_id', $companyId)
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->visible(fn ($get) => $get('action_type') === ActionRule::ACTION_SHOW_SURVEY)
                    ->required(fn ($get) => $get('action_type') === ActionRule::ACTION_SHOW_SURVEY),
                TextInput::make('payload.title')
                    ->label('Başlık')
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('action_type') === ActionRule::ACTION_SHOW_CONTENT),
                Textarea::make('payload.body')
                    ->label('İçerik')
                    ->maxLength(2000)
                    ->visible(fn ($get) => $get('action_type') === ActionRule::ACTION_SHOW_CONTENT)
                    ->required(fn ($get) => $get('action_type') === ActionRule::ACTION_SHOW_CONTENT),
                Select::make('payload.style')
                    ->label('Stil')
                    ->options([
                        'info' => 'Bilgi',
                        'warning' => 'Uyarı',
                    ])
                    ->default('info')
                    ->visible(fn ($get) => $get('action_type') === ActionRule::ACTION_SHOW_CONTENT),
                TextInput::make('payload.url')
                    ->label('URL')
                    ->url()
                    ->maxLength(2000)
                    ->visible(fn ($get) => in_array($get('action_type'), [ActionRule::ACTION_REDIRECT, ActionRule::ACTION_WEBHOOK], true))
                    ->required(fn ($get) => in_array($get('action_type'), [ActionRule::ACTION_REDIRECT, ActionRule::ACTION_WEBHOOK], true)),
                TextInput::make('payload.secret')
                    ->label('Webhook İmza Anahtarı')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('action_type') === ActionRule::ACTION_WEBHOOK),
                TextInput::make('payload.email')
                    ->label('Bildirim E-postası')
                    ->email()
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('action_type') === ActionRule::ACTION_NOTIFY_ADMIN)
                    ->required(fn ($get) => $get('action_type') === ActionRule::ACTION_NOTIFY_ADMIN),
                TextInput::make('payload.subject')
                    ->label('E-posta Konusu')
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('action_type') === ActionRule::ACTION_NOTIFY_ADMIN),
                Textarea::make('payload.message')
                    ->label('E-posta İçeriği')
                    ->maxLength(2000)
                    ->visible(fn ($get) => $get('action_type') === ActionRule::ACTION_NOTIFY_ADMIN),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                TextInput::make('priority')
                    ->label('Öncelik')
                    ->numeric()
                    ->default(0)
                    ->helperText('Küçük değer önce çalışır.'),
                TextInput::make('max_executions')
                    ->label('Maksimum Yürütme')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText('Boş bırakılırsa sınırsız.'),
                DateTimePicker::make('starts_at')
                    ->label('Geçerlilik Başlangıcı')
                    ->nullable(),
                DateTimePicker::make('ends_at')
                    ->label('Geçerlilik Bitişi')
                    ->nullable()
                    ->after('starts_at'),
            ])
            ->columns(2);
    }
}
