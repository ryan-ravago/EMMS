<?php

namespace App\Filament\Resources\WorkOrders\Schemas;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\InspectionItems\InspectionItemResource;
use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Models\WorkOrder;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Storage;

class WorkOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Work Order Details')
                    // ->columnStart(1)
                    ->columnSpan(3)
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('wo_no')
                            ->label('WO Number')
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        // TextEntry::make('status.status_title')
                        //     ->label('Status')
                        //     ->size(TextSize::Large)
                        //     // ->badge()
                        //     ->weight('bold')
                        //     // ->color(fn(WorkOrder $record): string => $record->status?->status_color)
                        //     ->icon(fn($record) => $record->status?->status_icon)
                        //     ->iconColor(fn($record) => $record->status?->status_color),
                        TextEntry::make('equipment.eqm_name')
                            ->label('Asset')
                            ->inlineLabel()
                            ->url(fn ($record) => EquipmentResource::getUrl('view', ['record' => $record->wo_eqm_id]))
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        TextEntry::make('priority.prio_name')
                            ->label('Priority')
                            ->badge(),
                        // TextEntry::make('wo_title')
                        //     ->label('Title')
                        //     ->inlineLabel(),
                        TextEntry::make('wo_req_desc')
                            ->label('Requestor - Problem Description')
                            ->visible(fn ($record) => filled($record->wo_req_desc)),
                        TextEntry::make('wo_desc')
                            ->label(fn ($record) => filled($record->wo_req_desc) ? 'Manager - Problem Description' : 'Problem Description')
                            ->placeholder('-'),
                        TextEntry::make('department.dep_name')
                            ->label('Department')
                            ->visible(fn () => auth()->user()->hasRole('super_admin'))
                            ->url(fn ($record) => DepartmentResource::getUrl('view', ['record' => $record->wo_dep_id]))
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        // TextEntry::make('wo_mt_id')
                        //     ->label('Maintenance Task')
                        //     ->inlineLabel()
                        //     ->url(
                        //         fn($record) => $record->wo_mt_id
                        //             ? MaintenanceTaskResource::getUrl('view', ['record' => $record->wo_mt_id])
                        //             : null
                        //     )
                        //     ->placeholder('-')
                        //     ->icon('heroicon-o-arrow-top-right-on-square')
                        //     ->iconPosition(IconPosition::After),
                        // TextEntry::make('inspectionItem.insi_no')
                        //     ->label('Inspection Item')
                        //     ->url(fn($record) => $record->wo_insi_id
                        //         ? InspectionItemResource::getUrl('view', ['record' => $record->wo_insi_id])
                        //         : null)
                        //     ->placeholder('-')
                        //     ->icon('heroicon-o-arrow-top-right-on-square')
                        //     ->iconPosition(IconPosition::After),
                        TextEntry::make('workers')
                            ->label('Assigned Tech.')
                            ->listWithLineBreaks()
                            ->badge()
                            ->icon('heroicon-o-user-circle')
                            ->state(fn ($record) => $record->workers->map(fn ($w) => "{$w->user_fname} {$w->user_lname}")->toArray()),
                        TextEntry::make('createdBy.full_name')
                            ->label('Submitted By')
                            ->numeric(),
                        TextEntry::make('wo_created_dt')
                            ->label('Date Submitted')
                            ->dateTime('M d, Y | h:i A'),
                        TextEntry::make('wo_closed_dt')
                            ->label('Closed At')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('-'),
                        // ImageEntry::make('wo_attachments')
                        //     ->label('Attachments')
                        //     ->placeholder('-')
                        //     ->columnSpanFull()
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Work Order Status')
                            ->schema([
                                TextEntry::make('status.status_title')
                                    ->label('Status')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color(fn ($record) => $record->status?->status_color)
                                    ->icon(fn ($record) => $record->status?->status_icon)
                                    ->iconColor(fn ($record) => $record->status?->status_color),
                            ]),

                        Section::make('Attachments')
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
                    ]),
                Section::make('Post Mortem')
                    // ->columnStart(1)
                    ->columnSpan(3)
                    ->inlineLabel()
                    ->visible(fn ($record) => filled($record->wo_root_cause))
                    ->schema([
                        TextEntry::make('wo_root_cause')
                            ->label('Root Cause'),
                        TextEntry::make('wo_corrective_action')
                            ->label('Corrective Action'),
                    ]),
            ])
            ->columns(4);
    }
}
