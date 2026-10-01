<?php

namespace App\Filament\Resources\AssetTags;

use App\Filament\Resources\AssetTags\Pages\CreateAssetTag;
use App\Filament\Resources\AssetTags\Pages\EditAssetTag;
use App\Filament\Resources\AssetTags\Pages\ListAssetTags;
use App\Filament\Resources\AssetTags\Pages\ViewAssetTag;
use App\Filament\Resources\AssetTags\Schemas\AssetTagForm;
use App\Filament\Resources\AssetTags\Schemas\AssetTagInfolist;
use App\Filament\Resources\AssetTags\Tables\AssetTagsTable;
use App\Models\AssetTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssetTagResource extends Resource
{
    protected static ?string $model = AssetTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'tag';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AssetTagForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssetTagInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetTagsTable::configure($table);
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
            'index' => ListAssetTags::route('/'),
            'create' => CreateAssetTag::route('/create'),
            'view' => ViewAssetTag::route('/{record}'),
            'edit' => EditAssetTag::route('/{record}/edit'),
        ];
    }
}
