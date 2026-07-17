<?php

namespace App\Filament\Resources\Equipment\Resources\WorkOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = auth()->user();

                if ($user === null) {
                    return $query->whereRaw('1 = 0');
                }

                if ($user->hasRole('super_admin')) {
                    return $query;
                }

                return $query->where('wo_dep_id', $user->user_dep_id);
            })
            ->columns([
                TextColumn::make('wo_no')
                    ->label('WO No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record) => $record->status->status_color)
                    ->icon(fn($record) => $record->status->status_icon)
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
                    ->listWithLineBreaks()
                    ->icon('heroicon-s-user-circle')
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
                SelectFilter::make('wo_prio_id')
                    ->label('Priority')
                    ->relationship('priority', 'prio_name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('wo_status_id')
                    ->label('Status')
                    ->relationship('status', 'status_title')
                    ->searchable()
                    ->preload(),
                Filter::make('wo_created_dt')
                    ->label('Date Submitted')
                    ->schema([
                        DatePicker::make('from')->label('From')->native(false),
                        DatePicker::make('until')->label('Until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('wo_created_dt', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('wo_created_dt', '<=', $data['until']));
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
            ]);
    }
}
