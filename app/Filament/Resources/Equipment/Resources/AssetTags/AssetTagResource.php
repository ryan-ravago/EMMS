<?php

namespace App\Filament\Resources\Equipment\Resources\AssetTags;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\Equipment\Resources\AssetTags\Pages\ViewAssetTag;
use App\Filament\Resources\Equipment\Resources\AssetTags\RelationManagers\LogsRelationManager;
use App\Filament\Resources\Equipment\Resources\AssetTags\Schemas\AssetTagInfolist;
use App\Filament\Resources\Equipment\Resources\AssetTags\Tables\AssetTagsTable;
use App\Models\AssetTag;
use BackedEnum;
use Filament\Resources\ParentResourceRegistration;
use Filament\Resources\Resource;
use Filament\Resources\ResourceConfiguration;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssetTagResource extends Resource
{
    protected static ?string $model = AssetTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $configurationClass = ResourceConfiguration::class;

    protected static ?string $recordTitleAttribute = 'tag_id';

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return EquipmentResource::asParent(childResource: static::class)
            ->relationship('tags')
            ->inverseRelationship('asset');
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
            LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewAssetTag::route('/{record}'),
        ];
    }
}
