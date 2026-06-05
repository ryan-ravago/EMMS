<?php

namespace App\Filament\Resources\InspectionItems;

use App\Filament\Resources\InspectionItems\Pages\CreateInspectionItem;
use App\Filament\Resources\InspectionItems\Pages\EditInspectionItem;
use App\Filament\Resources\InspectionItems\Pages\ListInspectionItems;
use App\Filament\Resources\InspectionItems\Pages\ViewInspectionItem;
use App\Filament\Resources\InspectionItems\RelationManagers\LogsRelationManager;
use App\Filament\Resources\InspectionItems\Schemas\InspectionItemForm;
use App\Filament\Resources\InspectionItems\Schemas\InspectionItemInfolist;
use App\Filament\Resources\InspectionItems\Tables\InspectionItemsTable;
use App\Models\InspectionItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InspectionItemResource extends Resource
{
    protected static ?string $model = InspectionItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'insi_id';

    public static function form(Schema $schema): Schema
    {
        return InspectionItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InspectionItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InspectionItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInspectionItems::route('/'),
            'create' => CreateInspectionItem::route('/create'),
            'view' => ViewInspectionItem::route('/{record}'),
            'edit' => EditInspectionItem::route('/{record}/edit'),
        ];
    }
}
