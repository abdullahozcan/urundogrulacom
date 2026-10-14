<?php

namespace App\Filament\Resources\Scans;

use App\Filament\Resources\Scans\Pages\CreateScan;
use App\Filament\Resources\Scans\Pages\EditScan;
use App\Filament\Resources\Scans\Pages\ListScans;
use App\Filament\Resources\Scans\Schemas\ScanForm;
use App\Filament\Resources\Scans\Tables\ScansTable;
use App\Filament\Resources\Concerns\AuthorizesTenantRecords;
use App\Models\Scan;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScanResource extends Resource
{
    use AuthorizesTenantRecords;

    protected static ?string $model = Scan::class;
    
    protected static ?string $modelLabel = 'Okuma (Scan)';
    protected static ?string $pluralModelLabel = 'Okumalar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'Raporlar';
    }

    public static function form(Schema $schema): Schema
    {
        return ScanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScansTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereHas('item.batch.product', function (Builder $q) use ($user) {
            $q->whereIn('company_id', $user->getAccessibleCompanyIds());
        });
    }

    /**
     * Scan has no `company_id` of its own; ownership is resolved through
     * `item.batch.product`. Fails closed when that chain cannot be resolved.
     */
    public static function canView($record): bool
    {
        return static::canAccessTenantOf($record);
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
            'index' => ListScans::route('/'),
            'create' => CreateScan::route('/create'),
            'edit' => EditScan::route('/{record}/edit'),
        ];
    }
}
