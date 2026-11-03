<?php

namespace App\Console\Commands;

use App\Mail\RfidHealthAlertMail;
use App\Models\RfidScannerStatus;
use App\Services\Rfid\RfidAlertSender;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;

class CheckRfidScanners extends Command
{
    private const CAUSE_INACTIVE = 'inactive';

    private const CAUSE_NO_REPORT = 'no_report';

    protected $signature = 'rfid:check-scanners';

    protected $description = 'Alert (email and in the app) when a gate scanner is reported inactive, or stops being reported';

    public function handle(RfidAlertSender $sender): int
    {
        $scanners = RfidScannerStatus::query()->with('yard')->orderBy('yard_id')->orderBy('scanner_name')->get();

        $this->table(
            ['Scanner', 'Status', 'Since', 'Last report (min ago)'],
            $scanners->map(fn (RfidScannerStatus $scanner): array => [
                $scanner->label(),
                RfidScannerStatus::stateLabels()[$scanner->state()],
                $scanner->status_changed_at->toDateTimeString(),
                $scanner->minutesSinceLastReport(),
            ])->all(),
        );

        if (! config('rfid.alerts.enabled')) {
            $this->info('Alerts are off (RFID_ALERTS_ENABLED). Nothing was sent.');

            return self::SUCCESS;
        }

        $now = now();

        foreach ($scanners as $scanner) {
            $cause = $this->problemOf($scanner, $now);

            $cause === null
                ? $this->handleHealthy($scanner, $sender)
                : $this->handleProblem($scanner, $cause, $sender, $now);
        }

        return self::SUCCESS;
    }

    /**
     * Inactive only counts once it has lasted a while, so a scanner that corrects itself on the next report is not announced.
     */
    private function problemOf(RfidScannerStatus $scanner, CarbonInterface $now): ?string
    {
        return match ($scanner->state()) {
            RfidScannerStatus::NO_REPORT => self::CAUSE_NO_REPORT,
            RfidScannerStatus::INACTIVE => $scanner->status_changed_at->lte($now->copy()->subMinutes((int) config('rfid.alerts.scanner_inactive_minutes')))
                ? self::CAUSE_INACTIVE
                : null,
            default => null,
        };
    }

    private function handleHealthy(RfidScannerStatus $scanner, RfidAlertSender $sender): void
    {
        // An inactive scanner still inside its grace period keeps any open episode as it is.
        if ($scanner->alert_cause === null || $scanner->state() === RfidScannerStatus::INACTIVE) {
            return;
        }

        // Only tell people it is fixed if they were told it was broken.
        if ($scanner->last_notified_at !== null) {
            $sender->send(
                RfidHealthAlertMail::RECOVERED,
                "Scanner {$scanner->label()} is working again",
                'The reader server reports this scanner as active again.',
                $this->details($scanner),
            );

            $this->info("Sent: {$scanner->label()} recovered");
        }

        $scanner->update(['alert_cause' => null, 'alert_since' => null, 'last_notified_at' => null]);
    }

    private function handleProblem(RfidScannerStatus $scanner, string $cause, RfidAlertSender $sender, CarbonInterface $now): void
    {
        // A new or different problem starts a new episode, so it is announced right away.
        if ($scanner->alert_cause !== $cause) {
            $scanner->fill(['alert_cause' => $cause, 'alert_since' => $now, 'last_notified_at' => null]);
        }

        $reminderDue = $scanner->last_notified_at === null
            || $scanner->last_notified_at->lte($now->copy()->subHours((int) config('rfid.alerts.reminder_hours')));

        if ($reminderDue && $sender->withinAlertWindow($now)) {
            $sender->send(
                $scanner->last_notified_at === null ? RfidHealthAlertMail::PROBLEM : RfidHealthAlertMail::REMINDER,
                $this->headline($scanner, $cause),
                $this->explanation($cause),
                $this->details($scanner),
                $this->steps($cause),
            );

            $scanner->last_notified_at = $now;
            $this->info("Sent: {$this->headline($scanner, $cause)}");
        }

        $scanner->save();
    }

    private function headline(RfidScannerStatus $scanner, string $cause): string
    {
        return $cause === self::CAUSE_INACTIVE
            ? "Scanner {$scanner->label()} is inactive"
            : "No status reports for scanner {$scanner->label()}";
    }

    private function explanation(string $cause): string
    {
        return $cause === self::CAUSE_INACTIVE
            ? 'The reader server reports that this gate scanner is not working, so equipment passing the gate may not be recorded.'
            : 'EMMS has not received a status report for this scanner for a while. The reader server may have stopped sending its status, or may be offline.';
    }

    /**
     * @return list<string>
     */
    private function steps(string $cause): array
    {
        return $cause === self::CAUSE_INACTIVE
            ? [
                'Check that the scanner is powered and connected to the reader server.',
                'Check the reader server for errors on this scanner.',
            ]
            : [
                'Check that the reader server is on and has internet access.',
                'Check that it still has the current token and the right EMMS address.',
            ];
    }

    /**
     * @return array<string, string>
     */
    private function details(RfidScannerStatus $scanner): array
    {
        return array_filter([
            'Scanner' => $scanner->label(),
            'Status since' => $scanner->status_changed_at->format('M d, Y h:i A'),
            'Last report' => $scanner->minutesSinceLastReport().' minute(s) ago',
            'Problem since' => $scanner->alert_since?->format('M d, Y h:i A'),
        ]);
    }
}
