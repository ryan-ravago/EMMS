<?php

namespace App\Jobs;

use App\Mail\MaintenanceTaskDueMail;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
        $ecaIds = [];

        DB::transaction(function () use ($now, &$ecaIds) {
            $ecaIds = $this->processEquipmentChecklistAssignments($now);
            $this->processMaintenanceTasks($now);
        });

        // ✅ send notifications AFTER transaction commits
        if (!empty($ecaIds)) {
            $this->sendDueNotifications($ecaIds, $now);
        }
    }

    // ----------------------------------------------------------------
    // Step 1: Check eca_due_dt, create maintenance_tasks, null out eca_due_dt
    // ----------------------------------------------------------------
    private function processEquipmentChecklistAssignments($now): array
    {
        $allEcaIds = [];

        DB::table('equipment_checklist_assignments as eca')
            ->join('checklist_templates as ct', 'ct.clt_id', '=', 'eca.eca_clt_id')
            ->where('eca.eca_due_dt', '<=', $now)
            ->whereNotNull('eca.eca_due_dt')
            ->whereRaw('TIME(?) >= ct.clt_schedule_time', [$now])  // ✅ use $now, not NOW()
            ->select([
                'eca.eca_id',
                'eca.eca_eqm_id',
                'eca.eca_dep_id',
                'eca.eca_clt_id',
                'eca.eca_due_dt',
                // ✅ interval fields needed to advance the due date after processing
                'ct.clt_interval_years',
                'ct.clt_interval_months',
                'ct.clt_interval_weeks',
                'ct.clt_interval_days',
                'ct.clt_name',  // ✅ for mt_clt_log snapshot
            ])
            ->orderBy('eca.eca_due_dt')
            ->chunk(200, function ($assignments) use ($now, &$allEcaIds) {

                // ✅ Collect all clt_ids to fetch their checklist items in one query
                $cltIds = $assignments->pluck('eca_clt_id')->unique()->toArray();

                $itemsByTemplate = DB::table('checklist_items')
                    ->whereIn('cli_clt_id', $cltIds)
                    ->orderBy('cli_sort_order')
                    ->get()
                    ->groupBy('cli_clt_id');

                $insert = [];

                foreach ($assignments as $row) {
                    $items = $itemsByTemplate->get($row->eca_clt_id, collect());

                    // ✅ Skip silently if template has no items — nothing to insert
                    if ($items->isEmpty()) {
                        Log::warning('[DueDateChecks] No checklist items found for template', [
                            'eca_id' => $row->eca_id,
                            'clt_id' => $row->eca_clt_id,
                        ]);
                        continue;
                    }

                    foreach ($items as $item) {
                        $insert[] = [
                            'mt_eqm_id'       => $row->eca_eqm_id,
                            'mt_dep_id'       => $row->eca_dep_id,
                            'mt_clt_id'       => $row->eca_clt_id,
                            'mt_clt_log'      => $row->clt_name,         // ✅ snapshot template name
                            'mt_cli_id'       => $item->cli_id,          // ✅ actual item id
                            'mt_cli_log'      => $item->cli_name,        // ✅ snapshot item name
                            'mt_status_id'    => 'pnd',
                            'mt_scheduled_dt' => $row->eca_due_dt,
                            'mt_due_dt'       => $row->eca_due_dt,
                            'mt_remarks'      => null,
                            'mt_by'           => null,                   // set if you have a system user id
                            'mt_dt'           => $now
                        ];
                    }
                }

                if (empty($insert)) {
                    return;
                }

                DB::table('maintenance_tasks')->insert($insert);

                // ✅ Advance eca_due_dt per assignment using its template's interval
                foreach ($assignments as $row) {
                    DB::table('equipment_checklist_assignments')
                        ->where('eca_id', $row->eca_id)
                        ->update(['eca_due_dt' => null]);
                }

                $ecaIds = $assignments->pluck('eca_id')->toArray();
                $allEcaIds = array_merge($allEcaIds, $ecaIds);  // ✅ collect across chunks

                Log::info('[DueDateChecks] Created maintenance tasks & advanced eca_due_dt', [
                    'eca_ids'      => $ecaIds,
                    'tasks_count'  => count($insert),
                ]);
            });

        return $allEcaIds;
    }

    // ----------------------------------------------------------------
    // Step 2: Send email notifications
    // ----------------------------------------------------------------
    private function sendDueNotifications(array $ecaIds, $now): void
    {
        // ✅ collect eqm_ids and clt_ids from the assignments we just processed
        $assignments = DB::table('equipment_checklist_assignments')
            ->whereIn('eca_id', $ecaIds)
            ->select('eca_eqm_id', 'eca_clt_id')
            ->get();

        $eqmIds = $assignments->pluck('eca_eqm_id')->unique()->toArray();
        $cltIds = $assignments->pluck('eca_clt_id')->unique()->toArray();

        $tasks = DB::table('maintenance_tasks as mt')
            ->join('equipment_units as eqm', 'eqm.eqm_id', '=', 'mt.mt_eqm_id')
            ->join('departments as dep', 'dep.dep_id', '=', 'mt.mt_dep_id')
            ->join('checklist_templates as ct', 'ct.clt_id', '=', 'mt.mt_clt_id')
            ->join('statuses as s', 's.status_id', '=', 'mt.mt_status_id')
            ->whereIn('mt.mt_eqm_id', $eqmIds)
            ->whereIn('mt.mt_clt_id', $cltIds)
            ->whereRaw('DATE(mt.mt_due_dt) = ?', [$now->toDateString()])  // ✅ cast datetime to date
            ->select([
                'mt.mt_id',
                'mt.mt_clt_log',   // ✅ for email subject
                'mt.mt_cli_log',
                'mt.mt_due_dt',
                'mt.mt_dep_id',
                'eqm.eqm_name',
                'dep.dep_name',
                'ct.clt_name',
                's.status_title',
            ])
            ->get();

        Log::info('[DueDateChecks] Tasks for notification', ['count' => $tasks->count()]);

        if ($tasks->isEmpty()) {
            Log::warning('[DueDateChecks] No tasks found for notification', ['eca_ids' => $ecaIds]);
            return;
        }

        $depIds = $tasks->pluck('mt_dep_id')->unique()->toArray();

        $recipients = DB::table('app_users')
            ->whereIn('user_dep_id', $depIds)
            ->whereNotNull('user_email')
            ->select('user_email', 'user_dep_id')
            ->get()
            ->groupBy('user_email');

        $emailsQueued = 0;

        foreach ($recipients as $email => $group) {
            $recipientDepIds = $group->pluck('user_dep_id')->unique()->toArray();
            $recipientTasks = $tasks->filter(fn($task) => in_array($task->mt_dep_id, $recipientDepIds));

            if ($recipientTasks->isEmpty()) {
                continue;
            }

            Mail::to($email)
                ->queue(new MaintenanceTaskDueMail($recipientTasks));

            $emailsQueued++;
        }

        Log::info('[DueDateChecks] Queued notification emails', [
            'emails_queued' => $emailsQueued,
            'task_count' => $tasks->count(),
        ]);
    }

    // ----------------------------------------------------------------
    // Step 3: Check mt_due_dt, mark overdue, log it
    // ----------------------------------------------------------------
    private function processMaintenanceTasks($now): void
    {
        DB::table('maintenance_tasks')
            ->where('mt_due_dt', '<=', $now)
            ->whereNotNull('mt_due_dt')
            ->whereNull('mt_closed_dt')
            ->whereNotIn('mt_status_id', ['cnc', 'cmp'])  // ✅ your status strings
            ->select('mt_id', 'mt_status_id', 'mt_due_dt')
            ->orderBy('mt_due_dt')
            ->chunk(200, function ($tasks) use ($now) {
                $ids = $tasks->pluck('mt_id')->toArray();

                DB::table('maintenance_tasks')
                    ->whereIn('mt_id', $ids)
                    ->update([
                        'mt_status_id' => 'pnd',       // ✅ your status string
                    ]);

                $logs = $tasks->map(fn($task) => [
                    'mtl_mt_id'     => $task->mt_id,
                    'mtl_status_id' => 'pnd',          // ✅ your status string
                    'mtl_remarks'   => 'Automatically marked as overdue by system',
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
}
