<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Filament\Resources\Partners\Pages\EditPartner;
use App\Filament\Resources\Partners\Pages\ListPartners;
use App\Filament\Resources\Partners\Schemas\PartnerForm;
use App\Filament\Resources\Partners\Tables\PartnersTable;
use App\Models\Partner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function canViewAny(): bool
    {
        return static::isSuperAdminUser();
    }

    /**
     * `canViewAny()` only hides the menu item and gates the list route. This
     * resource has no tenant scoping in `getEloquentQuery()`, so without the
     * record-level checks a company admin could reach a single record directly
     * at its `/{id}/edit` URL and act on it.
     */
    public static function canView(Model $record): bool
    {
        return static::isSuperAdminUser();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isSuperAdminUser();
    }

    public static function canDelete(Model $record): bool
    {
        return static::isSuperAdminUser();
    }

    public static function canDeleteAny(): bool
    {
        return static::isSuperAdminUser();
    }

    public static function canCreate(): bool
    {
        return static::isSuperAdminUser();
    }

    protected static function isSuperAdminUser(): bool
    {
        $user = auth()->user();

        return (bool) $user?->isSuperAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'edit' => EditPartner::route('/{record}/edit'),
        ];
    }
}
