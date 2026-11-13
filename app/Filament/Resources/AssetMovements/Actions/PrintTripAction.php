<?php

namespace App\Filament\Resources\AssetMovements\Actions;

use App\Models\AssetMovement;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * "Print": opens the trip as a PDF in a new tab (print from the browser's viewer). Shown on each row and on the
 * trip page, to people with the "Print" permission, for verified trips only.
 */
class PrintTripAction
{
    public static function make(): Action
    {
        return Action::make('printTrip')
            ->label('Print')
            ->icon(Heroicon::OutlinedPrinter)
            ->color('gray')
            ->authorize(fn (AssetMovement $record): bool => Auth::user()?->can('print', $record) ?? false)
            ->visible(fn (AssetMovement $record): bool => $record->isPrintable())
            ->url(fn (AssetMovement $record): string => route('asset-movements.print', $record))
            ->openUrlInNewTab();
    }
}
