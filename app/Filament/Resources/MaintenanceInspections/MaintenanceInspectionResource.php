<?php

namespace App\Filament\Resources\MaintenanceInspections;

use App\Filament\Resources\MaintenanceInspections\Pages\ListMaintenanceInspections;
use App\Filament\Resources\MaintenanceInspections\Pages\ViewMaintenanceInspection;
use App\Filament\Resources\MaintenanceInspections\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\MaintenanceInspections\RelationManagers\LogsRelationManager;
use App\Filament\Resources\MaintenanceInspections\Schemas\MaintenanceInspectionInfolist;
use App\Filament\Resources\MaintenanceInspections\Tables\MaintenanceInspectionsTable;
use App\Models\MaintenanceInspection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Closed maintenance inspections copied from AppSheet. Read-only: AppSheet is where they are created.
 */
class MaintenanceInspectionResource extends Resource
{
    protected static ?string $model = MaintenanceInspection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Inspection';

    protected static ?string $modelLabel = 'Inspection';

    protected static ?string $pluralModelLabel = 'Inspections';

    protected static ?string $recordTitleAttribute = 'inspection_no';

    protected static ?int $navigationSort = 4;

    /**
     * Super admins see every inspection; everyone else only sees their own department's.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('equipment')->visibleTo(auth()->user());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return MaintenanceInspectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaintenanceInspectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMaintenanceInspections::route('/'),
            'view' => ViewMaintenanceInspection::route('/{record}'),
        ];
    }
}
