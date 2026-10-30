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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('asset');
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return array_filter([
            'Equipment' => trim("{$record->asset?->eqm_prc_code} {$record->asset?->eqm_name}"),
            'Status' => $record->is_deleted ? 'Retired' : null,
        ]);
    }

    /**
     * Tags live under their equipment, so the link needs the equipment (and its page) as well.
     */
    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        if ($record->asset === null || ! static::canView($record)) {
            return null;
        }

        return static::getUrl(
            'view',
            ['record' => $record, 'asset' => $record->asset],
            configuration: EquipmentResource::pageKeyForRecord($record->asset),
        );
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
