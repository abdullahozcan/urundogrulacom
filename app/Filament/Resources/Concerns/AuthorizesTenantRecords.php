<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared authorization for tenant-scoped Filament resources.
 *
 * Filament fails OPEN: when a resource has no `can*()` override and the model
 * has no policy, the action is implicitly allowed for every authenticated
 * panel user. These resources authorise through the Resource class rather than
 * through policies, so every ability has to be declared explicitly.
 *
 * Each resource using this trait must implement `canView($record)`; the
 * `canEdit`/`canDelete` defaults delegate to it.
 */
trait AuthorizesTenantRecords
{
    /**
     * Whether the resource is reachable at all. Records themselves are
     * narrowed by `getEloquentQuery()`, so this only decides whether the
     * resource appears in navigation and its list route resolves.
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->isSuperAdmin() || $user->company_id !== null);
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    /**
     * Bulk delete stays available to tenant admins. `DeleteBulkAction` still
     * re-checks `canDelete()` for every selected record, so records outside
     * the editor's accessible companies cannot be removed.
     */
    public static function canDeleteAny(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canView($record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::canView($record);
    }

    /**
     * Resolve the owning company of a record that has no `company_id` column
     * of its own (Item -> Batch -> Product, Scan -> Item -> Batch -> Product).
     */
    protected static function tenantCompanyIdOf(Model $record): ?int
    {
        return match (true) {
            isset($record->company_id) => $record->company_id,
            isset($record->product) => $record->product?->company_id,
            isset($record->batch) => $record->batch?->product?->company_id,
            isset($record->item) => $record->item?->batch?->product?->company_id,
            default => null,
        };
    }

    /**
     * Tenant check for records that are reachable only through a relation.
     */
    protected static function canAccessTenantOf(Model $record): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $companyId = static::tenantCompanyIdOf($record);

        if ($companyId === null) {
            // Fail closed: an unresolvable owner must not mean "all tenants".
            return false;
        }

        return in_array((int) $companyId, $user->getAccessibleCompanyIds(), true);
    }
}