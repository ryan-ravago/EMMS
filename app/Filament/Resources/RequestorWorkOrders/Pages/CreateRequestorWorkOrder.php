<?php

namespace App\Filament\Resources\RequestorWorkOrders\Pages;

use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use App\Mail\WorkOrderActionRequiredMail;
use App\Mail\WorkOrderConfirmationMail;
use App\Models\AppUser;
use App\Models\WorkOrderLog;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreateRequestorWorkOrder extends CreateRecord
{
    protected static string $resource = RequestorWorkOrderResource::class;

    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Submit');
    }

    protected function afterCreate(): void
    {
        $workOrder = $this->record;

        // 1. Send confirmation email to the requestor
        if ($workOrder->createdBy && $workOrder->createdBy->user_email) {
            Mail::to($workOrder->createdBy->user_email)
                ->queue(new WorkOrderConfirmationMail($workOrder));
        }

        // 2. Notify managers of the assigned department
        $managerEmails = AppUser::whereHas('roles', fn($q) => $q->where('name', 'manager'))
            ->where('user_dep_id', $workOrder->wo_dep_id)
            ->whereNotNull('user_email')
            ->pluck('user_email')
            ->unique()
            ->all();

        if (! empty($managerEmails)) {
            Mail::to($managerEmails)
                ->queue(new WorkOrderActionRequiredMail($workOrder));
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Backend validation: ensure department is a maintenance department
        $department = DB::table('departments')->where('dep_id', $data['wo_dep_id'])->first();
        if (! $department || ! $department->is_maintenance) {
            throw new \Exception('Selected department must be a maintenance department.');
        }

        return DB::transaction(function () use ($data) {
            try {
                $now = now();

                $depCode = DB::table('departments')
                    ->where('dep_id', $data['wo_dep_id'])
                    ->value('dep_code');

                $count = DB::table('work_orders')
                    ->where('wo_dep_id', $data['wo_dep_id'])
                    ->whereDate('wo_created_dt', $now->toDateString())
                    ->count() + 1;

                $data['wo_no'] = 'WO-' . $depCode . '-' . $now->format('ymd') . str_pad($count, 3, '0', STR_PAD_LEFT);
                $data['wo_status_id'] = 'pndwor';
                $data['wo_created_by'] = auth()->id();
                $data['wo_created_dt'] = $now;

                $workOrder = parent::handleRecordCreation($data);

                WorkOrderLog::create([
                    'wol_wo_id' => $workOrder->wo_id,
                    'wol_a_id' => 'create',
                    'wol_status_id' => 'pndwor',
                    'wol_a_log' => DB::table('actions')->where('a_id', 'create')->value('a_past_tense'),
                    'wol_status_log' => DB::table('statuses')->where('status_id', 'pndwor')->value('status_title'),
                    'wol_by' => auth()->id(),
                    'wol_dt' => $now,
                ]);

                return $workOrder;
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Failed to create work order.')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();

                throw $e;
            }
        });
    }
}
