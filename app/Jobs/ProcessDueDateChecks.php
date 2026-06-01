<?php

namespace App\Jobs;

use App\Mail\MaintenanceTaskDueMail;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AppUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProcessDueDateChecks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = now();
        $etsIds = [];

        DB::transaction(function () use ($now, &$etsIds) {
            $etsIds = $this->processEquipmentTasksSchedules($now);
            $this->processOverDueMaintenanceTasks($now);
        });

        // send notifications AFTER transaction commits
        $this->sendDueNotifications($etsIds, $now);
    }

    /**
     * Check ets_due_dt, create maintenance_tasks, advance/null out ets_due_dt.
     */
    private function processEquipmentTasksSchedules($now): array
    {
        $allEtsIds = [];

        DB::table('equipment_tasks_schedules as ets')
            ->join('tasks as t', 't.task_id', '=', 'ets.ets_task_id')
            ->join('equipment_units as equ', 'equ.eqm_id', '=', 'ets.ets_eqm_id')
            ->whereNotNull('ets.ets_due_dt')
            ->where('ets.ets_due_dt', '<=', $now)
            ->select([
                'equ.eqm_name',
                'ets.ets_id',
                'ets.ets_eqm_id',
                'ets.ets_dep_id',
                'ets.ets_task_id',
                'ets.ets_due_dt',
                'ets.ets_itrv_years',
                'ets.ets_itrv_months',
                'ets.ets_itrv_weeks',
                'ets.ets_itrv_days',
                'ets.ets_sched_time',
                't.task_name',
            ])
            ->orderBy('ets.ets_due_dt')
            ->chunk(200, function ($schedules) use ($now, &$allEtsIds) {
                $insert = [];
                $batchId = Str::uuid();
                $scheduleIds = $schedules->pluck('ets_id')->toArray();

                foreach ($schedules as $row) {
                    $insert[] = [
                        'mt_batch_id'     => $batchId,
                        'mt_eqm_id'       => $row->ets_eqm_id,
                        'mt_eqm_log'      => $row->eqm_name,
                        'mt_dep_id'       => $row->ets_dep_id,
                        'mt_task_id'      => $row->ets_task_id,
                        'mt_task_log'     => $row->task_name,
                        'mt_status_id'    => 'pnd',
                        'mt_scheduled_dt' => $row->ets_due_dt,
                        'mt_due_dt'       => $row->ets_due_dt,
                        'mt_remarks'      => 'System-generated',
                        'mt_by'           => null,
                        'mt_dt'           => $now
                    ];
                }

                if (empty($insert)) {
                    return;
                }

                DB::table('maintenance_tasks')->insert($insert);

                $insertedIds = DB::table('maintenance_tasks')
                    ->where('mt_batch_id', $batchId)
                    ->select(['mt_id', 'mt_due_dt'])
                    ->get();

                $logs = [];
                foreach ($insertedIds as $mtId) {
                    $logs[] = [
                        'mtl_mt_id' => $mtId->mt_id,
                        'mtl_status_id' => 'pnd',
                        'mtl_due_dt' => $mtId->mt_due_dt,
                        'mtl_last_act_made' => 'create',
                        'mtl_remarks' => 'System-generated',
                        'mtl_by' => null,
                        'mtl_dt' => $now,
                    ];
                }

                if (!empty($logs)) {
                    DB::table('maintenance_task_logs')->insert($logs);
                }

                DB::table('equipment_tasks_schedules')
                    ->whereIn('ets_id', $scheduleIds)
                    ->update(['ets_due_dt' => null]);

                $allEtsIds = array_merge($allEtsIds, $scheduleIds);

                Log::info('[DueDateChecks] Created maintenance tasks & advanced ets_due_dt', [
                    'ets_ids' => $allEtsIds,
                    'tasks_count' => count($insert),
                ]);
            });

        return $allEtsIds;
    }

    /**
     * Check mt_due_dt, mark overdue, log it.
     */
    private function processOverDueMaintenanceTasks($now): void
    {
        DB::table('maintenance_tasks')
            ->where('mt_due_dt', '<=', $now)
            ->whereDate('mt_dt', '!=', $now->toDateString())
            ->whereNotNull('mt_due_dt')
            ->whereNull('mt_closed_dt')
            ->whereIn('mt_status_id', ['snz'])
            ->select('mt_id', 'mt_status_id', 'mt_due_dt')
            ->orderBy('mt_due_dt')
            ->chunk(200, function ($tasks) use ($now) {
                $ids = $tasks->pluck('mt_id')->toArray();

                DB::table('maintenance_tasks')
                    ->whereIn('mt_id', $ids)
                    ->whereIn('mt_status_id', ['snz'])
                    ->update([
                        'mt_status_id' => 'pnd',
                    ]);

                $logs = $tasks->map(fn($task) => [
                    'mtl_mt_id'     => $task->mt_id,
                    'mtl_status_id' => 'pnd',
                    'mtl_due_dt'    => $task->mt_due_dt,
                    'mtl_last_act_made' => 'rtv',
                    'mtl_remarks'   => 'Overdue - updated by system',
                    'mtl_by'        => null,
                    'mtl_dt'        => $now,
                ])->toArray();

                DB::table('maintenance_task_logs')->insert($logs);

                Log::info('[DueDateChecks] Marked maintenance tasks as overdue', [
                    'count' => count($ids),
                    'ids'   => $ids,
                ]);
            });
    }

    /**
     * Send email notifications.
     */
    private function sendDueNotifications(array $etsIds, $now): void
    {
        // Fetches the inserted task schedules to maintenance tasks selecting columns: ets_eqm_id & ets_task_id
        // Their due dates are nulled
        $schedules = DB::table('equipment_tasks_schedules')
            ->whereIn('ets_id', $etsIds)
            ->select('ets_eqm_id', 'ets_task_id')
            ->get();

        $eqmIds = $schedules->pluck('ets_eqm_id')->unique()->toArray();
        $taskIds = $schedules->pluck('ets_task_id')->unique()->toArray();

        // Fetch the maintenance tasks based on nulled task schedules
        // Newly due tasks (created today from task schedules)
        $newTasks = DB::table('maintenance_tasks as mt')
            ->join('equipment_units as eqm', 'eqm.eqm_id', '=', 'mt.mt_eqm_id')
            ->join('departments as dep', 'dep.dep_id', '=', 'mt.mt_dep_id')
            ->join('tasks as t', 't.task_id', '=', 'mt.mt_task_id')
            ->join('statuses as s', 's.status_id', '=', 'mt.mt_status_id')
            ->whereIn('mt.mt_eqm_id', $eqmIds)
            ->whereIn('mt.mt_task_id', $taskIds)
            ->whereRaw('DATE(mt.mt_due_dt) = ?', [$now->toDateString()])
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

        // Overdue tasks
        $overdueTasks = DB::table('maintenance_tasks as mt')
            ->join('equipment_units as eqm', 'eqm.eqm_id', '=', 'mt.mt_eqm_id')
            ->join('departments as dep', 'dep.dep_id', '=', 'mt.mt_dep_id')
            ->join('tasks as t', 't.task_id', '=', 'mt.mt_task_id')
            ->join('statuses as s', 's.status_id', '=', 'mt.mt_status_id')
            ->whereNull('mt.mt_closed_dt')
            ->where('mt.mt_status_id', 'pnd')
            ->whereRaw('DATE(mt.mt_due_dt) < ?', [$now->toDateString()]) // exclude today's new ones
            ->select(['mt.mt_id', 'mt.mt_task_log', 'mt.mt_due_dt', 'mt.mt_dep_id', 'eqm.eqm_name', 'dep.dep_name', 't.task_name', 's.status_title'])
            ->get();

        $this->dispatchEmails($newTasks, 'due');
        $this->dispatchEmails($overdueTasks, 'overdue');
    }

    private function dispatchEmails($tasks, string $type): void
    {
        if ($tasks->isEmpty()) {
            Log::info("[DueDateChecks] No {$type} tasks to notify.");
            return;
        }

        $depIds = $tasks->pluck('mt_dep_id')->unique()->toArray();

        // $recipients = DB::table('app_users as u')
        //     ->join('model_has_roles as mhr', 'mhr.model_id', '=', 'u.user_id')
        //     ->join('roles as r', 'r.id', '=', 'mhr.role_id')
        //     ->where('mhr.model_type', AppUser::class)
        //     ->whereIn('u.user_dep_id', $depIds)
        //     ->whereNotNull('u.user_email')
        //     ->where('r.name', 'manager')
        //     ->select('u.user_email', 'u.user_dep_id')
        //     ->distinct()
        //     ->get()
        //     ->groupBy('user_email');

        $recipients = AppUser::role('manager')
            ->whereIn('user_dep_id', $depIds)
            ->whereNotNull('user_email')
            ->select('user_email', 'user_dep_id')
            ->get()
            ->groupBy('user_email');

        if ($recipients->isEmpty()) {
            Log::warning("[DueDateChecks] No managers found for {$type} notification", [
                'dep_ids' => $depIds,
            ]);
            return;
        }

        $emailsQueued = 0;

        foreach ($recipients as $email => $group) {
            $managerDepId = $group->first()->user_dep_id;
            $recipientTasks = $tasks->filter(fn($task) => $task->mt_dep_id == $managerDepId);
            Log::info($recipientTasks);

            if ($recipientTasks->isEmpty()) {
                Log::warning("[DueDateChecks] No {$type} tasks matched for manager", [
                    'email' => $email,
                    'dep_ids' => $managerDepId,
                ]);
                continue;
            }

            try {
                Mail::to($email)->queue(new MaintenanceTaskDueMail($recipientTasks->values(), $type));
                $emailsQueued++;
            } catch (\Exception $e) {
                Log::error("[DueDateChecks] Failed to queue {$type} email", [
                    'email' => $email,
                    'error' => $e->getMessage(),
                    'task_ids' => $recipientTasks->pluck('mt_id')->toArray(),
                ]);
            }
        }

        Log::info("[DueDateChecks] Queued {$type} notification emails", [
            'emails_queued' => $emailsQueued,
            'task_count' => $tasks->count(),
        ]);
    }
}
