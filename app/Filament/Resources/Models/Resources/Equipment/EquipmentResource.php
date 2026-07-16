<?php

namespace App\Filament\Resources\Models\Resources\Equipment;

use App\Filament\Resources\Models\ModelResource;
use App\Filament\Resources\Models\Resources\Equipment\Pages\CreateEquipment;
use App\Filament\Resources\Models\Resources\Equipment\Pages\EditEquipment;
use App\Filament\Resources\Models\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Models\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Models\Resources\Equipment\Schemas\EquipmentInfolist;
use App\Filament\Resources\Models\Resources\Equipment\Tables\EquipmentTable;
use App\Models\Equipment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = ModelResource::class;

    protected static ?string $recordTitleAttribute = 'eqm_name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['equipmentModel', 'type', 'brand']);
    }

    public static function form(Schema $schema): Schema
    {
        return EquipmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EquipmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentTable::configure($table);
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
            'create' => CreateEquipment::route('/create'),
            'view' => ViewEquipment::route('/{record}'),
            'edit' => EditEquipment::route('/{record}/edit'),
        ];
    }
}
