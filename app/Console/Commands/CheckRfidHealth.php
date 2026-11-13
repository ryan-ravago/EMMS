<?php

namespace App\Console\Commands;

use App\Mail\RfidHealthAlertMail;
use App\Models\RfidHealthState;
use App\Services\Rfid\RfidAlertSender;
use App\Services\Rfid\RfidHealthCheck;
use App\Services\Rfid\RfidHealthReport;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;

class CheckRfidHealth extends Command
{
    /** How long to wait before warning again that the reader token is about to expire. */
    private const TOKEN_WARNING_REPEAT_DAYS = 7;

    protected $signature = 'rfid:check-health';

    protected $description = 'Check that the RFID reader server is getting scans to EMS and alert (email and in the app) when it is not';

    public function handle(RfidHealthCheck $check): int
    {
        $report = $check->inspect();

        $this->table(['Check', 'Result'], [
            ['Status', $report->status.($report->cause ? " ({$report->cause})" : '')],
            ['Reader server last contacted EMS', $report->lastRequestAt?->toDateTimeString() ?? 'Never'],
            ['Minutes since the last stored scan', $report->minutesSinceScan ?? 'Never'],
            ['Last token expires', $report->tokenExpiresAt?->toDateString() ?? 'Never'],
        ]);

        if (! config('rfid.alerts.enabled')) {
            $this->info('Alerts are off (RFID_ALERTS_ENABLED). Nothing was sent.');

            return self::SUCCESS;
        }

        $state = RfidHealthState::current();
        $now = now();

        $report->isHealthy()
            ? $this->handleHealthy($state, $report)
            : $this->handleProblem($state, $report, $now);

        $this->handleTokenWarning($state, $report, $now);

        return self::SUCCESS;
    }

    private function handleHealthy(RfidHealthState $state, RfidHealthReport $report): void
    {
        if ($state->status === RfidHealthReport::OK) {
            return;
        }

        // Only tell people it is fixed if they were told it was broken.
        if ($state->last_notified_at !== null) {
            $this->send(
                RfidHealthAlertMail::RECOVERED,
                'The RFID reader is working again',
                'Scans from the reader server are being received again.',
                $this->details($report, $state),
                [],
            );
        }

        $state->update(['status' => RfidHealthReport::OK, 'cause' => null, 'since' => null, 'last_notified_at' => null]);
    }

    private function handleProblem(RfidHealthState $state, RfidHealthReport $report, CarbonInterface $now): void
    {
        // A new or different problem starts a new episode, so it is announced right away.
        if ($state->status !== $report->status || $state->cause !== $report->cause) {
            $state->fill(['status' => $report->status, 'cause' => $report->cause, 'since' => $now, 'last_notified_at' => null]);
        }

        $reminderDue = $state->last_notified_at === null
            || $state->last_notified_at->lte($now->copy()->subHours((int) config('rfid.alerts.reminder_hours')));

        if ($reminderDue && app(RfidAlertSender::class)->withinAlertWindow($now)) {
            $this->send(
                $state->last_notified_at === null ? RfidHealthAlertMail::PROBLEM : RfidHealthAlertMail::REMINDER,
                $report->headline(),
                $report->explanation(),
                $this->details($report, $state),
                $report->steps(),
            );

            $state->last_notified_at = $now;
        }

        $state->save();
    }

    private function handleTokenWarning(RfidHealthState $state, RfidHealthReport $report, CarbonInterface $now): void
    {
        if (! $report->tokenExpiresWithin((int) config('rfid.alerts.token_warning_days'))) {
            $state->token_warning_sent_at === null || $state->update(['token_warning_sent_at' => null]);

            return;
        }

        $due = $state->token_warning_sent_at === null
            || $state->token_warning_sent_at->lte($now->copy()->subDays(self::TOKEN_WARNING_REPEAT_DAYS));

        if (! $due || ! app(RfidAlertSender::class)->withinAlertWindow($now)) {
            return;
        }

        $this->send(
            RfidHealthAlertMail::TOKEN_EXPIRING,
            'The RFID reader token expires soon',
            'When it expires the reader server can no longer send scans to EMS.',
            ['Token expires on' => $report->tokenExpiresAt->format('M d, Y')],
            ['On the EMS server run: php artisan rfid:issue-token --revoke', 'Put the new token on the reader server (it is shown only once). New tokens do not expire.'],
        );

        $state->update(['token_warning_sent_at' => $now]);
    }

    /**
     * @return array<string, string>
     */
    private function details(RfidHealthReport $report, RfidHealthState $state): array
    {
        return array_filter([
            'Problem since' => $state->since?->format('M d, Y h:i A'),
            'Reader server last contacted EMS' => $report->lastRequestAt?->format('M d, Y h:i A') ?? 'Never',
            'Last scan stored' => $report->minutesSinceScan === null ? 'Never' : $report->minutesSinceScan.' minute(s) ago',
        ]);
    }

    /**
     * @param  array<string, string>  $details
     * @param  list<string>  $steps
     */
    private function send(string $kind, string $headline, string $explanation, array $details, array $steps): void
    {
        app(RfidAlertSender::class)->send($kind, $headline, $explanation, $details, $steps);

        $this->info("Sent: {$headline}");
    }
}
