<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Filament\Resources\Settings\Tables\SettingsTable;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

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
     * at its `/{id}/edit` URL and act on it. Settings include
     * `custom_head_scripts`, which is injected raw into every public page.
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
        return SettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SettingsTable::configure($table);
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
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}
