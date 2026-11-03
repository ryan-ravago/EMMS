<?php

namespace App\Services\Rfid;

use App\Mail\RfidHealthAlertMail;
use App\Models\AppUser;
use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Tells people about RFID problems: an email to the configured addresses and an in-app notification to the
 * super admins. Shared by every RFID check (reader server health, scanner status) so they alert the same way.
 */
class RfidAlertSender
{
    /**
     * Email the configured addresses and notify the super admins in the app. A failure to deliver is reported
     * but never stops the caller, so the other channel and the caller's own bookkeeping still happen.
     *
     * @param  'problem'|'reminder'|'recovered'|'token_expiring'  $kind
     * @param  array<string, string>  $details
     * @param  list<string>  $steps
     */
    public function send(string $kind, string $headline, string $explanation, array $details = [], array $steps = []): void
    {
        $recipients = $this->recipients();

        if ($recipients !== []) {
            try {
                Mail::to($recipients)->send(new RfidHealthAlertMail($kind, $headline, $explanation, $details, $steps));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        try {
            $notification = Notification::make()->title($headline)->body($explanation);
            $kind === RfidHealthAlertMail::RECOVERED ? $notification->success() : $notification->danger();

            $notification->sendToDatabase(AppUser::query()->role('super_admin')->where('is_active', true)->get());
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Alerts are only sent on the configured days, between the configured times (app timezone), so nobody is
     * woken for a yard that is closed. A window that ends before it starts runs overnight.
     */
    public function withinAlertWindow(CarbonInterface $now): bool
    {
        $days = collect(explode(',', (string) config('rfid.alerts.days')))->map(fn (string $day): int => (int) trim($day));

        if (! $days->contains($now->isoWeekday())) {
            return false;
        }

        $start = (string) config('rfid.alerts.window_start');
        $end = (string) config('rfid.alerts.window_end');
        $time = $now->format('H:i');

        return $start <= $end
            ? $time >= $start && $time <= $end
            : $time >= $start || $time <= $end;
    }

    /**
     * RFID_ALERT_EMAILS is typed by hand, so tolerate spaces and blanks and drop anything that is not an address.
     *
     * @return list<string>
     */
    private function recipients(): array
    {
        return collect(explode(',', (string) config('rfid.alerts.emails')))
            ->map(fn (string $email): string => trim($email))
            ->filter(fn (string $email): bool => $email !== '' && ! Validator::make(['email' => $email], ['email' => 'email'])->fails())
            ->unique()
            ->values()
            ->all();
    }
}
