<?php

namespace App\Filament\Resources\TechnicianWorkOrders;

use App\Filament\Resources\TechnicianWorkOrders\Pages\CreateTechnicianWorkOrder;
use App\Filament\Resources\TechnicianWorkOrders\Pages\EditTechnicianWorkOrder;
use App\Filament\Resources\TechnicianWorkOrders\Pages\ListTechnicianWorkOrders;
use App\Filament\Resources\TechnicianWorkOrders\Pages\ViewTechnicianWorkOrder;
use App\Filament\Resources\TechnicianWorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\TechnicianWorkOrders\RelationManagers\LogUpdatesRelationManager;
use App\Filament\Resources\TechnicianWorkOrders\RelationManagers\ReportSubmissionsRelationManager;
use App\Filament\Resources\TechnicianWorkOrders\Schemas\TechnicianWorkOrderForm;
use App\Filament\Resources\TechnicianWorkOrders\Schemas\TechnicianWorkOrderInfolist;
use App\Filament\Resources\TechnicianWorkOrders\Tables\TechnicianWorkOrdersTable;
use App\Models\TechnicianWorkOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TechnicianWorkOrderResource extends Resource
{
    protected static ?string $model = TechnicianWorkOrder::class;
    protected static ?string $navigationLabel = 'Technician Work Orders';
    protected static ?string $slug = 'technician-work-orders';
    protected static ?string $modelLabel = 'Technician Work Order';
    protected static ?string $pluralModelLabel = 'Technician Work Orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'wo_no';

    // public static function canAccess(): bool
    // {
    //     return auth()->user()->hasRole('technician');
    // }

    public static function form(Schema $schema): Schema
    {
        return TechnicianWorkOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TechnicianWorkOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechnicianWorkOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class,
            LogUpdatesRelationManager::class,
            ReportSubmissionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicianWorkOrders::route('/'),
            'create' => CreateTechnicianWorkOrder::route('/create'),
            'view' => ViewTechnicianWorkOrder::route('/{record}'),
            'edit' => EditTechnicianWorkOrder::route('/{record}/edit'),
        ];
    }
}
