<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Mail\WorkOrderAssignedMail;
use App\Mail\WorkOrderConfirmationMail;
use App\Models\AppUser;
use App\Models\WorkOrderLog;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): ActionsAction
    {
        return parent::getCreateFormAction()
            ->label('Submit');
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            try {
                $now = now();

                if (! auth()->user()->hasRole('super_admin')) {
                    $data['wo_dep_id'] = auth()->user()->user_dep_id;
                }

                $depCode = DB::table('departments')
                    ->where('dep_id', $data['wo_dep_id'])
                    ->value('dep_code');

                $count = DB::table('work_orders')
                    ->where('wo_dep_id', $data['wo_dep_id'])
                    ->whereDate('wo_created_dt', $now->toDateString())
                    ->count() + 1;

                $data['wo_no'] = 'WO-' . $depCode . '-' . $now->format('ymd') . str_pad($count, 3, '0', STR_PAD_LEFT);
                $data['wo_status_id'] = 'inprog';
                $data['wo_created_by'] = auth()->id();
                $data['wo_created_dt'] = $now;

                $workOrder = parent::handleRecordCreation($data);

                WorkOrderLog::create([
                    'wol_wo_id' => $workOrder->wo_id,
                    'wol_a_id' => 'create',
                    'wol_status_id' => 'inprog',
                    'wol_a_log' => DB::table('actions')->where('a_id', 'create')->value('a_past_tense'),
                    'wol_status_log' => DB::table('statuses')->where('status_id', 'inprog')->value('status_title'),
                    'wol_by' => auth()->id(),
                    'wol_dt' => $now,
                ]);

                return $workOrder;
            } catch (\Throwable $e) {
                // Transaction will auto-rollback after exception is re-thrown
                Notification::make()
                    ->title('Failed to create work order.')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();

                throw $e; // re-throw so DB::transaction rolls back
            }
        });
    }

    protected function afterCreate(): void
    {
        $workOrder = $this->getRecord();
        $workOrder->load(['workers', 'priority', 'createdBy']);

        // Notify managers
        $managerEmails = AppUser::whereHas('roles', fn($q) => $q->where('name', 'manager'))
            ->where('user_dep_id', $workOrder->wo_dep_id)
            ->whereNotNull('user_email')
            ->pluck('user_email')
            ->unique()
            ->all();

        if (! empty($managerEmails)) {
            Mail::to($managerEmails)
                ->queue(new WorkOrderConfirmationMail($workOrder));
        }

        // Notify each assigned technician
        $workerEmails = $workOrder->workers
            ->pluck('user_email')
            ->filter()
            ->unique()
            ->all();

        if (! empty($workerEmails)) {
            Mail::to($workerEmails)
                ->queue(new WorkOrderAssignedMail($workOrder));
        }
    }
}
