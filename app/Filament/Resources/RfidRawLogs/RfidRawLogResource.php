<?php

namespace App\Filament\Resources\RfidRawLogs;

use App\Filament\Resources\RfidRawLogs\Pages\ListRfidRawLogs;
use App\Filament\Resources\RfidRawLogs\Pages\ViewRfidRawLog;
use App\Filament\Resources\RfidRawLogs\Schemas\RfidRawLogInfolist;
use App\Filament\Resources\RfidRawLogs\Tables\RfidRawLogsTable;
use App\Models\RfidRawLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Every read the RFID reader server sent, registered tag or not, kept for the retention period. Read-only:
 * the rows are written by the API and updated by the processor.
 */
class RfidRawLogResource extends Resource
{
    protected static ?string $model = RfidRawLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'RFID Raw Logs';

    protected static ?string $modelLabel = 'RFID Raw Log';

    protected static ?string $pluralModelLabel = 'RFID Raw Logs';

    protected static ?string $recordTitleAttribute = 'tag_id';

    protected static ?int $navigationSort = 9;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('yard');
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
        return RfidRawLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RfidRawLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRfidRawLogs::route('/'),
            'view' => ViewRfidRawLog::route('/{record}'),
        ];
    }
}
