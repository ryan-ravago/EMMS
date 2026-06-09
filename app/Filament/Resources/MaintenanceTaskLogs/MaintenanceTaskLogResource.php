<?php

namespace App\Filament\Resources\MaintenanceTaskLogs;

use App\Filament\Resources\MaintenanceTaskLogs\Pages\CreateMaintenanceTaskLog;
use App\Filament\Resources\MaintenanceTaskLogs\Pages\EditMaintenanceTaskLog;
use App\Filament\Resources\MaintenanceTaskLogs\Pages\ListMaintenanceTaskLogs;
use App\Filament\Resources\MaintenanceTaskLogs\Pages\ViewMaintenanceTaskLog;
use App\Filament\Resources\MaintenanceTaskLogs\Schemas\MaintenanceTaskLogForm;
use App\Filament\Resources\MaintenanceTaskLogs\Schemas\MaintenanceTaskLogInfolist;
use App\Filament\Resources\MaintenanceTaskLogs\Tables\MaintenanceTaskLogsTable;
use App\Models\MaintenanceTaskLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MaintenanceTaskLogResource extends Resource
{
    protected static ?string $model = MaintenanceTaskLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return MaintenanceTaskLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MaintenanceTaskLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaintenanceTaskLogsTable::configure($table);
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
            'index' => ListMaintenanceTaskLogs::route('/'),
            'create' => CreateMaintenanceTaskLog::route('/create'),
            // 'view' => ViewMaintenanceTaskLog::route('/{record}'),
            'edit' => EditMaintenanceTaskLog::route('/{record}/edit'),
        ];
    }
}
