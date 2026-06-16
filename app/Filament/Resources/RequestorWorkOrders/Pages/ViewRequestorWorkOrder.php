<?php

namespace App\Filament\Resources\RequestorWorkOrders\Pages;

use App\Filament\Resources\RequestorWorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewRequestorWorkOrder extends ViewRecord
{
    protected static string $resource = RequestorWorkOrderResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): string
    {
        return 'Details';
    }

    public function getRelationManagers(): array
    {
        return [
            LogsRelationManager::class,
        ];
    }
}
