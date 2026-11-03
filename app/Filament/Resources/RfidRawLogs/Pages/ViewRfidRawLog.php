<?php

namespace App\Filament\Resources\RfidRawLogs\Pages;

use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\RfidRawLogs\RfidRawLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewRfidRawLog extends ViewRecord
{
    use HasRecordNavigation;

    protected static string $resource = RfidRawLogResource::class;

    /**
     * Previous / Next follow the list, which is newest first: "Next" is the next row down, so the older read.
     *
     * @return array{0: string, 1: 'asc'|'desc'}
     */
    protected function getRecordNavigationOrder(): array
    {
        return ['id', 'desc'];
    }
}
