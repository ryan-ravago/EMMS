<?php

namespace App\Filament\Exports;

use App\Support\Activity\ActivityLabels;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Spatie\Activitylog\Models\Activity;

/**
 * Exports the activity log as it is shown on the Activity Log page: readable user, action and record names
 * (not ids), following the page's filters, search and sort. Raw properties are never exported.
 *
 * The page's table is built on the package's Activity model, so that is the type used here (the app's
 * subclass extends it and works too).
 */
class ActivityExporter extends Exporter
{
    protected static ?string $model = Activity::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label('When')
                ->formatStateUsing(fn (mixed $state): ?string => $state ? Carbon::parse($state)->format('Y-m-d H:i:s') : null),
            ExportColumn::make('user')
                ->label('User')
                ->state(fn (Activity $record): string => ActivityLabels::userName($record))
                ->preventFormulaInjection(),
            ExportColumn::make('action')
                ->label('Action')
                ->state(fn (Activity $record): string => ActivityLabels::eventLabel($record->event)),
            ExportColumn::make('record_type')
                ->label('Record type')
                ->state(fn (Activity $record): ?string => $record->subject_type ? ActivityLabels::typeName($record->subject_type) : null),
            ExportColumn::make('record')
                ->label('Record')
                ->state(fn (Activity $record): ?string => ActivityLabels::recordName($record))
                ->preventFormulaInjection(),
            ExportColumn::make('description')
                ->label('What happened')
                ->preventFormulaInjection(),
            ExportColumn::make('via')
                ->label('Triggered by')
                ->state(fn (Activity $record): ?string => $record->getExtraProperty('via'))
                ->preventFormulaInjection(),
            ExportColumn::make('bulk')
                ->label('Bulk action')
                ->state(fn (Activity $record): string => $record->batch_uuid ? 'Yes' : 'No'),
            ExportColumn::make('changes')
                ->label('Changes')
                ->enabledByDefault(false)
                ->state(fn (Activity $record): ?string => ($changes = ActivityLabels::changes($record)) ? ActivityLabels::summarize($changes, 50) : null)
                ->preventFormulaInjection(),
            ExportColumn::make('id')
                ->label('Log ID')
                ->enabledByDefault(false),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with('causer');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your activity log export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
