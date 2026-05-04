<?php

namespace App\Filament\Resources\EquipmentTypes;

use App\Filament\Resources\EquipmentTypes\Pages\CreateEquipmentType;
use App\Filament\Resources\EquipmentTypes\Pages\EditEquipmentType;
use App\Filament\Resources\EquipmentTypes\Pages\ListEquipmentTypes;
use App\Filament\Resources\EquipmentTypes\Pages\ViewEquipmentType;
use App\Filament\Resources\EquipmentTypes\Schemas\EquipmentTypeForm;
use App\Filament\Resources\EquipmentTypes\Schemas\EquipmentTypeInfolist;
use App\Filament\Resources\EquipmentTypes\Tables\EquipmentTypesTable;
use App\Models\EquipmentType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EquipmentTypeResource extends Resource
{
    protected static ?string $model = EquipmentType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    public static function form(Schema $schema): Schema
    {
        return EquipmentTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EquipmentTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentTypesTable::configure($table);
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
            'index' => ListEquipmentTypes::route('/'),
            'create' => CreateEquipmentType::route('/create'),
            'view' => ViewEquipmentType::route('/{record}'),
            'edit' => EditEquipmentType::route('/{record}/edit'),
        ];
    }
}
