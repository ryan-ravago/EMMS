<?php

namespace App\Filament\Resources\EquipmentUnitCategories;

use App\Filament\Resources\EquipmentUnitCategories\Pages\CreateEquipmentUnitCategory;
use App\Filament\Resources\EquipmentUnitCategories\Pages\EditEquipmentUnitCategory;
use App\Filament\Resources\EquipmentUnitCategories\Pages\ListEquipmentUnitCategories;
use App\Filament\Resources\EquipmentUnitCategories\Schemas\EquipmentUnitCategoryForm;
use App\Filament\Resources\EquipmentUnitCategories\Tables\EquipmentUnitCategoriesTable;
use App\Models\EquipmentUnitCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EquipmentUnitCategoryResource extends Resource
{
    protected static ?string $model = EquipmentUnitCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return EquipmentUnitCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentUnitCategoriesTable::configure($table);
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
            'index' => ListEquipmentUnitCategories::route('/'),
            'create' => CreateEquipmentUnitCategory::route('/create'),
            'edit' => EditEquipmentUnitCategory::route('/{record}/edit'),
        ];
    }
}
