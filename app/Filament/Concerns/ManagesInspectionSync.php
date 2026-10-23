<?php

namespace App\Filament\Concerns;

use App\Models\AppSetting;
use App\Services\Inspections\InspectionSyncRunner;
use App\Support\Activity\ActivityLabels;
use App\Support\Activity\ActivityLogging;
use App\Support\InspectionSyncSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Throwable;

/**
 * The body of an inspection sync manager page: a "Sync Now" button, the daily schedule time, and the log of
 * recent syncs. The page decides which sync it manages (maintenance or AIMS) and who may use it.
 */
trait ManagesInspectionSync
{
    public ?array $data = [];

    /**
     * @return 'maintenance'|'aims'
     */
    abstract protected static function syncKind(): string;

    /** What is being synced, for sentences: "maintenance inspections". */
    abstract protected static function syncSubject(): string;

    public function mount(): void
    {
        $this->form->fill(['value' => InspectionSyncSettings::time(static::syncKind())]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TimePicker::make('value')
                    ->label('Daily Sync Time')
                    ->seconds(false)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function saveSchedule(): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();
        $key = InspectionSyncSettings::key(static::syncKind());
        $task = ActivityLogging::inspectionSyncTask(static::syncKind());

        $oldTime = $this->formatTime(InspectionSyncSettings::time(static::syncKind()));
        $newTime = $this->formatTime($data['value']);

        // The automatic model row would only say "Updated App Setting"; the row below says what it means.
        activity()->withoutLogs(fn () => AppSetting::set($key, $data['value']));

        if ($oldTime !== $newTime) {
            activity('Sync')
                ->event('schedule_changed')
                ->withProperties(['task' => $task, 'from' => $oldTime, 'to' => $newTime])
                ->log("{$task} daily schedule changed from {$oldTime} to {$newTime}");
        }

        Notification::make()
            ->title('Schedule Updated')
            ->body(ucfirst(static::syncSubject()).' will now sync daily at '.Carbon::parse($data['value'])->format('g:ia'))
            ->success()
            ->send();
    }

    /** Sync runs (manual and scheduled) and schedule changes, latest first. */
    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Syncs')
            ->description('Manual and scheduled runs, and changes to the daily schedule. Full history is in the Activity Log.')
            ->query(
                Activity::query()
                    ->with('causer')
                    ->where('properties->task', ActivityLogging::inspectionSyncTask(static::syncKind()))
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M d, Y h:i A')
                    ->description(fn (Activity $record): string => $record->created_at->diffForHumans())
                    ->sortable(),

                TextColumn::make('trigger')
                    ->label('Trigger')
                    ->state(fn (Activity $record): string => $record->event === 'schedule_changed'
                        ? 'Schedule'
                        : (string) $record->getExtraProperty('trigger', '—'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Manual' => 'info',
                        'Scheduled', 'Schedule' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('causer_id')
                    ->label('By')
                    ->state(fn (Activity $record): string => $record->causer
                        ? trim("{$record->causer->user_fname} {$record->causer->user_lname}")
                        : 'Schedule'),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (Activity $record): string => $record->event === 'schedule_changed'
                        ? 'Changed'
                        : match ($record->getExtraProperty('status')) {
                            'success' => 'Success',
                            'failed' => 'Failed',
                            default => '—',
                        })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Success' => 'success',
                        'Failed' => 'danger',
                        'Changed' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Result')
                    ->wrap(),
            ])
            ->recordActions([
                Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Sync details')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (Activity $record) => view('filament.pages.activity-log-details', ActivityLabels::details($record))),
            ])
            ->recordAction('details')
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Now')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->authorize(fn (): bool => static::canAccess())
                ->requiresConfirmation()
                ->modalHeading('Sync '.ucfirst(static::syncSubject()))
                ->modalDescription('This reads the AppSheet Google Sheet and copies closed (Completed or Cancelled) '.static::syncSubject().' that are not in EMMS yet. Inspections already copied are never changed.')
                ->modalSubmitActionLabel('Yes, Sync Now')
                ->closeModalByClickingAway(false)
                ->closeModalByEscaping(false)
                ->action(function (): void {
                    abort_unless(static::canAccess(), 403);

                    $startedAt = microtime(true);

                    try {
                        $result = app(InspectionSyncRunner::class)->run(static::syncKind());
                    } catch (Throwable $exception) {
                        ActivityLogging::inspectionSync(static::syncKind(), 'Manual', 'failed', error: $exception->getMessage(), startedAt: $startedAt);

                        Notification::make()
                            ->title('Sync Failed')
                            ->body('Could not read the AppSheet sheet: '.$exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $failed = $result['failed'] > 0;

                    ActivityLogging::inspectionSync(static::syncKind(), 'Manual', $failed ? 'failed' : 'success', $result, $failed ? "{$result['failed']} inspection(s) could not be saved" : null, $startedAt);

                    Notification::make()
                        ->title($failed ? 'Sync Finished With Errors' : 'Sync Complete')
                        ->body("Imported: {$result['imported']}. Already imported: {$result['skipped']}. Still open in AppSheet: {$result['open']}.".($failed ? " Failed: {$result['failed']}." : ''))
                        ->color($failed ? 'warning' : 'success')
                        ->icon($failed ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                        ->send();
                }),
        ];
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? Carbon::parse($time)->format('g:i A') : null;
    }
}
