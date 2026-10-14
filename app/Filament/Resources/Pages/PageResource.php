<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Filament\Resources\Pages\Tables\PagesTable;
use App\Models\Page;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

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
        return PageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagesTable::configure($table);
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
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
