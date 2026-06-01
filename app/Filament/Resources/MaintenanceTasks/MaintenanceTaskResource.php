<?php

namespace App\Filament\Resources\MaintenanceTasks;

use App\Filament\Resources\MaintenanceTasks\Pages\CreateMaintenanceTask;
use App\Filament\Resources\MaintenanceTasks\Pages\EditMaintenanceTask;
use App\Filament\Resources\MaintenanceTasks\Pages\ListMaintenanceTasks;
use App\Filament\Resources\MaintenanceTasks\Pages\ViewMaintenanceTask;
use App\Filament\Resources\MaintenanceTasks\RelationManagers\MaintenanceTaskLogsRelationManager;
use App\Filament\Resources\MaintenanceTasks\Schemas\MaintenanceTaskForm;
use App\Filament\Resources\MaintenanceTasks\Schemas\MaintenanceTaskInfolist;
use App\Filament\Resources\MaintenanceTasks\Tables\MaintenanceTasksTable;
use App\Models\MaintenanceTask;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceTaskResource extends Resource
{
    protected static ?string $model = MaintenanceTask::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // If the authenticated user is a super_admin, skip the department filter
        if (auth()->user()?->hasRole('super_admin')) {
            return $query;
        }

        // Otherwise, scope the query to the user's specific department
        return $query->where('mt_dep_id', auth()->user()->user_dep_id);
    }

    public static function form(Schema $schema): Schema
    {
        return MaintenanceTaskForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MaintenanceTaskInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaintenanceTasksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MaintenanceTaskLogsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMaintenanceTasks::route('/'),
            // 'create' => CreateMaintenanceTask::route('/create'),
            'view' => ViewMaintenanceTask::route('/{record}'),
            // 'edit' => EditMaintenanceTask::route('/{record}/edit'),
        ];
    }
}
