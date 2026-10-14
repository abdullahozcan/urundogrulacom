<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

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
     * `canViewAny()` only hides the menu item and gates the list route. These
     * resources have no tenant scoping in `getEloquentQuery()`, so without the
     * record-level checks a company admin could reach a single record directly
     * at `/admin/posts/{id}/edit` and act on it.
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
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
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
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
