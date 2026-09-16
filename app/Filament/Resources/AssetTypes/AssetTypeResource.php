<?php

namespace App\Filament\Resources\AssetTypes;

use App\Filament\Resources\AssetTypes\Pages\CreateAssetType;
use App\Filament\Resources\AssetTypes\Pages\EditAssetType;
use App\Filament\Resources\AssetTypes\Pages\ListAssetTypes;
use App\Filament\Resources\AssetTypes\Pages\ViewAssetType;
use App\Filament\Resources\AssetTypes\RelationManagers\AssetsRelationManager;
use App\Filament\Resources\AssetTypes\Schemas\AssetTypeForm;
use App\Filament\Resources\AssetTypes\Schemas\AssetTypeInfolist;
use App\Filament\Resources\AssetTypes\Tables\AssetTypesTable;
use App\Models\AssetType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssetTypeResource extends Resource
{
    protected static ?string $model = AssetType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AssetTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssetTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AssetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetTypes::route('/'),
            'create' => CreateAssetType::route('/create'),
            'view' => ViewAssetType::route('/{record}'),
            'edit' => EditAssetType::route('/{record}/edit'),
        ];
    }
}
