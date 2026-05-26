<?php

namespace App\Filament\Resources\TechnicianWorkOrders\Tables;

use App\Models\WorkOrder;
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

class TechnicianWorkOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wo_no')
                    ->label('WO No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipment.eqm_name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->sortable()
                    ->visible(fn() => auth()->user()->hasRole('super_admin')),
                TextColumn::make('wo_title')
                    ->label('Title')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('priority.prio_name')
                    ->label('Priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn(WorkOrder $record) => $record->status->status_color)
                    ->icon(fn(WorkOrder $record) => $record->status->status_icon)
                    ->sortable(),
                TextColumn::make('workers')
                    ->badge()
                    ->listWithLineBreaks()
                    ->icon('heroicon-o-user-circle')
                    ->state(fn($record) => $record->workers->map(fn($w) => "{$w->user_fname} {$w->user_lname}")->toArray()),
                TextColumn::make('wo_created_dt')
                    ->label('Created At')
                    ->dateTime('M d, Y | h:i A')
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
                    ->label('Created At')
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
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('wo_created_dt', 'desc');
    }
}
