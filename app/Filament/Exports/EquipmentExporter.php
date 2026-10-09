<?php

namespace App\Filament\Exports;

use App\Models\Equipment;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class EquipmentExporter extends Exporter
{
    protected static ?string $model = Equipment::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('eqm_prc_code')
                ->label('Asset Code')
                ->preventFormulaInjection(),
            ExportColumn::make('eqm_name')
                ->label('Name')
                ->preventFormulaInjection(),
            ExportColumn::make('assetType.name')
                ->label('Asset Type'),
            ExportColumn::make('parent.eqm_name')
                ->label('Allocated To')
                ->preventFormulaInjection(),
            ExportColumn::make('eqm_is_active')
                ->label('Status')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),
            ExportColumn::make('lifecycleStatus.status_title')
                ->label('Lifecycle Status'),
            ExportColumn::make('location')
                ->label('Location')
                ->state(fn (Equipment $record): ?string => $record->effectiveLocation()?->full_path)
                ->preventFormulaInjection(),
            ExportColumn::make('type.eqmt_name')
                ->label('Equipment Type')
                ->enabledByDefault(false),
            ExportColumn::make('brand.eqmb_name')
                ->label('Brand')
                ->enabledByDefault(false),
            ExportColumn::make('equipmentModel.eqmm_name')
                ->label('Model')
                ->enabledByDefault(false),
            ExportColumn::make('year_model')
                ->label('Year Model')
                ->enabledByDefault(false),
            ExportColumn::make('eqm_serial_num')
                ->label('Serial No.')
                ->enabledByDefault(false)
                ->preventFormulaInjection(),
            ExportColumn::make('eqm_plate_num')
                ->label('Plate No.')
                ->enabledByDefault(false)
                ->preventFormulaInjection(),
            ExportColumn::make('eqm_vin')
                ->label('VIN')
                ->enabledByDefault(false)
                ->preventFormulaInjection(),
            ExportColumn::make('eqm_chassis_no')
                ->label('Chassis No.')
                ->enabledByDefault(false)
                ->preventFormulaInjection(),
            ExportColumn::make('eqm_engine')
                ->label('Engine')
                ->enabledByDefault(false)
                ->preventFormulaInjection(),
            ExportColumn::make('eqm_date_purchased')
                ->label('Date Purchased')
                ->enabledByDefault(false)
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d') : null),
            ExportColumn::make('eqm_next_pm_due_at')
                ->label('Next PM Due')
                ->enabledByDefault(false)
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d') : null),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(['assetType', 'parent.location', 'location.parent', 'lifecycleStatus', 'type', 'brand', 'equipmentModel']);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your asset export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
