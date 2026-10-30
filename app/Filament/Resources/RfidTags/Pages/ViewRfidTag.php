<?php

namespace App\Filament\Resources\RfidTags\Pages;

use App\Filament\Concerns\HasDetailsTab;
use App\Filament\Concerns\HasRecordNavigation;
use App\Filament\Resources\RfidTags\RfidTagResource;
use Filament\Resources\Pages\ViewRecord;

class ViewRfidTag extends ViewRecord
{
    use HasDetailsTab;
    use HasRecordNavigation;

    protected static string $resource = RfidTagResource::class;
}
