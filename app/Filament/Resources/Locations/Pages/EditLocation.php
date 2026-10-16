<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use App\Models\Location;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLocation extends EditRecord
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (DeleteAction $action, Location $record): void {
                    $reason = $record->deletionBlockedReason();

                    if ($reason === null) {
                        return;
                    }

                    Notification::make()
                        ->title('Cannot delete this location')
                        ->body($reason)
                        ->danger()
                        ->persistent()
                        ->send();

                    $action->cancel();
                }),
        ];
    }
}
