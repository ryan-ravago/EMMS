<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Action as ModelsAction;
use App\Models\Equipment;
use App\Models\Location;
use App\Models\Status;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewEquipment extends ViewRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $headerActions = [];

        if ($record->eqm_is_active) {
            $headerActions[] = ActionGroup::make([
                $this->allocateAction(),
                $this->deployAction(),
                $this->setIdleAction(),
                $this->setUnderMaintenanceAction(),
            ])
                ->label('More actions')
                ->button()
                ->color('gray');
        }

        $headerActions[] = EditAction::make();

        return $headerActions;
    }

    protected function allocateAction(): Action
    {
        $action = ModelsAction::where('a_id', 'alc')->firstOrFail();

        return Action::make('markAsalc')
            ->label($action->a_present_tense)
            ->icon($action->a_icon)
            ->color('success')
            ->visible(fn() => $this->record->asset_type_id === 2
                && $this->record->lifecycle_status_id !== 'alc')
            ->modalHeading("Mark as {$action->a_present_tense}")
            ->modalIcon($action->a_icon)
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
            ->action(fn(array $data) => $this->saveLifecycleTransition($action, 'alc', $data));
    }

    protected function deployAction(): Action
    {
        $action = ModelsAction::where('a_id', 'dep')->firstOrFail();

        return Action::make('markAsdep')
            ->label($action->a_present_tense)
            ->icon($action->a_icon)
            ->color('success')
            ->visible(fn() => $this->record->asset_type_id === 1
                && $this->record->lifecycle_status_id !== 'dep')
            ->modalHeading("Mark as {$action->a_present_tense}")
            ->modalIcon($action->a_icon)
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
            ->action(fn(array $data) => $this->saveLifecycleTransition($action, 'dep', $data));
    }

    protected function setIdleAction(): Action
    {
        $action = ModelsAction::where('a_id', 'sidle')->firstOrFail();

        return Action::make('markAssidle')
            ->label($action->a_present_tense)
            ->icon($action->a_icon)
            ->color('danger')
            ->visible(fn() => $this->record->lifecycle_status_id !== 'idle')
            ->modalHeading("Mark as {$action->a_present_tense}")
            ->modalIcon($action->a_icon)
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
            ->action(fn(array $data) => $this->saveLifecycleTransition($action, 'idle', $data));
    }

    protected function setUnderMaintenanceAction(): Action
    {
        $action = ModelsAction::where('a_id', 'smt')->firstOrFail();

        return Action::make('markAssmt')
            ->label($action->a_present_tense)
            ->icon($action->a_icon)
            ->color('primary')
            ->visible(fn() => $this->record->lifecycle_status_id !== 'udmt')
            ->modalHeading("Mark as {$action->a_present_tense}")
            ->modalIcon($action->a_icon)
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
            ->action(fn(array $data) => $this->saveLifecycleTransition($action, 'udmt', $data));
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
                    'parent_id' => match ($action->a_id) {
                        'alc'   => $data['allocate_to_equipment_id'] ?? null,
                        'sidle' => null,
                        default => $record->parent_id,
                    },
                ]);

                $record->lifecycleLogs()->create([
                    'asset_id'  => $record->eqm_id,
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

            $this->record->refresh();

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
