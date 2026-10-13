<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use App\Models\ProductReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $modelLabel = 'Değerlendirme';
    protected static ?string $pluralModelLabel = 'Değerlendirme Moderasyonu';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        $query = ProductReview::query()->where('is_approved', false);

        if ($user && !$user->isSuperAdmin()) {
            $query->whereHas('product', fn (Builder $q) => $q->whereIn('company_id', $user->getAccessibleCompanyIds()));
        }

        $count = $query->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Ürün Yönetimi';
    }

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('product');
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereHas('product', function (Builder $q) use ($user) {
            $q->whereIn('company_id', $user->getAccessibleCompanyIds());
        });
    }

    public static function canView($record): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $productCompanyId = $record->product?->company_id;

        return $productCompanyId !== null && in_array((int) $productCompanyId, $user->getAccessibleCompanyIds());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return static::canView($record);
    }

    /**
     * Approving/rejecting a review mutates `is_approved` even though
     * `canEdit()` is false, so it gets its own gate. Moderation is limited to
     * super admins; tenant admins can read reviews and delete them.
     */
    public static function canModerate(): bool
    {
        $user = auth()->user();

        return (bool) $user?->isSuperAdmin();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
        ];
    }
}
