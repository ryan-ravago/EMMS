<?php

namespace App\Filament\Resources\RequestorWorkOrders\Schemas;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\InspectionItems\InspectionItemResource;
use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Models\Status;
use App\Models\WorkOrder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Storage;

class RequestorWorkOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Work Order Details')
                    ->columnSpan(2)
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('wo_no')
                            ->label('WO Number')
                            ->weight('bold'),
                        TextEntry::make('equipment.eqm_name')
                            ->label('Equipment')
                            ->inlineLabel()
                            ->url(fn ($record) => EquipmentResource::getUrl('view', ['record' => $record->wo_eqm_id]))
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        TextEntry::make('status.status_title')
                            ->label('Status')
                            ->badge()
                            // ->state(function (WorkOrder $record): string {
                            //     if ($record->wo_status_id === 'inprog') {
                            //         return Status::find('appr')->status_title ?? 'Approved';
                            //     }

                            //     return $record->status->status_title ?? '-';
                            // })
                            // ->color(fn(WorkOrder $record): string => $record->wo_status_id === 'inprog'
                            //     ? (Status::find('appr')->status_color ?? 'success')
                            //     : ($record->status?->status_color ?? 'gray'))
                            ->color(fn (WorkOrder $record) => $record->status->status_color)
                            ->icon(fn (WorkOrder $record) => $record->status->status_icon),
                        TextEntry::make('priority.prio_name')
                            ->label('Priority')
                            ->badge(),
                        TextEntry::make('wo_title')
                            ->label('Title')
                            ->inlineLabel(),
                        TextEntry::make('wo_req_desc')
                            ->label('Requestor Description'),
                        TextEntry::make('wo_desc')
                            ->label('Manager Description')
                            ->placeholder('-'),
                        TextEntry::make('department.dep_name')
                            ->label('Department')
                            ->url(fn ($record) => DepartmentResource::getUrl('view', ['record' => $record->wo_dep_id]))
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        TextEntry::make('wo_mt_id')
                            ->label('Maintenance Task')
                            ->inlineLabel()
                            ->url(
                                fn ($record) => $record->wo_mt_id
                                    ? MaintenanceTaskResource::getUrl('view', ['record' => $record->wo_mt_id])
                                    : null
                            )
                            ->placeholder('-')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        TextEntry::make('inspectionItem.insi_no')
                            ->label('Inspection Item')
                            ->url(fn ($record) => $record->wo_insi_id
                                ? InspectionItemResource::getUrl('view', ['record' => $record->wo_insi_id])
                                : null)
                            ->placeholder('-')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        TextEntry::make('workers')
                            ->label('Technicians')
                            ->listWithLineBreaks()
                            ->badge()
                            ->placeholder('-')
                            ->icon('heroicon-o-user-circle')
                            ->state(fn ($record) => $record->workers->map(fn ($w) => "{$w->user_fname} {$w->user_lname}")->toArray()),
                        TextEntry::make('createdBy.full_name')
                            ->label('Created By')
                            ->numeric(),
                        TextEntry::make('wo_created_dt')
                            ->label('Created At')
                            ->dateTime('M d, Y | h:i A'),
                        TextEntry::make('wo_closed_dt')
                            ->label('Closed At')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('-'),
                    ]),

                Section::make('Attachments')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('wo_attachments')
                            ->label('Files')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->html()
                            ->state(function ($record) {
                                if (empty($record->wo_attachments)) {
                                    return null;
                                }

                                return collect($record->wo_attachments)
                                    ->map(function ($file) {
                                        $url = Storage::disk('local')->temporaryUrl($file, now()->addMinutes(30));

                                        return "
                                            <div class='flex items-center justify-between gap-3 px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 mb-2'>
                                                <span class='text-xs text-gray-700 dark:text-gray-300 truncate'>".basename($file)."</span>
                                                <div class='flex gap-1'>
                                                    <a href='{$url}' target='_blank' title='Preview'
                                                        class='text-xs p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition'>
                                                        View
                                                    </a>
                                                    <a href='{$url}' download='".basename($file)."' title='Download'
                                                        class='text-xs p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition'>
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        ";
                                    })
                                    ->implode('');
                            }),
                    ]),

            ])
            ->columns(3);
    }
}
