<?php

namespace App\Filament\Pages;

use App\Jobs\SyncEquipmentFromSap;
use App\Models\AppSetting;
use App\Models\Equipment;
use App\Models\OPRC;
use App\Support\Activity\ActivityLogging;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class SapSyncManager extends Page
{
    use HasPageShield, InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'SAP Sync Manager';
    protected static string | UnitEnum | null $navigationGroup = 'Super Admin';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.sap-sync-manager';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'value' => AppSetting::where('key', 'sap_sync_time')->value('value'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TimePicker::make('value')
                    ->label('Daily Sync Time')
                    ->seconds(false)        // HH:MM only
                    ->required(),
            ])
            ->statePath('data');
    }

    public function saveSchedule(): void
    {
        $data = $this->form->getState();

        $oldTime = $this->formatTime(AppSetting::where('key', 'sap_sync_time')->value('value'));
        $newTime = $this->formatTime($data['value']);

        // The automatic model row would only say "Updated App Setting"; the row below says what it means.
        activity()->withoutLogs(fn () => AppSetting::set('sap_sync_time', $data['value']));

        if ($oldTime !== $newTime) {
            activity('Sync')
                ->event('schedule_changed')
                ->withProperties(['task' => 'SAP equipment sync', 'from' => $oldTime, 'to' => $newTime])
                ->log($oldTime
                    ? "SAP daily sync schedule changed from {$oldTime} to {$newTime}"
                    : "SAP daily sync schedule set to {$newTime}");
        }

        Notification::make()
            ->title('Schedule Updated')
            ->body('SAP sync will now run daily at ' . Carbon::parse($data['value'])->format('g:ia'))
            ->success()
            ->send();
    }

    /** Latest sync runs (manual and scheduled) and schedule changes, newest first. */
    public function getRecentSyncs(): Collection
    {
        return Activity::query()
            ->with('causer')
            ->where('properties->task', 'SAP equipment sync')
            ->latest()
            ->limit(10)
            ->get();
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? Carbon::parse($time)->format('g:i A') : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync from SAP')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sync Equipment from SAP')
                ->modalDescription('This will fetch equipment data from SAP and update your local database.')
                ->modalSubmitActionLabel('Yes, Sync Now')
                ->closeModalByClickingAway(false)        // 👈 can't close by clicking outside
                ->closeModalByEscaping(false)            // 👈 can't close by pressing Escape
                ->action(function () {
                    $startedAt = microtime(true);

                    try {
                        DB::transaction(function () use ($startedAt) {
                            $sapRecords = OPRC::select([
                                'PrcCode',
                                'PrcName',
                                'Active',
                            ])->get();

                            if ($sapRecords->isEmpty()) {
                                ActivityLogging::sapSync('Manual', 'no_records', startedAt: $startedAt);

                                Notification::make()
                                    ->title('No Records Found')
                                    ->body('SAP returned no records to sync.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $data = $sapRecords
                                ->filter(fn($sap) => !empty($sap->PrcCode))
                                ->map(fn($sap) => [
                                    'eqm_prc_code'  => $sap->PrcCode,
                                    'eqm_name'      => $sap->PrcName,
                                    'eqm_is_active' => $sap->Active === 'Y' ? 1 : 0,
                                ])->toArray();

                            // 👇 get all PrcCodes from SAP
                            $sapPrcCodes = $sapRecords
                                ->filter(fn($sap) => !empty($sap->PrcCode))
                                ->pluck('PrcCode')
                                ->toArray();

                            // 👇 deactivate local records not found in SAP
                            $deactivated = Equipment::whereNotNull('eqm_prc_code')
                                ->whereNotIn('eqm_prc_code', $sapPrcCodes)
                                ->where('eqm_is_active', 1)
                                ->update(['eqm_is_active' => 0]);

                            Equipment::upsert(
                                $data,
                                ['eqm_prc_code'],
                                ['eqm_name', 'eqm_is_active']
                            );

                            AppSetting::where('key', 'sap_sync_time')
                                ->update([
                                    'last_equipment_sync' => now(),
                                ]);

                            ActivityLogging::sapSync('Manual', 'success', ['synced' => count($data), 'deactivated' => $deactivated], startedAt: $startedAt);

                            Notification::make()
                                ->title('SAP Sync Complete')
                                ->body("Synced: " . count($data) . " records. Deactivated: {$deactivated} records.")
                                ->success()
                                ->send();
                        });
                    } catch (\Illuminate\Database\QueryException $e) {
                        ActivityLogging::sapSync('Manual', 'failed', error: $e->getMessage(), startedAt: $startedAt);

                        $previous = $e->getPrevious();

                        if ($previous instanceof \PDOException) {
                            Notification::make()
                                ->title('SAP Connection Failed')
                                ->body('Could not connect to SAP database. Please check your network or server.')
                                ->danger()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Database Error')
                                ->body('Query failed: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        ActivityLogging::sapSync('Manual', 'failed', error: $e->getMessage(), startedAt: $startedAt);

                        Notification::make()
                            ->title('Sync Failed')
                            ->body('Unexpected error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
            // ->successNotificationTitle('Sync completed successfully'),
        ];
    }
}
