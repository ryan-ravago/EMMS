<?php

namespace App\Filament\Resources\Equipment;

use App\Filament\Resources\Equipment\Pages\CreateEquipment;
use App\Filament\Resources\Equipment\Pages\EditEquipment;
use App\Filament\Resources\Equipment\Pages\ListEquipment;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Equipment\RelationManagers\EquipmentTaskChecklistTemplatesRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\EquipmentTasksSchedulesRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\InspectionsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\InspsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\MaintenanceTasksRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\RequestorWorkOrdersRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\WorkOrdersRelationManager;
use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Equipment\Schemas\EquipmentInfolist;
use App\Filament\Resources\Equipment\Tables\EquipmentTable;
use App\Models\AppUser;
use App\Models\Equipment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'eqm_name';

    protected static ?string $navigationLabel = 'Equipment';

    protected static ?string $modelLabel = 'Equipment';

    protected static ?string $pluralModelLabel = 'Equipment';

    protected static ?string $slug = 'equipment';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Equipment';

    // public static function getNavigationBadge(): ?string
    // {
    //     return cache()->remember('equipment_count', 60, fn() => static::getModel()::count());
    // }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return EquipmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EquipmentInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['model']);
    }

    public static function table(Table $table): Table
    {
        return EquipmentTable::configure($table);
    }

    public static function getRelations(): array
    {
        $relations = [];

        /** @var AppUser|null $user */
        $user = Auth::user();

        // Standard maintenance tabs for maintenance roles
        if ($user?->hasAnyRole(['super_admin', 'manager', 'technician'])) {
            $relations = [
                WorkOrdersRelationManager::class,
                EquipmentTaskChecklistTemplatesRelationManager::class,
                EquipmentTasksSchedulesRelationManager::class,
                InspectionsRelationManager::class,
                MaintenanceTasksRelationManager::class,
                InspsRelationManager::class
            ];
        }

        // Requestor specific tab
        if ($user instanceof AppUser && $user->hasRole('requestor')) {
            $relations[] = RequestorWorkOrdersRelationManager::class;
        }

        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipment::route('/'),
            'create' => CreateEquipment::route('/create'),
            'view' => ViewEquipment::route('/{record}'),
            'edit' => EditEquipment::route('/{record}/edit'),
        ];
    }
}
