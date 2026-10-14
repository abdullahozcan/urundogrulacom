<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\RelationManagers\BatchesRelationManager;
use App\Filament\Resources\Products\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Products\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\Products\RelationManagers\ScansRelationManager;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Filament\Resources\Concerns\AuthorizesTenantRecords;
use App\Models\Product;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use AuthorizesTenantRecords;

    protected static ?string $model = Product::class;
    
    protected static ?string $modelLabel = 'Ürün';
    protected static ?string $pluralModelLabel = 'Ürünler';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.product.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.product.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.product.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ürün Bilgileri')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Ürün Adı')
                            ->state(fn (Product $record) => $record->getName())
                            ->weight('bold'),
                        TextEntry::make('company.name')
                            ->label('Kuruluş'),
                        TextEntry::make('category.name')
                            ->label('Kategori')
                            ->state(fn (Product $record) => $record->category?->name ?? '-')
                            ->placeholder('-'),
                        TextEntry::make('type')
                            ->label('Tip')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => $state === 'digital' ? 'Dijital' : 'Fiziksel'),
                        TextEntry::make('qr_mode')
                            ->label('QR Modu')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => $state === 'general' ? 'Genel' : 'Tekil'),
                        TextEntry::make('general_qr_code')
                            ->label('Genel QR Kodu')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('serial_number')
                            ->label('Seri Numarası')
                            ->placeholder('-'),
                        TextEntry::make('expiration_date')
                            ->label('Son Kullanma / Garanti')
                            ->date('d.m.Y')
                            ->placeholder('-'),
                        TextEntry::make('redirect_url')
                            ->label('Yönlendirme URL')
                            ->url(fn (Product $record) => $record->redirect_url, shouldOpenInNewTab: true)
                            ->limit(40)
                            ->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('Özet İstatistik')
                    ->schema([
                        TextEntry::make('items_count')
                            ->label('Item Sayısı')
                            ->state(fn (Product $record) => $record->batches()->withCount('items')->get()->sum('items_count')),
                        TextEntry::make('scans_count')
                            ->label('Toplam Tarama')
                            ->state(fn (Product $record) => \App\Models\Scan::query()
                                ->whereHas('item.batch', fn (Builder $q) => $q->where('product_id', $record->id))
                                ->count()),
                        TextEntry::make('reviews_avg')
                            ->label('Ortalama Puan')
                            ->state(fn (Product $record) => $record->reviews()->where('is_approved', true)->avg('rating') ?? '-'),
                        TextEntry::make('reviews_count')
                            ->label('Değerlendirme')
                            ->state(fn (Product $record) => $record->reviews()->count()),
                        TextEntry::make('documents_count')
                            ->label('Belge Sayısı')
                            ->state(fn (Product $record) => $record->documents()->count()),
                        ImageEntry::make('image')
                            ->label('Ürün Görseli')
                            ->hidden(fn (Product $record) => blank($record->image))
                            ->columnSpanFull(),
                    ])
                    ->columns(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn('company_id', $user->getAccessibleCompanyIds());
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        // Records themselves are scoped by `getEloquentQuery()`; this only
        // decides whether the resource is reachable at all.
        return (bool) $user && ($user->isSuperAdmin() || $user->company_id !== null);
    }

    public static function canView($record): bool
    {
        return static::canAccessTenantOf($record);
    }

    public static function getRelations(): array
    {
        return [
            BatchesRelationManager::class,
            ScansRelationManager::class,
            DocumentsRelationManager::class,
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
