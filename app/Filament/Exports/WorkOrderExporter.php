<?php

namespace App\Filament\Exports;

use App\Models\WorkOrder;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class WorkOrderExporter extends Exporter
{
    protected static ?string $model = WorkOrder::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('wo_no')
                ->label('WO No.')
                ->preventFormulaInjection(),
            ExportColumn::make('status.status_title')
                ->label('Status'),
            ExportColumn::make('equipment.eqm_prc_code')
                ->label('Asset Code')
                ->preventFormulaInjection(),
            ExportColumn::make('equipment.eqm_name')
                ->label('Asset')
                ->preventFormulaInjection(),
            ExportColumn::make('department.dep_name')
                ->label('Department'),
            ExportColumn::make('priority.prio_name')
                ->label('Priority'),
            ExportColumn::make('workers')
                ->label('Assigned Technicians')
                ->state(fn (WorkOrder $record): string => $record->workers
                    ->map(fn ($worker): string => trim("{$worker->user_fname} {$worker->user_lname}"))
                    ->implode('; ')),
            ExportColumn::make('wo_created_dt')
                ->label('Date Created')
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d H:i') : null),
            ExportColumn::make('wo_closed_dt')
                ->label('Date Closed')
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d H:i') : null),
            ExportColumn::make('createdBy')
                ->label('Created By')
                ->state(fn (WorkOrder $record): ?string => $record->createdBy?->full_name)
                ->preventFormulaInjection()
                ->enabledByDefault(false),
            ExportColumn::make('wo_req_desc')
                ->label('Requestor Problem Description')
                ->preventFormulaInjection()
                ->enabledByDefault(false),
            ExportColumn::make('wo_desc')
                ->label('Manager Problem Description')
                ->preventFormulaInjection()
                ->enabledByDefault(false),
            ExportColumn::make('wo_root_cause')
                ->label('Root Cause')
                ->preventFormulaInjection()
                ->enabledByDefault(false),
            ExportColumn::make('wo_corrective_action')
                ->label('Corrective Action')
                ->preventFormulaInjection()
                ->enabledByDefault(false),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(['status', 'equipment', 'department', 'priority', 'workers', 'createdBy']);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your work order export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
