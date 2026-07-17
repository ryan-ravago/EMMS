<?php

namespace App\Filament\Resources\WorkOrderNotes;

use App\Filament\Resources\WorkOrderNotes\Pages\CreateWorkOrderNote;
use App\Filament\Resources\WorkOrderNotes\Pages\EditWorkOrderNote;
use App\Filament\Resources\WorkOrderNotes\Pages\ListWorkOrderNotes;
use App\Filament\Resources\WorkOrderNotes\Schemas\WorkOrderNoteForm;
use App\Filament\Resources\WorkOrderNotes\Tables\WorkOrderNotesTable;
use App\Models\WorkOrderNote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkOrderNoteResource extends Resource
{
    protected static ?string $model = WorkOrderNote::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WorkOrderNoteForm::configure($schema);
    }

    public static function canAccess(): bool
    {
        return false;  // Block all direct access
    }

    public static function table(Table $table): Table
    {
        return WorkOrderNotesTable::configure($table);
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
            'index' => ListWorkOrderNotes::route('/'),
            'create' => CreateWorkOrderNote::route('/create'),
            'edit' => EditWorkOrderNote::route('/{record}/edit'),
        ];
    }
}
