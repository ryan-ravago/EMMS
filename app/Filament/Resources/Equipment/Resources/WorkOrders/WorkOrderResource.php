<?php

namespace App\Filament\Resources\Equipment\Resources\WorkOrders;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\Equipment\Resources\WorkOrders\Pages\CreateWorkOrder;
use App\Filament\Resources\Equipment\Resources\WorkOrders\Pages\EditWorkOrder;
use App\Filament\Resources\Equipment\Resources\WorkOrders\Pages\ViewWorkOrder;
use App\Filament\Resources\Equipment\Resources\WorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\Equipment\Resources\WorkOrders\RelationManagers\LogUpdatesRelationManager;
use App\Filament\Resources\Equipment\Resources\WorkOrders\RelationManagers\NotesRelationManager;
use App\Filament\Resources\Equipment\Resources\WorkOrders\Schemas\WorkOrderForm;
use App\Filament\Resources\Equipment\Resources\WorkOrders\Schemas\WorkOrderInfolist;
use App\Filament\Resources\Equipment\Resources\WorkOrders\Tables\WorkOrdersTable;
use App\Models\WorkOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationLabel = 'Work Order';

    protected static ?string $pluralLabel = 'Work Order';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $parentResource = EquipmentResource::class;

    protected static ?string $recordTitleAttribute = 'wo_no';

    public static function form(Schema $schema): Schema
    {
        return WorkOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WorkOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class,
            LogUpdatesRelationManager::class,
            NotesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateWorkOrder::route('/create'),
            'view' => ViewWorkOrder::route('/{record}'),
            'edit' => EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
