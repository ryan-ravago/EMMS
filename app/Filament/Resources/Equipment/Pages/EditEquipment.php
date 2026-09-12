<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipment extends EditRecord
{
    protected static string $resource = EquipmentResource::class;

    protected ?array $originalAttributes = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }


    protected function beforeSave(): void
    {
        $this->originalAttributes = $this->record->getOriginal();
    }

    protected function afterSave(): void
    {
        $changes = $this->record->getChanges();
        unset($changes['eqm_updated_at']);

        if (empty($changes)) {
            return;
        }

        $diff = collect($changes)->mapWithKeys(fn($new, $field) => [
            $field => [
                'old' => $this->originalAttributes[$field] ?? null,
                'new' => $new,
            ],
        ])->toArray();

        $this->record->editLogs()->create([
            'changes' => $diff,
            'performed_by' => auth()->id(),
            'logged_at' => now(),
        ]);

        activity()
            ->performedOn($this->record)
            ->withProperties($diff)
            ->log('Equipment updated');
    }
}
