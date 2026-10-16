<?php

namespace App\Filament\Resources\WorkOrderTypes;

use App\Filament\Resources\WorkOrderTypes\Pages\CreateWorkOrderType;
use App\Filament\Resources\WorkOrderTypes\Pages\EditWorkOrderType;
use App\Filament\Resources\WorkOrderTypes\Pages\ListWorkOrderTypes;
use App\Filament\Resources\WorkOrderTypes\Pages\ViewWorkOrderType;
use App\Filament\Resources\WorkOrderTypes\Schemas\WorkOrderTypeForm;
use App\Filament\Resources\WorkOrderTypes\Schemas\WorkOrderTypeInfolist;
use App\Filament\Resources\WorkOrderTypes\Tables\WorkOrderTypesTable;
use App\Models\WorkOrderType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkOrderTypeResource extends Resource
{
    protected static ?string $model = WorkOrderType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'type';

    protected static ?string $navigationLabel = 'Work Order Types';

    protected static ?string $modelLabel = 'Work Order Type';

    protected static ?string $pluralModelLabel = 'Work Order Types';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Super Admin';
    }

    public static function form(Schema $schema): Schema
    {
        return WorkOrderTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WorkOrderTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkOrderTypesTable::configure($table);
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
            'index' => ListWorkOrderTypes::route('/'),
            'create' => CreateWorkOrderType::route('/create'),
            'view' => ViewWorkOrderType::route('/{record}'),
            'edit' => EditWorkOrderType::route('/{record}/edit'),
        ];
    }
}
