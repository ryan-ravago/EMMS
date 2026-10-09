<?php

namespace App\Filament\Exports;

use App\Models\MaintenanceTask;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class MaintenanceTaskExporter extends Exporter
{
    protected static ?string $model = MaintenanceTask::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('status.status_title')
                ->label('Status'),
            ExportColumn::make('mt_eqm_log')
                ->label('Equipment')
                ->preventFormulaInjection(),
            ExportColumn::make('department.dep_name')
                ->label('Department'),
            ExportColumn::make('mt_task_log')
                ->label('Task')
                ->preventFormulaInjection(),
            ExportColumn::make('mt_due_dt')
                ->label('Due Date')
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d H:i') : null),
            ExportColumn::make('mt_closed_dt')
                ->label('Closed')
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d H:i') : null),
            ExportColumn::make('mt_scheduled_dt')
                ->label('Scheduled')
                ->enabledByDefault(false)
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d H:i') : null),
            ExportColumn::make('mt_dt')
                ->label('Created')
                ->enabledByDefault(false)
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d H:i') : null),
            ExportColumn::make('mt_remarks')
                ->label('Remarks')
                ->enabledByDefault(false)
                ->preventFormulaInjection(),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(['status', 'department']);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your maintenance task export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
