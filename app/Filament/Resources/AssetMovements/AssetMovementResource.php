<?php

namespace App\Filament\Resources\AssetMovements;

use App\Filament\Resources\AssetMovements\Pages\ListAssetMovements;
use App\Filament\Resources\AssetMovements\Pages\ViewAssetMovement;
use App\Filament\Resources\AssetMovements\Schemas\AssetMovementInfolist;
use App\Filament\Resources\AssetMovements\Tables\AssetMovementsTable;
use App\Models\AssetMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Equipment time out / time in verification. Trips are built from tag scans, so there is nothing to create,
 * edit or delete here: people acknowledge a trip, fill in a missing time, or disregard it.
 */
class AssetMovementResource extends Resource
{
    protected static ?string $model = AssetMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    // protected static string|UnitEnum|null $navigationGroup = 'Equipment';

    protected static ?string $navigationLabel = 'Time In / Out';

    protected static ?string $modelLabel = 'Equipment Trip';

    protected static ?string $pluralModelLabel = 'Equipment Trips';

    protected static ?int $navigationSort = 7;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['asset', 'tag', 'yardOut', 'yardIn', 'acknowledgedBy']);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = AssetMovement::query()->needingAttention()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssetMovementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetMovementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetMovements::route('/'),
            'view' => ViewAssetMovement::route('/{record}'),
        ];
    }
}
