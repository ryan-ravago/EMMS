<?php

namespace App\Filament\Resources\ChecklistTemplates\Pages;

use App\Filament\Resources\ChecklistTemplates\ChecklistTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChecklistTemplate extends CreateRecord
{
    protected static string $resource = ChecklistTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clt_created_by']      = auth()->id();
        $data['clt_created_dt']      = now();
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
                \Filament\Notifications\Notification::make()
                    ->title('Interval Required')
                    ->body('At least one interval field (years, months, weeks, or days) is required.')
                    ->warning()
                    ->send();

                $this->halt();
            }
        }
    }
}
