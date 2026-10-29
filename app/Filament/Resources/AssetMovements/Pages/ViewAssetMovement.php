<?php

namespace App\Filament\Resources\AssetMovements\Pages;

use App\Filament\Resources\AssetMovements\Actions\VerificationActions;
use App\Filament\Resources\AssetMovements\AssetMovementResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAssetMovement extends ViewRecord
{
    protected static string $resource = AssetMovementResource::class;

    protected function getHeaderActions(): array
    {
        return VerificationActions::all();
    }
}
