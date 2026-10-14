<?php

namespace App\Filament\Resources\Surveys;

use App\Filament\Resources\Surveys\Pages\CreateSurvey;
use App\Filament\Resources\Surveys\Pages\EditSurvey;
use App\Filament\Resources\Surveys\Pages\ListSurveys;
use App\Filament\Resources\Surveys\Schemas\SurveyForm;
use App\Filament\Resources\Surveys\Tables\SurveysTable;
use App\Filament\Resources\Concerns\AuthorizesTenantRecords;
use App\Models\Survey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SurveyResource extends Resource
{
    use AuthorizesTenantRecords;

    protected static ?string $model = Survey::class;

    protected static ?string $modelLabel = 'Anket';
    protected static ?string $pluralModelLabel = 'Anketler';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationGroup(): ?string
    {
        return 'Ürün Yönetimi';
    }

    public static function form(Schema $schema): Schema
    {
        return SurveyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SurveysTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withCount('questions');
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn('company_id', $user->getAccessibleCompanyIds());
    }

    public static function canView($record): bool
    {
        return static::canAccessTenantOf($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSurveys::route('/'),
            'create' => CreateSurvey::route('/create'),
            'edit' => EditSurvey::route('/{record}/edit'),
        ];
    }
}
