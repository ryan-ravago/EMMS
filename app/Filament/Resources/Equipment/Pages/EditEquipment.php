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

    protected ?array $originalCategoryNames = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return EquipmentResource::getUrl('view', ['record' => $this->record->eqm_id]);
    }

    protected function beforeSave(): void
    {
        $this->originalAttributes = $this->record->getOriginal();
        $this->originalCategoryNames = $this->record->categories()
            ->orderBy('eqmc_name')
            ->pluck('eqmc_name')
            ->all();
    }

    protected function afterSave(): void
    {
        $changes = $this->record->getChanges();
        unset($changes['eqm_updated_at']);

        $columnsChanged = ! empty($changes);

        $diff = collect($changes)->mapWithKeys(fn ($new, $field) => [
            $field => [
                'old' => $this->originalAttributes[$field] ?? null,
                'new' => $new,
            ],
        ]);

        $newCategoryNames = $this->record->categories()
            ->orderBy('eqmc_name')
            ->pluck('eqmc_name')
            ->all();

        $categoriesChanged = $newCategoryNames !== $this->originalCategoryNames;

        if ($categoriesChanged) {
            $categoriesDiff = [
                'old' => $this->originalCategoryNames,
                'new' => $newCategoryNames,
            ];

            $diff->put('categories', $categoriesDiff);

            if ($columnsChanged) {
                // Equipment::save() already created a log row for the column diff; fold categories into it instead of a second row.
                $latestLog = $this->record->editLogs()->latest('id')->first();
                $latestLog?->update(['changes' => [...$latestLog->changes, 'categories' => $categoriesDiff]]);
            } else {
                $this->record->editLogs()->create([
                    'changes' => ['categories' => $categoriesDiff],
                    'performed_by' => auth()->id(),
                    'logged_at' => now(),
                ]);
            }
        }

        if ($diff->isEmpty()) {
            return;
        }

        activity()
            ->performedOn($this->record)
            ->withProperties($diff->toArray())
            ->log('Equipment updated');
    }
}
