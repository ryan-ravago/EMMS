<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Action as ModelsAction;
use App\Models\Equipment;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewEquipment extends ViewRecord
{
    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return array_merge(parent::getListeners(), [
            'equipment-tags-updated' => '$refresh',
        ]);
    }

    protected static string $resource = EquipmentResource::class;

    public function getSubheading(): ?string
    {
        $record = $this->getRecord();
        $location = $record->location?->full_path ?? 'No location';
        $lifecycleStatus = $record->lifecycleStatus?->status_title ?? 'Unknown status';

        return "Location: {$location} | Lifecycle status: {$lifecycleStatus}";
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $headerActions = [];

        if ($record->eqm_is_active) {
            $allocateActionModel = ModelsAction::where('a_id', 'alc')->firstOrFail();
            $deployActionModel = ModelsAction::where('a_id', 'dep')->firstOrFail();
            $setIdleActionModel = ModelsAction::where('a_id', 'sidle')->firstOrFail();
            $setUnderMaintenanceActionModel = ModelsAction::where('a_id', 'smt')->firstOrFail();

            $headerActions[] = ActionGroup::make([
                Action::make('allocate')
                    ->label($allocateActionModel->a_present_tense)
                    ->icon($allocateActionModel->a_icon)
                    ->color('success')
                    ->visible(fn() => Auth::user()->can('allocate', $record))
                    ->modalHeading("Mark as {$allocateActionModel->a_present_tense}")
                    ->modalIcon($allocateActionModel->a_icon)
                    ->modalWidth('md')
                    ->modalSubmitActionLabel('Submit')
                    ->schema([
                        Select::make('allocate_to_equipment_id')
                            ->label('Allocate To Equipment')
                            ->options(fn() => Equipment::where('asset_type_id', 1)->pluck('eqm_name', 'eqm_id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(fn(array $data) => $this->saveLifecycleTransition($allocateActionModel, 'alc', $data)),

                Action::make('deploy')
                    ->label($deployActionModel->a_present_tense)
                    ->icon($deployActionModel->a_icon)
                    ->color('success')
                    ->visible(fn() => Auth::user()->can('deploy', $record))
                    ->modalHeading("Mark as {$deployActionModel->a_present_tense}")
                    ->modalIcon($deployActionModel->a_icon)
                    ->modalWidth('md')
                    ->modalSubmitActionLabel('Submit')
                    ->schema([
                        SelectTree::make('location_id')
                            ->label('Location')
                            ->withCount()
                            ->searchable()
                            ->enableBranchNode()
                            ->default(fn() => $this->record->location_id)
                            ->relationship('location', 'name', 'parent_id'),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(fn(array $data) => $this->saveLifecycleTransition($deployActionModel, 'dep', $data)),

                Action::make('setToIdle')
                    ->label($setIdleActionModel->a_present_tense)
                    ->icon($setIdleActionModel->a_icon)
                    ->color('danger')
                    ->visible(fn() => Auth::user()->can('setToIdle', $record))
                    ->modalHeading("Mark as {$setIdleActionModel->a_present_tense}")
                    ->modalIcon($setIdleActionModel->a_icon)
                    ->modalWidth('md')
                    ->modalSubmitActionLabel('Submit')
                    ->schema([
                        SelectTree::make('location_id')
                            ->label('Location')
                            ->withCount()
                            ->searchable()
                            ->enableBranchNode()
                            ->default(fn() => $this->record->location_id)
                            ->relationship('location', 'name', 'parent_id'),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(fn(array $data) => $this->saveLifecycleTransition($setIdleActionModel, 'idle', $data)),

                Action::make('setToMaintenance')
                    ->label($setUnderMaintenanceActionModel->a_present_tense)
                    ->icon($setUnderMaintenanceActionModel->a_icon)
                    ->color('primary')
                    ->visible(fn() => Auth::user()->can('setToMaintenance', $record))
                    ->modalHeading("Mark as {$setUnderMaintenanceActionModel->a_present_tense}")
                    ->modalIcon($setUnderMaintenanceActionModel->a_icon)
                    ->modalWidth('md')
                    ->modalSubmitActionLabel('Submit')
                    ->schema([
                        SelectTree::make('location_id')
                            ->label('Location')
                            ->withCount()
                            ->searchable()
                            ->enableBranchNode()
                            ->default(fn() => $this->record->location_id)
                            ->relationship('location', 'name', 'parent_id'),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(fn(array $data) => $this->saveLifecycleTransition($setUnderMaintenanceActionModel, 'udmt', $data)),
            ])
                ->label('More actions')
                ->button()
                ->color('gray');
        }

        $headerActions[] = EditAction::make();

        return $headerActions;
    }

    protected function saveLifecycleTransition(ModelsAction $action, string $newStatusId, array $data): void
    {
        try {
            DB::transaction(function () use ($action, $newStatusId, $data) {
                /*
                * Lock the equipment row so another lifecycle
                * action cannot modify it simultaneously.
                */
                $record = Equipment::query()
                    ->lockForUpdate()
                    ->findOrFail($this->record->getKey());

                $record->update([
                    'lifecycle_status_id' => $newStatusId,
                    'location_id' => match ($action->a_id) {
                        'dep', 'sidle', 'smt' => $data['location_id'] ?? $record->location_id,
                        default => $record->location_id,
                    },
                    'parent_id' => match ($action->a_id) {
                        'alc' => $data['allocate_to_equipment_id'] ?? null,
                        'sidle', 'smt' => null,
                        default => $record->parent_id,
                    },
                ]);

                $record->lifecycleLogs()->create([
                    'asset_id' => $record->eqm_id,
                    'action_id' => $action->a_id,
                    'status_id' => $newStatusId,
                    'deploy_to_loc_id' => $data['location_id'] ?? null,
                    'allocate_to_equipment_id' => $data['allocate_to_equipment_id'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'performed_by' => auth()->id(),
                    'logged_at' => now(),
                ]);

                activity()
                    ->performedOn($record)
                    ->withProperties([
                        'action_id' => $action->a_id,
                        'status_id' => $newStatusId,
                        'location_id' => $data['location_id'] ?? null,
                        'allocate_to_equipment_id' => $data['allocate_to_equipment_id'] ?? null,
                        'remarks' => $data['remarks'] ?? null,
                    ])
                    ->log("Marked as {$action->a_present_tense}");
            });

            // $this->record->refresh();

            $this->redirect(EquipmentResource::getUrl('view', ['record' => $this->record->eqm_id]), navigate: true);

            Notification::make()
                ->title("Marked as {$action->a_present_tense}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Something went wrong')
                ->body('The lifecycle action could not be completed. Please try again.')
                ->danger()
                ->send();
        }
    }
}
