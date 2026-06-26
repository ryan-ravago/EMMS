<?php

namespace App\Console\Commands;

use App\Services\Maintenance\OverdueMaintenanceNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendOverdueMaintenanceTaskNotifications extends Command
{
    protected $signature = 'maintenance:notify-overdue-tasks';

    protected $description = 'Send overdue maintenance task emails to managers.';

    public function handle(OverdueMaintenanceNotificationService $service): int
    {
        $processed = $service->notify(Carbon::now());

        $this->info("Processed {$processed} overdue maintenance notification email(s).");

        return self::SUCCESS;
    }
}
