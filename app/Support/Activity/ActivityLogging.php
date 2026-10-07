<?php

namespace App\Support\Activity;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Events\ActionCalled;
use Filament\Actions\Events\ActionCalling;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\Models\Activity;
use Throwable;

/**
 * One place that makes every activity_log row readable, whether it came from a model event
 * or a hand-written activity() call:
 *
 *  - snapshots the record's name (still known after the record is deleted),
 *  - notes what triggered it (a Filament action, a bulk action, a queued job, a scheduled command),
 *  - groups rows of one bulk action into a batch (batch_uuid),
 *  - rewrites the description into a full sentence.
 *
 * Logging must never break the real work, so everything here is wrapped in try/catch.
 */
class ActivityLogging
{
    private static ?string $via = null;

    private static bool $bulk = false;

    public static function register(): void
    {
        /** @var class-string<Activity> $activityModel */
        $activityModel = config('activitylog.activity_model');
        $activityModel::creating(fn (Activity $activity) => self::enrich($activity));

        // Filament actions: record, bulk action, header action, custom action...
        Event::listen(ActionCalling::class, function (mixed $event): void {
            $action = self::actionFrom($event);

            if (! $action) {
                return;
            }

            $bulk = $action instanceof BulkAction;

            if ($bulk) {
                LogBatch::startBatch();
            }

            self::begin(($bulk ? 'Bulk action: ' : 'Action: ').self::actionLabel($action), $bulk);
        });

        Event::listen(ActionCalled::class, function (mixed $event): void {
            $action = self::actionFrom($event);

            if (! $action) {
                return;
            }

            if ($action instanceof BulkAction) {
                LogBatch::endBatch();
            }

            self::end();
        });

        // Queued jobs (the scheduler dispatches some of them).
        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            self::begin('Background job: '.Str::headline(class_basename($event->job->resolveName())));
        });

        Event::listen([JobProcessed::class, JobFailed::class, JobExceptionOccurred::class], fn () => self::end());

        // Artisan commands that are on the schedule. The scheduler runs them as a separate
        // process, so this has to be detected inside that process.
        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if ($event->command && self::isScheduledCommand($event->command)) {
                self::begin('Scheduled command: '.$event->command);
            }
        });

        Event::listen(CommandFinished::class, fn () => self::end());
    }

    /** A summary row for a scheduled task run ("SAP sync: 12 synced, 1 deactivated, 0 deleted"). */
    public static function scheduled(string $task, string $summary, array $properties = []): void
    {
        try {
            activity('Scheduled')
                ->causedByAnonymous()
                ->event('scheduled')
                ->withProperties(['task' => $task, ...$properties])
                ->log($summary);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * One row per SAP equipment sync run, whoever started it: the daily schedule or a person
     * pressing "Sync from SAP". Manual runs are credited to the logged-in user.
     *
     * @param  'Manual'|'Scheduled'  $trigger
     * @param  'queued'|'success'|'no_records'|'failed'  $status
     * @param  array{synced?: int, deactivated?: int, deleted?: int}  $counts
     */
    public static function sapSync(string $trigger, string $status, array $counts = [], ?string $error = null, ?float $startedAt = null): void
    {
        $duration = $startedAt !== null ? round(microtime(true) - $startedAt, 1) : null;
        $took = $duration !== null ? " in {$duration}s" : '';

        $result = match ($status) {
            'queued' => 'started by the daily schedule',
            'success' => ($counts['synced'] ?? 0).' synced, '.($counts['deactivated'] ?? 0).' deactivated, '.($counts['deleted'] ?? 0).' deleted'.$took,
            'no_records' => 'SAP returned no records'.$took,
            default => 'failed'.$took.($error ? ' — '.Str::limit($error, 300) : ''),
        };

        $summary = "SAP equipment sync ({$trigger}): {$result}";

        $properties = [
            'trigger' => $trigger,
            'status' => $status,
            ...$counts,
            ...($duration !== null ? ['duration_seconds' => $duration] : []),
            ...($error ? ['error' => Str::limit($error, 500)] : []),
        ];

        if ($trigger === 'Scheduled') {
            self::scheduled('SAP equipment sync', $summary, $properties);

            return;
        }

        try {
            activity('Sync')
                ->event($status === 'failed' ? 'sync_failed' : 'synced')
                ->withProperties(['task' => 'SAP equipment sync', ...$properties])
                ->log($summary);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private static function enrich(Activity $activity): void
    {
        try {
            $properties = ActivityLabels::properties($activity);
            $subject = $activity->relationLoaded('subject') ? $activity->getRelation('subject') : null;
            $subject = $subject instanceof Model ? $subject : null;

            if ($subject && ! $properties->has('subject_label')) {
                $properties->put('subject_label', ActivityLabels::subjectLabel($subject));
            }

            if (self::$via !== null && $activity->event !== 'scheduled') {
                $properties->put('via', self::$via);

                if (self::$bulk) {
                    $properties->put('bulk', true);
                }
            }

            $activity->properties = $properties;
            $activity->description = ActivityLabels::describe($activity, $subject);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private static function begin(string $via, bool $bulk = false): void
    {
        self::$via = $via;
        self::$bulk = $bulk;
    }

    private static function end(): void
    {
        self::$via = null;
        self::$bulk = false;
    }

    private static function actionFrom(mixed $event): ?Action
    {
        // Filament dispatches these with the action itself as the payload.
        if ($event instanceof ActionCalling || $event instanceof ActionCalled) {
            $event = $event->getAction();
        }

        return $event instanceof Action ? $event : null;
    }

    private static function actionLabel(Action $action): string
    {
        try {
            $label = trim(strip_tags((string) $action->getLabel()));
        } catch (Throwable) {
            $label = '';
        }

        return $label !== '' ? $label : Str::headline((string) $action->getName());
    }

    private static function isScheduledCommand(string $name): bool
    {
        try {
            return collect(app(Schedule::class)->events())
                ->contains(fn ($event) => is_string($event->command) && str_contains($event->command, $name));
        } catch (Throwable) {
            return false;
        }
    }
}
