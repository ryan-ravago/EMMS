<?php

namespace App\Filament\Pages;

use App\Filament\Exports\MaintenanceTaskExporter;
use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Filament\Support\ReportPage;
use App\Models\MaintenanceTask;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MaintenanceReport extends ReportPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Maintenance Report';

    protected static ?string $title = 'Maintenance Report';

    protected static ?string $slug = 'reports/maintenance';

    protected static ?int $navigationSort = 2;

    private const STATUS_IDS = ['pnd', 'inprog', 'snz', 'cmp'];

    protected function exporter(): string
    {
        return MaintenanceTaskExporter::class;
    }

    protected function exportModel(): string
    {
        return MaintenanceTask::class;
    }

    protected function reportTable(Table $table): Table
    {
        return $table
            ->query(fn () => $this->scopeToDepartment(
                MaintenanceTask::query()->with(['status', 'department']),
                'mt_dep_id',
            ))
            ->columns([
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn (MaintenanceTask $record): ?string => $record->status?->status_color)
                    ->sortable(),
                TextColumn::make('mt_eqm_log')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->visible(fn (): bool => $this->canSeeAllDepartments())
                    ->sortable(),
                TextColumn::make('mt_task_log')
                    ->label('Task')
                    ->searchable(),
                TextColumn::make('mt_due_dt')
                    ->label('Due Date')
                    ->dateTime('M j, Y h:i A')
                    ->color(fn (MaintenanceTask $record): ?string => $record->mt_closed_dt === null && $record->mt_due_dt < now() ? 'danger' : null)
                    ->sortable(),
                TextColumn::make('mt_closed_dt')
                    ->label('Closed')
                    ->dateTime('M j, Y h:i A')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('mt_due_dt', 'desc')
            ->filters([
                SelectFilter::make('mt_dep_id')
                    ->label('Department')
                    ->relationship('department', 'dep_name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => $this->canSeeAllDepartments()),
                SelectFilter::make('mt_status_id')
                    ->label('Status')
                    ->options($this->statusLabels(self::STATUS_IDS)),
                Filter::make('overdue')
                    ->label('Overdue only')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNull('mt_closed_dt')->where('mt_due_dt', '<', now())),
                $this->dateRangeFilter('due', 'mt_due_dt', 'Due'),
            ])
            ->recordUrl(fn (MaintenanceTask $record): ?string => Auth::user()?->can('view', $record)
                ? MaintenanceTaskResource::getUrl('view', ['record' => $record])
                : null);
    }

    public function getSummary(): array
    {
        $open = fn (): Builder => $this->filteredQuery()->whereNull('mt_closed_dt');

        return [
            'total' => $this->filteredQuery()->count(),
            'groups' => [
                'By status' => $this->countBy('mt_status_id', $this->statusLabels(self::STATUS_IDS)),
                'Timeliness' => [
                    'Overdue' => $open()->where('mt_due_dt', '<', now())->count(),
                    'Open, not yet due' => $open()->where('mt_due_dt', '>=', now())->count(),
                    'Closed' => $this->filteredQuery()->whereNotNull('mt_closed_dt')->count(),
                ],
            ],
        ];
    }
}
