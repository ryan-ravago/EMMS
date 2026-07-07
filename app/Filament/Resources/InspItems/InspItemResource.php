<?php

namespace App\Filament\Resources\InspItems;

use App\Filament\Resources\InspItems\Pages\CreateInspItem;
use App\Filament\Resources\InspItems\Pages\EditInspItem;
use App\Filament\Resources\InspItems\Pages\ListInspItems;
use App\Filament\Resources\InspItems\Pages\ViewInspItem;
use App\Filament\Resources\InspItems\Schemas\InspItemForm;
use App\Filament\Resources\InspItems\Schemas\InspItemInfolist;
use App\Filament\Resources\InspItems\Tables\InspItemsTable;
use App\Filament\Resources\Insps\InspResource;
use App\Models\InspItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InspItemResource extends Resource
{
    protected static ?string $model = InspItem::class;

    protected static ?string $parentResource = InspResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Inspections';

    protected static ?string $recordTitleAttribute = 'inspi_id';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return InspItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InspItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InspItemsTable::configure($table);
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
            'index' => ListInspItems::route('/'),
            'create' => CreateInspItem::route('/create'),
            // 'view' => ViewInspItem::route('/{record}'),
            'edit' => EditInspItem::route('/{record}/edit'),
        ];
    }
}
