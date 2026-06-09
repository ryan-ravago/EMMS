<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\CreateWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\EditWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\ListWorkOrders;
use App\Filament\Resources\WorkOrders\Pages\ViewWorkOrder;
use App\Filament\Resources\WorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\WorkOrders\RelationManagers\LogUpdatesRelationManager;
use App\Filament\Resources\WorkOrders\RelationManagers\ReportSubmissionsRelationManager;
use App\Filament\Resources\WorkOrders\Schemas\WorkOrderForm;
use App\Filament\Resources\WorkOrders\Schemas\WorkOrderInfolist;
use App\Filament\Resources\WorkOrders\Tables\WorkOrdersTable;
use App\Models\WorkOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $recordTitleAttribute = 'wo_no';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()?->hasRole('super_admin')) {
            return $query;
        }

        return $query->where('wo_dep_id', Auth::user()?->user_dep_id);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

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
            ReportSubmissionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkOrders::route('/'),
            'create' => CreateWorkOrder::route('/create'),
            'view' => ViewWorkOrder::route('/{record}'),
            'edit' => EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
