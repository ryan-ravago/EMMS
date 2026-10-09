<?php

namespace App\Filament\Support;

use App\Models\Status;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Exporter;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * A filterable table of records with a summary above it. The Preview and Export header
 * actions run on the table's own query, so they follow the filters and the department
 * scope, and they reuse the export permissions of the matching resource policy.
 */
abstract class ReportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected string $view = 'filament.pages.report';

    /**
     * @return class-string<Exporter>
     */
    abstract protected function exporter(): string;

    /**
     * @return class-string<Model>
     */
    abstract protected function exportModel(): string;

    abstract protected function reportTable(Table $table): Table;

    /**
     * @return array{total: int, groups: array<string, array<string, int>>}
     */
    abstract public function getSummary(): array;

    public function table(Table $table): Table
    {
        return $this->reportTable($table)
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            ExportPreviewAction::make()
                ->exporter($this->exporter())
                ->authorize(fn () => Auth::user()->can('previewExport', $this->exportModel())),
            ExportAction::make()
                ->exporter($this->exporter())
                ->color('gray')
                ->authorize(fn () => Auth::user()->can('export', $this->exportModel())),
        ];
    }

    protected function canSeeAllDepartments(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'execom']) ?? false;
    }

    protected function scopeToDepartment(Builder $query, string $column): Builder
    {
        if ($this->canSeeAllDepartments()) {
            return $query;
        }

        return $query->where($column, Auth::user()?->user_dep_id);
    }

    protected function filteredQuery(): Builder
    {
        return $this->getFilteredTableQuery()->reorder();
    }

    /**
     * @param  array<int|string, string>  $labels  column value => label
     * @return array<string, int>
     */
    protected function countBy(string $column, array $labels): array
    {
        $counts = $this->filteredQuery()
            ->toBase()
            ->select($column, DB::raw('count(*) as aggregate'))
            ->groupBy($column)
            ->pluck('aggregate', $column);

        $result = [];

        foreach ($labels as $value => $label) {
            $result[$label] = (int) ($counts[$value] ?? 0);
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $ids
     * @return array<string, string> status id => title, in the order of $ids
     */
    protected function statusLabels(array $ids): array
    {
        $titles = Status::query()->whereIn('status_id', $ids)->pluck('status_title', 'status_id');

        return collect($ids)->mapWithKeys(fn (string $id): array => [$id => $titles[$id] ?? $id])->all();
    }

    protected function dateRangeFilter(string $name, string $column, string $label): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                DatePicker::make('from')->label($label.' from')->native(false),
                DatePicker::make('until')->label($label.' until')->native(false),
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->where($column, '>=', Carbon::parse($date)->startOfDay()))
                ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->where($column, '<=', Carbon::parse($date)->endOfDay())))
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if ($data['from'] ?? null) {
                    $indicators[] = Indicator::make($label.' from '.Carbon::parse($data['from'])->toFormattedDateString())
                        ->removeField('from');
                }

                if ($data['until'] ?? null) {
                    $indicators[] = Indicator::make($label.' until '.Carbon::parse($data['until'])->toFormattedDateString())
                        ->removeField('until');
                }

                return $indicators;
            });
    }
}
