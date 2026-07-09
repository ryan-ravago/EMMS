<?php

namespace App\Filament\Resources\Equipment\Resources\Insps;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\Equipment\Resources\Insps\Pages\CreateInsps;
use App\Filament\Resources\Equipment\Resources\Insps\Pages\EditInsps;
use App\Filament\Resources\Equipment\Resources\Insps\Pages\ViewInsps;
use App\Filament\Resources\Equipment\Resources\Insps\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Equipment\Resources\Insps\Schemas\InspsForm;
use App\Filament\Resources\Equipment\Resources\Insps\Schemas\InspsInfolist;
use App\Filament\Resources\Equipment\Resources\Insps\Tables\InspsTable;
use App\Models\Insp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InspsResource extends Resource
{
    protected static ?string $model = Insp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = EquipmentResource::class;

    protected static ?string $recordTitleAttribute = 'insp_no';

    public static function form(Schema $schema): Schema
    {
        return InspsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InspsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InspsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateInsps::route('/create'),
            'view' => ViewInsps::route('/{record}'),
            'edit' => EditInsps::route('/{record}/edit'),
        ];
    }
}
