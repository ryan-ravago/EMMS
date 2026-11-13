<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Services\Google\GmailWorkOrderReplySyncService;
use App\Support\Activity\ActivityLogging;
use Illuminate\Console\Command;

class SyncGoogleWorkOrderReplies extends Command
{
    protected $signature = 'work-orders:sync-google-replies {--email= : Read this mailbox instead of WORK_ORDER_GMAIL_ACCOUNT}';

    protected $description = 'Sync Gmail replies into work order updates.';

    public function handle(GmailWorkOrderReplySyncService $service): int
    {
        if (! config('work_orders.reply_sync_enabled')) {
            $this->line('The Gmail reply sync is switched off (WORK_ORDER_REPLY_SYNC_ENABLED).');

            return self::SUCCESS;
        }

        $email = trim((string) ($this->option('email') ?: config('work_orders.gmail_account_email')));

        // Never guess the mailbox: every user signs in with Google, so any of them could be picked.
        if ($email === '') {
            $this->warn('No mailbox is set. Put the mailbox address in WORK_ORDER_GMAIL_ACCOUNT (.env).');

            return self::SUCCESS;
        }

        $mailboxUser = AppUser::query()->whereRaw('lower(user_email) = ?', [strtolower($email)], 'and')->first();

        if (! $mailboxUser) {
            $this->warn("No EMS user has the email {$email}. The mailbox must be an EMS user.");

            return self::SUCCESS;
        }

        if (! $mailboxUser->google_refresh_token) {
            $this->warn("{$email} has not signed in to EMS with Google yet, so its inbox cannot be read. Sign in once with that account.");

            return self::SUCCESS;
        }

        try {
            $processed = $service->syncMailbox($mailboxUser);

            // Runs every minute: only record runs that actually did something.
            if ($processed > 0) {
                ActivityLogging::scheduled('Gmail reply sync', "Gmail reply sync: processed {$processed} reply message(s) into work order updates", ['processed' => $processed]);
            }

            $this->info("Processed {$processed} Gmail reply message(s).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
