<?php

namespace App\Console\Commands;

use App\Mail\MaintenanceDueMail;
use App\Models\AppUser;
use App\Models\Equipment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckDueMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check-due';
    protected $description = 'Check for equipment due for preventive maintenance and notify PREV department managers';


    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Fetch PREV department managers ONCE
            $prevManagers = AppUser::whereHas(
                'roles',
                fn($q) => $q->where('name', 'manager')
            )
                ->whereHas(
                    'department',
                    fn($q) => $q->where('dep_code', 'PREV')
                )
                ->get();

            if ($prevManagers->isEmpty()) {
                $this->warn('❌ No managers found in PREV department');
                Log::warning('CheckDueMaintenanceCommand: No managers found in PREV department');
                return self::FAILURE;
            }

            $this->info('ℹ️ Found ' . $prevManagers->count() . ' manager(s) in PREV department');


            // Get equipment due for maintenance
            $dueEquipment = Equipment::where('eqm_is_active', true)
                ->whereNotNull('eqm_next_pm_due_at')
                ->where('eqm_next_pm_due_at', '<=', now())
                ->where(function ($query) {
                    $query->whereNull('eqm_last_pm_notified_at')
                        ->orWhere('eqm_last_pm_notified_at', '<', now()->subDay());
                })
                ->with(['type', 'equipmentModel'])  // ✅ Direct relationships
                ->get();


            if ($dueEquipment->isEmpty()) {
                $this->info('ℹ️ No equipment due for maintenance');
                Log::info('CheckDueMaintenanceCommand: No equipment due for maintenance');
                return self::SUCCESS;
            }

            $this->info('ℹ️ Found ' . $dueEquipment->count() . ' equipment due for maintenance');

            // Get manager emails (filter out null emails)
            $managerEmails = $prevManagers
                ->pluck('user_email')
                ->filter()
                ->toArray();

            if (empty($managerEmails)) {
                $this->warn('❌ No valid email addresses found for managers');
                Log::warning('CheckDueMaintenanceCommand: No valid manager emails found');
                return self::FAILURE;
            }

            // Send ONE email to all managers
            $dueEquipment->each(function ($equipment) use ($managerEmails) {
                try {
                    Mail::to($managerEmails)
                        ->queue(new MaintenanceDueMail($equipment));

                    // Mark as notified
                    $equipment->update(['eqm_last_pm_notified_at' => now()->toDateString()]);
                    $this->info("✓ Notified ({$managerEmails[0]}) for: {$equipment->eqm_name}");
                    Log::info("CheckDueMaintenanceCommand: Notified managers for {$equipment->eqm_name}");
                } catch (\Exception $e) {
                    $this->error("✗ Failed to notify for: {$equipment->eqm_name}");
                    Log::error("CheckDueMaintenanceCommand: Failed to notify for {$equipment->eqm_name}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            });

            $this->info('✓ Maintenance check completed successfully');
            Log::info('CheckDueMaintenanceCommand: Completed successfully');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Command failed: ' . $e->getMessage());
            Log::error('CheckDueMaintenanceCommand: Command failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return self::FAILURE;
        }
    }
}
