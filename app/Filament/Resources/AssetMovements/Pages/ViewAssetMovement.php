<?php

namespace App\Filament\Resources\AssetMovements\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\AssetMovements\Actions\VerificationActions;
use App\Filament\Resources\AssetMovements\AssetMovementResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetMovement extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = AssetMovementResource::class;

    protected function getHeaderActions(): array
    {
        return VerificationActions::all();
    }

    /**
     * Previous / Next follow the list: newest first by the time out, or by the time in when the time out is
     * missing, with the id as tie-breaker. "Next" is the next row down the list, so the older trip.
     */
    protected function findAdjacentRecordKey(string $direction): string|int|null
    {
        $trip = $this->getRecord();
        $moment = ($trip->time_out ?? $trip->time_in)?->format('Y-m-d H:i:s');

        if ($moment === null) {
            return null;
        }

        $older = $direction === 'next';
        $operator = $older ? '<' : '>';
        $order = $older ? 'desc' : 'asc';

        return $this->getRecordNavigationQuery()
            ->whereRaw(
                "(COALESCE(asset_movements.time_out, asset_movements.time_in) {$operator} ? OR (COALESCE(asset_movements.time_out, asset_movements.time_in) = ? AND asset_movements.id {$operator} ?))",
                [$moment, $moment, $trip->getKey()],
            )
            ->orderByRaw("COALESCE(asset_movements.time_out, asset_movements.time_in) {$order}")
            ->orderBy('asset_movements.id', $order)
            ->value('asset_movements.id');
    }
}
