<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Services\Google\GmailWorkOrderReplySyncService;
use Illuminate\Console\Command;

class SyncGoogleWorkOrderReplies extends Command
{
    protected $signature = 'work-orders:sync-google-replies {--email= : Google mailbox account email override}';

    protected $description = 'Sync Gmail replies into work order log updates.';

    public function handle(GmailWorkOrderReplySyncService $service): int
    {
        $email = $this->option('email') ?: config('work_orders.gmail_account_email');

        $mailboxUser = $email
            ? AppUser::query()->whereRaw('lower(user_email) = ?', [strtolower($email)], 'and')->first()
            : AppUser::query()->whereNotNull('google_refresh_token', 'and')->orderBy('user_id', 'desc')->first();

        if (! $mailboxUser) {
            $this->warn('No Google mailbox user was found.');

            return self::SUCCESS;
        }

        try {
            $processed = $service->syncMailbox($mailboxUser);

            $this->info("Processed {$processed} Gmail reply message(s).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
