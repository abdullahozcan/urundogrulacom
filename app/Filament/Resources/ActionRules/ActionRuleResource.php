<?php

namespace App\Filament\Resources\ActionRules;

use App\Filament\Resources\ActionRules\Pages\CreateActionRule;
use App\Filament\Resources\ActionRules\Pages\EditActionRule;
use App\Filament\Resources\ActionRules\Pages\ListActionRules;
use App\Filament\Resources\ActionRules\Schemas\ActionRuleForm;
use App\Filament\Resources\ActionRules\Tables\ActionRulesTable;
use App\Models\ActionRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActionRuleResource extends Resource
{
    protected static ?string $model = ActionRule::class;

    protected static ?string $modelLabel = 'Aksiyon Kuralı';
    protected static ?string $pluralModelLabel = 'Aksiyon Kuralları';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    public static function getNavigationGroup(): ?string
    {
        return 'Ürün Yönetimi';
    }

    public static function form(Schema $schema): Schema
    {
        return ActionRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActionRulesTable::configure($table);
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

    public static function canView($record): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin() || in_array((int) $record->company_id, $user->getAccessibleCompanyIds());
    }

    public static function canEdit($record): bool
    {
        return static::canView($record);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user();
    }

    public static function canDelete($record): bool
    {
        return static::canView($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActionRules::route('/'),
            'create' => CreateActionRule::route('/create'),
            'edit' => EditActionRule::route('/{record}/edit'),
        ];
    }
}
