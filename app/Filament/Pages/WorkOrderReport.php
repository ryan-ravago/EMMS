<?php

namespace App\Filament\Pages;

use App\Filament\Exports\WorkOrderExporter;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Filament\Support\ReportPage;
use App\Models\Priority;
use App\Models\WorkOrder;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class WorkOrderReport extends ReportPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Work Order Report';

    protected static ?string $title = 'Work Order Report';

    protected static ?string $slug = 'reports/work-orders';

    protected static ?int $navigationSort = 1;

    private const STATUS_IDS = ['pndwor', 'inprog', 'cmp', 'rej', 'cnc'];

    protected function exporter(): string
    {
        return WorkOrderExporter::class;
    }

    protected function exportModel(): string
    {
        return WorkOrder::class;
    }

    protected function reportTable(Table $table): Table
    {
        return $table
            ->query(fn () => $this->scopeToDepartment(
                WorkOrder::query()->with(['status', 'equipment', 'department', 'priority', 'workers']),
                'wo_dep_id',
            ))
            ->columns([
                TextColumn::make('wo_no')
                    ->label('WO No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn (WorkOrder $record): ?string => $record->status?->status_color)
                    ->sortable(),
                TextColumn::make('equipment.eqm_name')
                    ->label('Asset')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->visible(fn (): bool => $this->canSeeAllDepartments())
                    ->sortable(),
                TextColumn::make('priority.prio_name')
                    ->label('Priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('workers')
                    ->label('Technicians')
                    ->badge()
                    ->listWithLineBreaks()
                    ->state(fn (WorkOrder $record): array => $record->workers
                        ->map(fn ($worker): string => trim("{$worker->user_fname} {$worker->user_lname}"))
                        ->all()),
                TextColumn::make('wo_created_dt')
                    ->label('Date Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                TextColumn::make('wo_closed_dt')
                    ->label('Date Closed')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('wo_created_dt', 'desc')
            ->filters([
                SelectFilter::make('wo_dep_id')
                    ->label('Department')
                    ->relationship('department', 'dep_name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => $this->canSeeAllDepartments()),
                SelectFilter::make('wo_status_id')
                    ->label('Status')
                    ->options($this->statusLabels(self::STATUS_IDS)),
                SelectFilter::make('wo_prio_id')
                    ->label('Priority')
                    ->options(fn (): array => Priority::query()->orderBy('prio_id')->pluck('prio_name', 'prio_id')->all()),
                $this->dateRangeFilter('created', 'wo_created_dt', 'Created'),
            ])
            ->recordUrl(fn (WorkOrder $record): ?string => Auth::user()?->can('view', $record)
                ? WorkOrderResource::getUrl('view', ['record' => $record])
                : null);
    }

    public function getSummary(): array
    {
        return [
            'total' => $this->filteredQuery()->count(),
            'groups' => [
                'By status' => $this->countBy('wo_status_id', $this->statusLabels(self::STATUS_IDS)),
                'By priority' => $this->countBy('wo_prio_id', Priority::query()->orderBy('prio_id')->pluck('prio_name', 'prio_id')->all()),
            ],
        ];
    }
}
