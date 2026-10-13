<?php

namespace App\Filament\Resources\Companies;

use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Filament\Resources\Companies\RelationManagers\ChildrenRelationManager;
use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Resources\Companies\Tables\CompaniesTable;
use App\Models\Company;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    
    protected static ?string $modelLabel = 'Firma';
    protected static ?string $pluralModelLabel = 'Firmalar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        return $user->isSuperAdmin() ? 'Sistem' : 'Firma Yönetimi';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $company = Company::find($user->company_id);

        return $company && is_null($company->parent_id);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $company = Company::find($user->company_id);

        return $company && is_null($company->parent_id);
    }

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('id', $user->company_id)
              ->orWhere('parent_id', $user->company_id);
        });
    }

    /**
     * A root-company admin may read its own record and its children, and may
     * manage its own children through the relation manager. Everything that
     * would restructure the hierarchy (`max_sub_companies`, `parent_id`) is
     * already hidden from the form for non super admins, and delete stays
     * super-admin only so a tenant cannot orphan itself or its children.
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $company = Company::find($user->company_id);

        return $company && $company->isRoot();
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->company_id) {
            return false;
        }

        return (int) $record->id === (int) $user->company_id
            || (int) $record->parent_id === (int) $user->company_id;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // A root company admin may edit only their own (root) record, which
        // is what the children relation manager hangs off. Child records are
        // managed from that relation manager, never as a standalone record.
        return (int) $record->id === (int) $user->company_id
            && is_null($record->parent_id);
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            return false;
        }

        // A root company must not be deletable from the panel: it would
        // orphan every child company and user underneath it.
        return !is_null($record->parent_id) || !$record->users()->exists();
    }

    public static function canDeleteAny(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canRestore(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canForceDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    protected static function isSuperAdmin(): bool
    {
        $user = auth()->user();

        return $user && $user->isSuperAdmin();
    }

    public static function getRelations(): array
    {
        return [
            ChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
