<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\WorkOrders\RelationManagers\LogUpdatesRelationManager;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\WorkOrder;
use App\Models\WorkOrderLogUpdate;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;

class ViewWorkOrder extends ViewRecord
{
    protected static string $resource = WorkOrderResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true; // merges infolist + relation manager tabs together
    }

    public function getContentTabLabel(): string
    {
        return 'Details'; // label for the main infolist tab
    }

    public function getRelationManagers(): array
    {
        return [
            LogUpdatesRelationManager::class,
            LogsRelationManager::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('addUpdate')
                ->label('Add Update')
                ->visible(fn() => Auth::user()->can('AddUpdate:WorkOrderResource'))
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('info')
                ->modalHeading('Add Work Order Update')
                ->modalWidth(Width::TwoExtraLarge)
                ->closeModalByClickingAway(false)
                ->modalCloseButton(false)
                ->schema([
                    Textarea::make('wolu_update_note')
                        ->label('Update Note')
                        ->required()
                        ->rows(4),
                    FileUpload::make('wolu_attachments')
                        ->label('Attachments')
                        ->multiple()
                        ->nullable()
                        ->maxFiles(10)
                        ->maxSize(10240)
                        ->acceptedFileTypes([
                            'image/*',
                            'application/pdf',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv',
                        ])
                        ->imageEditor(),
                ])
                ->action(function (array $data, WorkOrder $record) {
                    WorkOrderLogUpdate::create([
                        'wolu_wo_id'      => $record->wo_id,
                        'wolu_update_note' => $data['wolu_update_note'],
                        'wolu_attachments' => $data['wolu_attachments'] ?? null,
                        'wolu_by'         => auth()->id(),
                        'wolu_dt'         => now(),
                    ]);

                    Notification::make()
                        ->title('Update added successfully.')
                        ->success()
                        ->send();

                    $this->js('Livewire.dispatch("refreshRelationManager")');
                }),

        ];
    }
}
