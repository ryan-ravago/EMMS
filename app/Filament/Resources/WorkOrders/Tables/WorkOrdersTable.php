<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\WorkOrder;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('wo_created_dt', 'desc')
            ->columns([
                TextColumn::make('wo_no')
                    ->label('WO No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn(WorkOrder $record) => $record->status->status_color)
                    ->icon(fn(WorkOrder $record) => $record->status->status_icon)
                    ->sortable(),
                TextColumn::make('equipment.eqm_name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->sortable()
                    ->visible(fn() => auth()->user()->hasRole('super_admin')),
                // TextColumn::make('wo_title')
                //     ->label('Title')
                //     ->searchable()
                //     ->wrap(),
                TextColumn::make('priority.prio_name')
                    ->label('Priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('workers')
                    ->badge()
                    ->searchable(
                        query: fn(Builder $query, string $search): Builder => $query->orWhereHas(
                            'workers',
                            fn(Builder $q) =>
                            $q->where('user_fname', 'like', "%{$search}%")
                                ->orWhere('user_lname', 'like', "%{$search}%")
                        )
                    )
                    ->listWithLineBreaks()
                    ->icon('heroicon-o-user-circle')
                    ->state(fn($record) => $record->workers->map(fn($w) => "{$w->user_fname} {$w->user_lname}")->toArray()),
                TextColumn::make('wo_closed_dt')
                    ->label('Closed At')
                    ->dateTime('M d, Y h:i A')
                    ->color('gray')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('wo_created_dt')
                    ->label('Date Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('wo_dep_id')
                    ->label('Department')
                    ->relationship('department', 'dep_name')
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()->hasRole('super_admin')),
                SelectFilter::make('eqm_id')
                    ->label('Equipment')
                    ->relationship('equipment', 'eqm_name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('wo_prio_id')
                    ->label('Priority')
                    ->relationship(
                        'priority',
                        'prio_name',
                        fn($query) => $query->orderByRaw("FIELD(prio_id, 1, 2, 3, 4)")
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('wo_status_id')
                    ->label('Status')
                    ->relationship(
                        'status',
                        'status_title',
                        fn($query) => $query
                            ->whereIn('status_id', ['pndwor', 'inprog', 'rej', 'cnc', 'cmp'])
                            ->orderByRaw("FIELD(status_id, 'pndwor', 'inprog', 'cmp', 'rej', 'cnc')")
                    )
                    ->searchable()
                    ->preload(),
                Filter::make('wo_desc')
                    ->label('Manager Problem Description')
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['wo_desc'] ?? null) {
                            $indicators[] = "Manager Problem Description: " . $data['wo_desc'];
                        }

                        return $indicators;
                    })
                    ->schema([
                        TextInput::make('wo_desc')
                            ->placeholder('Search...')
                            ->label('Manager Problem Description')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['wo_desc'],
                                fn(Builder $query, $managerProbDesc): Builder => $query->where('wo_desc', 'like', "%{$managerProbDesc}%")
                            );
                    }),
                Filter::make('wo_created_dt')
                    ->label('Date Submitted')
                    ->schema([
                        DatePicker::make('from')
                            ->label('From')
                            ->native(false)
                            ->nullable(),
                        DatePicker::make('until')
                            ->label('Until')
                            ->native(false)
                            ->nullable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $date): Builder => $q->whereDate('wo_created_dt', '>=', $date))
                            ->when($data['until'] ?? null, fn($q, $date): Builder => $q->whereDate('wo_created_dt', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('From ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Until ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(function (Collection $records) {
                            DB::transaction(function () use ($records) {
                                try {
                                    $ids = $records->pluck('wo_id');

                                    DB::table('work_order_log_updates')->whereIn('wolu_wo_id', $ids)->delete();
                                    DB::table('work_order_logs')->whereIn('wol_wo_id', $ids)->delete();

                                    $records->each->delete();
                                } catch (\Throwable $e) {
                                    Log::error('Failed to bulk delete work orders: ' . $e->getMessage(), [
                                        'wo_ids' => $ids ?? [],
                                    ]);

                                    throw $e;
                                }
                            });
                        })
                        ->successNotificationTitle('Work orders deleted successfully.')
                        ->failureNotificationTitle('Failed to delete work orders.'),
                ]),
            ])
            ->defaultSort('wo_created_dt', 'desc');
    }
}
