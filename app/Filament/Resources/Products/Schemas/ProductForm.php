<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Item;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name', modifyQueryUsing: function (Builder $query) {
                        $user = auth()->user();

                        if (!$user || $user->isSuperAdmin()) {
                            return;
                        }

                        $query->whereKey($user->company_id);
                    })
                    ->default(fn () => auth()->user()?->company_id)
                    ->visible(fn () => auth()->user()?->isSuperAdmin())
                    ->dehydrated()
                    ->required(),
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name', modifyQueryUsing: function (Builder $query, callable $get) {
                        $user = auth()->user();
                        $companyId = $get('company_id') ?? $user?->company_id;

                        if ($user && !$user->isSuperAdmin()) {
                            $query->where('company_id', $user->company_id);
                            return;
                        }

                        if ($companyId) {
                            $query->where('company_id', $companyId);
                        }
                    })
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                Select::make('type')
                    ->label('Ürün Tipi')
                    ->options([
                        'physical' => 'Fiziksel',
                        'digital' => 'Dijital',
                    ])
                    ->default('physical')
                    ->required()
                    ->live(),
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
                Select::make('digital_content_type')
                    ->label('İçerik Tipi')
                    ->options([
                        'pdf' => 'PDF Belgesi',
                        'video' => 'Video',
                    ])
                    ->visible(fn ($get) => $get('type') === 'digital')
                    ->required(fn ($get) => $get('type') === 'digital')
                    ->live(),
                FileUpload::make('digital_file_path')
                    ->label('Dijital Dosya')
                    ->directory('digital_products')
                    ->acceptedFileTypes(['application/pdf', 'video/mp4', 'video/quicktime'])
                    ->maxSize(51200) // 50MB
                    ->visible(fn ($get) => $get('type') === 'digital')
                    ->required(fn ($get) => $get('type') === 'digital'),
            ]);
    }
}
