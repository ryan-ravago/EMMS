<?php

namespace App\Filament\Resources\AssetTagLogs;

use App\Filament\Resources\AssetTagLogs\Pages\ListAssetTagLogs;
use App\Filament\Resources\AssetTagLogs\Pages\ViewAssetTagLog;
use App\Filament\Resources\AssetTagLogs\Schemas\AssetTagLogInfolist;
use App\Filament\Resources\AssetTagLogs\Tables\AssetTagLogsTable;
use App\Models\AssetTagLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Every scan of a registered RFID tag (asset_tag_logs), in one list. Read-only: scans are written by the reader
 * pipeline (see RawLogProcessor) and are permanent, so nothing is created, edited or deleted here.
 *
 * Who may look is the "View Any" / "View" permission of this resource in Shield (super admin, asset admin and
 * asset manager by default).
 */
class AssetTagLogResource extends Resource
{
    protected static ?string $model = AssetTagLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'RFID Tag Logs';

    protected static ?string $modelLabel = 'RFID Tag Log';

    protected static ?string $pluralModelLabel = 'RFID Tag Logs';

    protected static ?int $navigationSort = 8;

    public static function getEloquentQuery(): Builder
    {
        // One query each for the tags, their assets and the yards of the rows on the page (no N+1).
        return parent::getEloquentQuery()->with(['tag.asset', 'yard']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssetTagLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetTagLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetTagLogs::route('/'),
            'view' => ViewAssetTagLog::route('/{record}'),
        ];
    }
}
