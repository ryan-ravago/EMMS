<?php

namespace App\Filament\Resources\ChecklistTemplates\Pages;

use App\Filament\Resources\ChecklistTemplates\ChecklistTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditChecklistTemplate extends EditRecord
{
    protected static string $resource = ChecklistTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['clt_last_updated_by'] = auth()->id();
        $data['clt_last_updated_dt'] = now();

        return $data;
    }

    protected function beforeValidate(): void
    {
        $data = $this->form->getState();

        if (($data['clt_cut_id'] ?? null) == 2) {
            $hasInterval = $data['clt_interval_years']
                || $data['clt_interval_months']
                || $data['clt_interval_weeks']
                || $data['clt_interval_days'];

            if (!$hasInterval) {
                Notification::make()
                    ->title('Interval Required')
                    ->body('At least one interval field (years, months, weeks, or days) is required.')
                    ->danger()
                    ->send();

                $this->halt();
            }
        }
    }
}
