<?php

namespace App\Filament\Resources\AimsInspections;

use App\Filament\Resources\AimsInspections\Pages\ListAimsInspections;
use App\Filament\Resources\AimsInspections\Pages\ViewAimsInspection;
use App\Filament\Resources\AimsInspections\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\AimsInspections\RelationManagers\LogsRelationManager;
use App\Filament\Resources\AimsInspections\Schemas\AimsInspectionInfolist;
use App\Filament\Resources\AimsInspections\Tables\AimsInspectionsTable;
use App\Models\AimsInspection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Closed AIMS inspections copied from AppSheet. Read-only, and only for the AIMS department and super admins.
 */
class AimsInspectionResource extends Resource
{
    protected static ?string $model = AimsInspection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'AIMS Inspection';

    protected static ?string $modelLabel = 'AIMS Inspection';

    protected static ?string $pluralModelLabel = 'AIMS Inspections';

    protected static ?string $recordTitleAttribute = 'inspection_no';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('equipment')->visibleTo(auth()->user());
    }

    public static function canViewAny(): bool
    {
        return AimsInspection::isVisibleTo(auth()->user()) && parent::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AimsInspectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AimsInspectionsTable::configure($table);
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
            'index' => ListAimsInspections::route('/'),
            'view' => ViewAimsInspection::route('/{record}'),
        ];
    }
}
