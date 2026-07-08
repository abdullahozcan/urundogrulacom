<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Item;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name', modifyQueryUsing: function (Builder $query) {
                        $user = auth()->user();

                        if (!$user || $user->isSuperAdmin()) {
                            return;
                        }

                        $query->whereIn('id', $user->getAccessibleCompanyIds());
                    })
                    ->default(fn () => auth()->user()?->company_id)
                    ->visible(fn () => auth()->user()?->isSuperAdmin())
                    ->dehydrated()
                    ->required(),
                Section::make('Genel Bilgiler')
                    ->description('Ürün adı ve açıklaması')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Ürün Adı')
                            ->required(),
                        RichEditor::make('description')
                            ->label('Açıklama')
                            ->columnSpanFull(),
                    ]),
                Section::make('Künye Bilgileri')
                    ->description('Kategori, tarih, görsel ve ürün tipi')
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name', modifyQueryUsing: function (Builder $query, callable $get) {
                                $user = auth()->user();
                                $companyId = $get('company_id') ?? $user?->getActiveCompanyId();

                                if (!$companyId) {
                                    $query->whereRaw('0 = 1');
                                    return;
                                }

                                $query->where('company_id', $companyId);
                            })
                            ->searchable()
                            ->preload(),
                        DatePicker::make('expiration_date')
                            ->label('Son Kullanma Tarihi'),
                        TextInput::make('serial_number')
                            ->label('Seri Numaras&imath;'),
                        FileUpload::make('image')
                            ->image()
                            ->disk('public'),
                        Select::make('type')
                            ->label('Ürün Tipi')
                            ->options([
                                'physical' => 'Fiziksel',
                                'digital' => 'Dijital',
                            ])
                            ->default('physical')
                            ->required()
                            ->live(),
                    ]),
                Section::make('Sosyal Medya')
                    ->description('Ürünün sosyal medya hesaplar&imath; (isteğe bağl&imath;)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('website_url')
                            ->label('Web Sitesi')
                            ->url()
                            ->placeholder('https://example.com'),
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->placeholder('https://instagram.com/...'),
                        TextInput::make('facebook_url')
                            ->label('Facebook')
                            ->url()
                            ->placeholder('https://facebook.com/...'),
                        TextInput::make('twitter_url')
                            ->label('Twitter / X')
                            ->url()
                            ->placeholder('https://twitter.com/...'),
                        TextInput::make('tiktok_url')
                            ->label('TikTok')
                            ->url()
                            ->placeholder('https://tiktok.com/@...'),
                        TextInput::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url()
                            ->placeholder('https://linkedin.com/in/...'),
                    ]),
                Section::make('QR Kod Ayarlar&imath;')
                    ->description('QR kod türü ve genel QR kod değeri')
                    ->columns(2)
                    ->schema([
                        Select::make('qr_mode')
                            ->label('QR Modu')
                            ->options([
                                'unique' => 'Benzersiz (Tekil)',
                                'general' => 'Genel (Ürüne Özel, Tekil Değil)',
                            ])
                            ->default('unique')
                            ->required()
                            ->live(),
                        TextInput::make('general_qr_code')
                            ->label('Mevcut Genel QR Kodu')
                            ->visible(fn ($get) => $get('qr_mode') === 'general')
                            ->required(fn ($get) => $get('qr_mode') === 'general')
                            ->uuid()
                            ->minLength(3)
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('generate_general_qr_code')
                                    ->label('Otomatik Oluştur')
                                    ->action(fn (callable $set) => $set('general_qr_code', (string) Str::uuid()))
                            )
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail): void {
                                    if (Item::query()->where('uuid', $value)->exists()) {
                                        $fail('Bu UUID zaten kullaniliyor.');
                                    }
                                };
                            })
                            ->unique(ignoreRecord: true),
                    ]),
                Section::make('Dijital İçerik')
                    ->description('Dijital ürünler için dosya ve link ayarlar&imath;')
                    ->columns(2)
                    ->visible(fn ($get) => $get('type') === 'digital')
                    ->schema([
                        Select::make('digital_content_type')
                            ->label('İçerik Tipi')
                            ->options([
                                'pdf' => 'PDF Belgesi',
                                'video' => 'Video',
                            ])
                            ->required(fn ($get) => $get('type') === 'digital')
                            ->live(),
                        FileUpload::make('digital_file_path')
                            ->label('Dijital Dosya')
                            ->directory('digital_products')
                            ->disk('public')
                            ->live()
                            ->acceptedFileTypes(fn ($get) => $get('digital_content_type') === 'video'
                                ? ['video/mp4', 'video/quicktime']
                                : ['application/pdf'])
                            ->maxSize(51200)
                            ->disabled(fn ($get) => $get('type') === 'digital' && $get('digital_content_type') === 'video' && filled($get('youtube_url')))
                            ->required(fn ($get) => $get('type') === 'digital' && ($get('digital_content_type') !== 'video' || blank($get('youtube_url')))),
                        TextInput::make('youtube_url')
                            ->label('YouTube Linki')
                            ->placeholder('https://youtube.com/watch?v=...')
                            ->url()
                            ->visible(fn ($get) => $get('type') === 'digital' && $get('digital_content_type') === 'video')
                            ->disabled(fn ($get) => filled($get('digital_file_path')))
                            ->required(fn ($get) => $get('type') === 'digital' && $get('digital_content_type') === 'video' && blank($get('digital_file_path'))),
                    ]),
            ]);
    }
}
