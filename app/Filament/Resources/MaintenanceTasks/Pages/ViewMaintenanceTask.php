<?php

namespace App\Filament\Resources\MaintenanceTasks\Pages;

use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Filament\Resources\MaintenanceTasks\RelationManagers\MaintenanceTaskLogsRelationManager;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceTaskLog;
use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ViewMaintenanceTask extends ViewRecord
{
    protected static string $resource = MaintenanceTaskResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(MaintenanceTaskResource::getUrl('index')),
            Action::make('makeWorkOrder')
                ->label('Make Work Order')
                ->color('primary')
                ->modalWidth('lg')
                ->closeModalByClickingAway(false)
                ->modalHeading('Make Work Order')
                ->modalCloseButton(false)
                ->visible(fn(MaintenanceTask $record): bool => in_array($record->mt_status_id, ['snz', 'pnd']))
                ->schema([
                    Textarea::make('mtl_remarks')
                        ->label('Remarks')
                        ->required(),
                ])
                ->action(function () {
                    // Custom logic here
                }),
            Action::make('snooze')
                ->label('Snooze')
                ->color('info')
                ->modalWidth('lg')
                ->closeModalByClickingAway(false)
                ->modalHeading('Snooze Task')
                ->modalCloseButton(false)
                ->visible(fn(MaintenanceTask $record): bool => in_array($record->mt_status_id, ['pnd']))
                ->schema([
                    DatePicker::make('mtl_due_dt')
                        ->label('Extend Due Date')
                        ->displayFormat('M d, Y')
                        ->native(false)
                        ->required()
                        // ->minDate(fn(MaintenanceTask $record) => Carbon::parse($record->mt_due_dt)->addDay())
                        ->rules([
                            fn(MaintenanceTask $record) => function (string $attribute, $value, $fail) use ($record) {
                                if (Carbon::parse($value)->lte(Carbon::parse($record->mt_due_dt))) {
                                    $fail('New due date must be later than the current due date.');
                                }
                            },
                        ]),
                    Textarea::make('mtl_remarks')
                        ->label('Remarks')
                        ->required(),
                ])
                ->action(function (array $data, MaintenanceTask $record, Action $action) {
                    if (! in_array($record->mt_status_id, ['pnd'])) {
                        Notification::make()
                            ->title('Invalid Status')
                            ->body('Task must be Pending to be Snoozed.')
                            ->danger()
                            ->send();

                        $action->halt();
                        return;
                    }

                    try {
                        DB::transaction(function () use ($data, $record) {
                            $now = Carbon::now();

                            $record->update([
                                'mt_status_id' => 'snz',
                                'mt_due_dt' => $data['mtl_due_dt'],
                            ]);

                            MaintenanceTaskLog::create([
                                'mtl_mt_id' => $record->mt_id,
                                'mtl_status_id' => 'snz',
                                'mtl_due_dt' => $data['mtl_due_dt'],
                                'mtl_last_act_made' => 'snooze',
                                'mtl_remarks' => $data['mtl_remarks'],
                                'mtl_by' => auth()->id(),
                                'mtl_dt' => $now,
                            ]);
                        });

                        Notification::make()
                            ->title('Task Snoozed')
                            ->success()
                            ->send();

                        $this->js('Livewire.dispatch("refreshRelationManager")');
                    } catch (Throwable $th) {
                        Notification::make()
                            ->title('Action Failed')
                            ->body('An error occurred while updating the task. No changes were saved.' . '' . $th->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('markAsComplete')
                ->label('Mark as Complete')
                ->color('success')
                ->modalWidth('lg')
                ->closeModalByClickingAway(false)
                ->modalHeading('Complete Task')
                ->modalCloseButton(false)
                ->visible(fn(MaintenanceTask $record): bool => in_array($record->mt_status_id, ['snz', 'pnd']))
                ->schema([
                    Textarea::make('mtl_remarks')
                        ->label('Remarks')
                        ->required(),
                ])
                ->action(function (array $data, MaintenanceTask $record) {
                    if (! in_array($record->mt_status_id, ['snz', 'pnd'])) {
                        Notification::make()
                            ->title('Invalid Status')
                            ->body('Task must be Pending or Snoozed to be marked complete.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        DB::transaction(function () use ($data, $record) {
                            $now = Carbon::now();

                            $record->update([
                                'mt_status_id' => 'cmp',
                                'mt_closed_dt' => $now,
                            ]);

                            MaintenanceTaskLog::create([
                                'mtl_mt_id' => $record->mt_id,
                                'mtl_status_id' => 'cmp',
                                'mtl_last_act_made' => 'create',
                                'mtl_remarks' => $data['mtl_remarks'],
                                'mtl_by' => auth()->id(),
                                'mtl_dt' => $now,
                            ]);
                        });

                        Notification::make()
                            ->title('Task marked as complete')
                            ->success()
                            ->send();

                        $this->js('Livewire.dispatch("refreshRelationManager")');
                    } catch (Throwable $th) {
                        Notification::make()
                            ->title('Action Failed')
                            ->body('An error occurred while updating the task. No changes were saved.' . '' . $th->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
