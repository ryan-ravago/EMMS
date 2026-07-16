<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Categories\RelationManagers\ChildrenRelationManager;
use App\Filament\Resources\Categories\RelationManagers\ModelsRelationManager;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Categories\Schemas\CategoryInfolist;
use App\Filament\Resources\Categories\Tables\CategoriesTable;
use App\Models\EquipmentCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = EquipmentCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static ?string $recordTitleAttribute = 'eqmc_name';

    protected static ?string $navigationLabel = 'Category';

    protected static ?string $modelLabel = 'Category';

    protected static ?string $pluralModelLabel = 'Category';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Equipment Details';

    // public static function getNavigationGroup(): ?string
    // {
    //     $user = auth()->user();

    //     // 1. If Super Admin, always keep it at the top level (no group)
    //     if ($user->hasRole('super_admin')) {
    //         return null;
    //     }

    //     // 2. Count roles. If they only have 1 role (or 0), don't show a group folder.
    //     // Assuming you are using Spatie Permissions or a 'roles' relationship
    //     if ($user->roles()->count() < 2) {
    //         return null;
    //     }

    //     // 3. If they have 2+ roles, assign the group based on priority:
    //     if ($user->hasRole('manager')) {
    //         return 'Manager';
    //     }

    //     if ($user->hasRole('custodian')) {
    //         return 'Custodian';
    //     }

    //     return null;
    // }

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'view' => ViewCategory::route('/{record}'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
