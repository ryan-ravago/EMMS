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
use Illuminate\Support\Facades\Auth;

class TechnicianWorkOrderResource extends Resource
{
    protected static ?string $model = TechnicianWorkOrder::class;

    protected static ?string $slug = 'technician-work-orders';

    public static function getNavigationLabel(): string
    {
        return Auth::user()?->hasRole('technician') ? 'Work Order' : 'Technician Work Order';
    }

    public static function getLabel(): ?string
    {
        return Auth::user()?->hasRole('technician') ? 'Work Order' : 'Technician Work Order';
    }

    public static function getPluralLabel(): ?string
    {
        return Auth::user()?->hasRole('technician') ? 'Work Order' : 'Technician Work Order';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'wo_no';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['equipment', 'department', 'priority', 'status', 'workers', 'createdBy']);

        if (Auth::user()?->hasRole('super_admin')) {
            return $query;
        }

        $query->where('wo_dep_id', Auth::user()?->user_dep_id);

        // Moved from TechnicianWorkOrder::booted()
        $query->whereHas('workers', fn ($q) => $q->where('user_id', Auth::id()));

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

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
            ReportSubmissionsRelationManager::class,
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
