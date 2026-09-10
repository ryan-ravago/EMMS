<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Action as ModelsAction;
use App\Models\Equipment;
use App\Models\Status;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewEquipment extends ViewRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        $action = ModelsAction::whereIn('a_id', ['dep', 'sidle', 'smt'])
            ->get()
            ->keyBy('a_id');

        return [
            ActionGroup::make([
                $this->statusAction($action['dep']),
                $this->statusAction($action['sidle']),
                $this->statusAction($action['smt']),
            ])
                ->label('More actions')
                ->button()
                ->color('gray'),
            EditAction::make(),
        ];
    }

    protected function statusAction(ModelsAction $action): Action
    {
        return Action::make("markAs{$action->a_id}")
            ->label($action->a_present_tense)
            ->icon($action->a_icon)
            ->color(match ($action->a_id) {
                'dep' => 'success',
                'sidle' => 'danger',
                'smt' => 'primary',
                default => 'gray',
            })
            ->visible(fn() => $this->record->lifecycle_action_id !== $action->a_id)
            ->modalIcon($action->a_icon)
            ->modalWidth('md')
            ->modalSubmitActionLabel('Submit')
            ->schema([
                Textarea::make('remarks')
                    ->label('Remarks')
                    ->rows(3),
            ])
            ->action(function (array $data) use ($action) {
                DB::transaction(function () use ($action, $data) {
                    $record = Equipment::query()->lockForUpdate()->findOrFail($this->record->getKey());

                    $record->update(['lifecycle_status_id' => $action->a_id]);

                    activity()
                        ->performedOn($record)
                        ->withProperties(['remarks' => $data['remarks'] ?? null])
                        ->log("Marked as {$action->a_present_tense}");
                });

                $this->record->refresh();

                Notification::make()->title("Marked as {$action->a_present_tense}")->success()->send();
            });
    }
}
