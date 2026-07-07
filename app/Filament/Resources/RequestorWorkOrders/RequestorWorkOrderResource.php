<?php

namespace App\Filament\Resources\RequestorWorkOrders;

use App\Filament\Resources\RequestorWorkOrders\Pages\CreateRequestorWorkOrder;
use App\Filament\Resources\RequestorWorkOrders\Pages\EditRequestorWorkOrder;
use App\Filament\Resources\RequestorWorkOrders\Pages\ListRequestorWorkOrders;
use App\Filament\Resources\RequestorWorkOrders\Pages\ViewRequestorWorkOrder;
use App\Filament\Resources\RequestorWorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\RequestorWorkOrders\Schemas\RequestorWorkOrderForm;
use App\Filament\Resources\RequestorWorkOrders\Schemas\RequestorWorkOrderInfolist;
use App\Filament\Resources\RequestorWorkOrders\Tables\RequestorWorkOrdersTable;
use App\Models\RequestorWorkOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RequestorWorkOrderResource extends Resource
{
    protected static ?string $model = RequestorWorkOrder::class;

    protected static ?string $slug = 'requestor-work-orders';

    public static function getNavigationLabel(): string
    {
        return auth()->user()->hasRole('super_admin') ? 'Requestor Work Order' : 'Work Order';
    }

    public static function getModelLabel(): string
    {
        return auth()->user()->hasRole('super_admin') ? 'Requestor Work Order' : 'Work Order';
    }

    public static function getPluralModelLabel(): string
    {
        return auth()->user()->hasRole('super_admin') ? 'Requestor Work Order' : 'Work Order';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static ?string $recordTitleAttribute = 'wo_no';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['equipment', 'department', 'priority', 'status', 'createdBy']);

        // Requestors see only work orders they created
        return $query->where('wo_created_by', Auth::id());
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return RequestorWorkOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RequestorWorkOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RequestorWorkOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRequestorWorkOrders::route('/'),
            'create' => CreateRequestorWorkOrder::route('/create'),
            'view' => ViewRequestorWorkOrder::route('/{record}'),
            'edit' => EditRequestorWorkOrder::route('/{record}/edit'),
        ];
    }
}
