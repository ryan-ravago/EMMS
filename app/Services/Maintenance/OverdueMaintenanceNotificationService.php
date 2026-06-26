<?php

namespace App\Services\Maintenance;

use App\Mail\MaintenanceTaskDueMail;
use App\Models\AppUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OverdueMaintenanceNotificationService
{
    public function notify(Carbon $now): int
    {
        $tasks = $this->fetchOverdueTasks($now);

        return $this->dispatchEmails($tasks, $now);
    }

    private function fetchOverdueTasks(Carbon $now): Collection
    {
        return DB::table('maintenance_tasks as mt')
            ->join('equipment_units as eqm', 'eqm.eqm_id', '=', 'mt.mt_eqm_id')
            ->join('departments as dep', 'dep.dep_id', '=', 'mt.mt_dep_id')
            ->join('tasks as t', 't.task_id', '=', 'mt.mt_task_id')
            ->join('statuses as s', 's.status_id', '=', 'mt.mt_status_id')
            ->where('eqm.eqm_is_active', 1)
            ->whereNull('mt.mt_closed_dt')
            ->where('mt.mt_status_id', 'pnd')
            ->whereRaw('DATE(mt.mt_due_dt) < ?', [$now->toDateString()])
            ->where(function ($query) use ($now) {
                $query->whereNull('mt.mt_overdue_notified_dt')
                    ->orWhereDate('mt.mt_overdue_notified_dt', '<', $now->toDateString());
            })
            ->select([
                'mt.mt_id',
                'mt.mt_task_log',
                'mt.mt_due_dt',
                'mt.mt_dep_id',
                'eqm.eqm_name',
                'dep.dep_name',
                't.task_name',
                's.status_title',
            ])
            ->get();
    }

    private function dispatchEmails(Collection $tasks, Carbon $now): int
    {
        if ($tasks->isEmpty()) {
            Log::info('[OverdueMaintenance] No overdue tasks to notify.');

            return 0;
        }

        $depIds = $tasks->pluck('mt_dep_id')->unique()->toArray();

        $recipients = AppUser::role('manager')
            ->whereHas('department', fn ($query) => $query->where('is_maintenance', 1))
            ->whereIn('user_dep_id', $depIds)
            ->whereNotNull('user_email')
            ->select('user_email', 'user_dep_id')
            ->get()
            ->groupBy('user_email');

        if ($recipients->isEmpty()) {
            Log::warning('[OverdueMaintenance] No managers found for overdue notification', [
                'dep_ids' => $depIds,
            ]);

            return 0;
        }

        $emailsQueued = 0;

        foreach ($recipients as $email => $group) {
            $managerDepId = $group->first()->user_dep_id;
            $recipientTasks = $tasks->filter(fn ($task) => $task->mt_dep_id == $managerDepId);

            if ($recipientTasks->isEmpty()) {
                Log::warning('[OverdueMaintenance] No overdue tasks matched for manager', [
                    'email' => $email,
                    'dep_ids' => $managerDepId,
                ]);

                continue;
            }

            try {
                Mail::to($email)->queue(new MaintenanceTaskDueMail($recipientTasks->values(), 'overdue'));
                $emailsQueued++;

                DB::table('maintenance_tasks')
                    ->whereIn('mt_id', $recipientTasks->pluck('mt_id')->toArray())
                    ->update([
                        'mt_overdue_notified_dt' => $now->toDateString(),
                    ]);
            } catch (\Exception $exception) {
                Log::error('[OverdueMaintenance] Failed to queue overdue email', [
                    'email' => $email,
                    'error' => $exception->getMessage(),
                    'task_ids' => $recipientTasks->pluck('mt_id')->toArray(),
                ]);
            }
        }

        Log::info('[OverdueMaintenance] Queued overdue notification emails', [
            'emails_queued' => $emailsQueued,
            'task_count' => $tasks->count(),
        ]);

        return $emailsQueued;
    }
}
