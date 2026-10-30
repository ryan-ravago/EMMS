<?php

namespace App\Filament\Resources\RfidTags;

use App\Filament\Resources\Equipment\Resources\AssetTags\RelationManagers\LogsRelationManager;
use App\Filament\Resources\Equipment\Resources\AssetTags\Schemas\AssetTagInfolist;
use App\Filament\Resources\Equipment\Resources\AssetTags\Tables\AssetTagsTable;
use App\Filament\Resources\RfidTags\Pages\ListRfidTags;
use App\Filament\Resources\RfidTags\Pages\ViewRfidTag;
use App\Models\AssetTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Every RFID tag in one list. A tag is created and assigned from its equipment, so nothing is created here.
 * Access follows the existing tag policy.
 */
class RfidTagResource extends Resource
{
    protected static ?string $model = AssetTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'RFID Tags';

    protected static ?string $modelLabel = 'RFID Tag';

    protected static ?string $pluralModelLabel = 'RFID Tags';

    protected static ?string $recordTitleAttribute = 'tag_id';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('asset');
    }

    /**
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['tag_id', 'asset.eqm_prc_code', 'asset.eqm_name'];
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssetTagInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetTagsTable::configure($table, withAsset: true);
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
            'index' => ListRfidTags::route('/'),
            'view' => ViewRfidTag::route('/{record}'),
        ];
    }
}
