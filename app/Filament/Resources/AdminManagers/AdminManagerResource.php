<?php

namespace App\Filament\Resources\AdminManagers;

use App\Filament\Resources\AdminManagers\Pages\CreateAdminManager;
use App\Filament\Resources\AdminManagers\Pages\EditAdminManager;
use App\Filament\Resources\AdminManagers\Pages\ListAdminManagers;
use App\Filament\Resources\AdminManagers\Pages\ViewAdminManager;
use App\Filament\Resources\AdminManagers\Schemas\AdminManagerForm;
use App\Filament\Resources\AdminManagers\Schemas\AdminManagerInfolist;
use App\Filament\Resources\AdminManagers\Tables\AdminManagersTable;
use App\Models\AdminManager;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminManagerResource extends Resource
{
    protected static ?string $model = AdminManager::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Admin Manager';

    protected static ?string $modelLabel = 'Admin Manager';

    protected static ?string $pluralModelLabel = 'Admin Managers';

    protected static ?string $slug = 'admin-managers';

    protected static ?string $recordTitleAttribute = 'user_fname';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Super Admin';
    }

    public static function form(Schema $schema): Schema
    {
        return AdminManagerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdminManagerInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['department', 'roles'])
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', 'admin_manager'));
    }

    public static function table(Table $table): Table
    {
        return AdminManagersTable::configure($table);
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
            'index' => ListAdminManagers::route('/'),
            'create' => CreateAdminManager::route('/create'),
            'view' => ViewAdminManager::route('/{record}'),
            'edit' => EditAdminManager::route('/{record}/edit'),
        ];
    }
}
