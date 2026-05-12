<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\ChecklistTemplates\ChecklistTemplateResource;
use App\Models\ChecklistTemplate;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecklistTemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistTemplates';

    protected static ?string $relatedResource = ChecklistTemplateResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('clt_name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('checklistUsageType.cut_name')
                    ->label('Usage Type')
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->sortable()
                    ->visible(fn() => auth()->user()->hasRole('super_admin')),
                TextColumn::make('clt_interval_years')
                    ->label('Interval')
                    ->formatStateUsing(function ($record) {
                        $parts = array_filter([
                            $record->clt_interval_years  ? "{$record->clt_interval_years}y"  : null,
                            $record->clt_interval_months ? "{$record->clt_interval_months}mo" : null,
                            $record->clt_interval_weeks  ? "{$record->clt_interval_weeks}w"  : null,
                            $record->clt_interval_days   ? "{$record->clt_interval_days}d"   : null,
                        ]);

                        $interval = $parts ? implode(' ', $parts) : '—';
                        $time = $record->clt_schedule_time
                            ? Carbon::parse($record->clt_schedule_time)->format('h:i A')
                            : null;

                        return $time ? "{$interval} @ {$time}" : $interval;
                    }),
                TextColumn::make('eca_due_dt')
                    ->label('Due Date')
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('M j, Y h:i A') : '-')
            ])
            ->headerActions([
                CreateAction::make(),
                Action::make('attachChecklistTemplateUnderEquipmentUnits')
                    ->label('Attach')
                    ->modalWidth('lg')
                    ->closeModalByClickingAway(false)
                    ->modalCloseButton(false)
                    ->schema([
                        Select::make('recordId')
                            ->label('Checklist Template')
                            ->options(function () {
                                $attached = $this->getOwnerRecord()->checklistTemplates()->pluck('clt_id')->toArray();

                                return ChecklistTemplate::with('checklistUsageType')
                                    ->whereNotIn('clt_id', $attached)
                                    ->orderBy('clt_name')
                                    ->get()
                                    ->mapWithKeys(fn($template) => [
                                        $template->clt_id => "{$template->clt_name} — {$template->checklistUsageType?->cut_name}"
                                    ]);
                            })
                            ->searchable()
                            ->live()
                            ->required(),
                        DateTimePicker::make('eca_due_effectivity_dt')
                            ->label('Effectivity Date & Time')
                            ->seconds(false)
                            ->displayFormat('M j, Y h:i A')
                            ->hidden(function (Get $get) {
                                $template = ChecklistTemplate::find($get('recordId'));
                                return !$template || $template->clt_cut_id != 2;
                            })
                    ])
                    ->action(function (array $data): void {
                        try {
                            $template = ChecklistTemplate::find($data['recordId']);
                            $owner = $this->getOwnerRecord();

                            $dueEffectivityDt = null;
                            $dueDt = null;

                            if ($template && $template->clt_cut_id == 2 && !empty($data['eca_due_effectivity_dt'])) {
                                $dueEffectivityDt = $data['eca_due_effectivity_dt'];

                                $dueDt = Carbon::parse($dueEffectivityDt)
                                    ->addYears($template->clt_interval_years ?? 0)
                                    ->addMonths($template->clt_interval_months ?? 0)
                                    ->addWeeks($template->clt_interval_weeks ?? 0)
                                    ->addDays($template->clt_interval_days ?? 0);

                                if (!empty($template->clt_schedule_time)) {
                                    $scheduleTime = Carbon::parse($template->clt_schedule_time);
                                    $dueDt->setTime($scheduleTime->hour, $scheduleTime->minute);
                                }
                            }

                            $owner->checklistTemplates()->attach($data['recordId'], [
                                'eca_due_effectivity_dt' => $dueEffectivityDt,
                                'eca_due_dt' => $dueDt,
                                'eca_assigned_by' => auth()->id(),
                                'eca_assigned_at' => now(),
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Template Attached')
                                ->body("{$template->clt_name} has been successfully attached.")
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Attachment Failed')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ])
            ->extraAttributes([
                'style' => 'margin-top: 30px;'
            ]);
    }
}
