<?php

namespace App\Filament\Resources\MaintenanceTasks\Tables;

use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Models\MaintenanceTask;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn(MaintenanceTask $record): string => $record->status->status_color)
                    ->icon(fn(MaintenanceTask $record): string => $record->status->status_icon)
                    ->sortable(),

                TextColumn::make('mt_eqm_log')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->visible(fn() => auth()->user()->hasRole('super_admin'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mt_task_log')
                    ->label('Task')
                    ->searchable(),

                TextColumn::make('mt_due_dt')
                    ->label('Due Date')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->color(
                        fn($record) =>
                        $record->mt_closed_dt === null && $record->mt_due_dt < now()
                            ? 'danger'
                            : null
                    ),

                TextColumn::make('mt_closed_dt')
                    ->label('Closed')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('mt_dt')
                    ->label('Created')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordUrl(fn(Model $record): string => MaintenanceTaskResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
