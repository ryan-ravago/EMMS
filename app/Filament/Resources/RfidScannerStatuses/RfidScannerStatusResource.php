<?php

namespace App\Filament\Resources\RfidScannerStatuses;

use App\Filament\Resources\RfidScannerStatuses\Pages\ListRfidScannerStatuses;
use App\Filament\Resources\RfidScannerStatuses\Pages\ViewRfidScannerStatus;
use App\Filament\Resources\RfidScannerStatuses\RelationManagers\LogsRelationManager;
use App\Filament\Resources\RfidScannerStatuses\Schemas\RfidScannerStatusInfolist;
use App\Filament\Resources\RfidScannerStatuses\Tables\RfidScannerStatusesTable;
use App\Models\RfidScannerStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Whether each gate scanner (IN / OUT) is working, as reported by the RFID reader server. Read-only: the reader
 * server is the one that knows, EMMS only shows it.
 */
class RfidScannerStatusResource extends Resource
{
    protected static ?string $model = RfidScannerStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $navigationLabel = 'Scanner Status';

    protected static ?string $modelLabel = 'Scanner';

    protected static ?string $pluralModelLabel = 'Scanner Status';

    protected static ?int $navigationSort = 10;

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
        return RfidScannerStatusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RfidScannerStatusesTable::configure($table);
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
            'index' => ListRfidScannerStatuses::route('/'),
            'view' => ViewRfidScannerStatus::route('/{record}'),
        ];
    }
}
