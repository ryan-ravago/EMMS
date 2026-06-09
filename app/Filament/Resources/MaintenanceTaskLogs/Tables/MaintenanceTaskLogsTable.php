<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Tables;

use App\Filament\Resources\MaintenanceTaskLogs\MaintenanceTaskLogResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\MaintenanceTaskLog;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTaskLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status.status_title') // adjust to your status relationship/field
                    ->label('Status')
                    ->badge()
                    ->color(fn (MaintenanceTaskLog $record): string => $record->status->status_color)
                    ->icon(fn (MaintenanceTaskLog $record): string => $record->status->status_icon)
                    ->searchable(),
                TextColumn::make('action.a_past_tense')
                    ->label('Last Action Made')
                    ->searchable()
                    ->placeholder('—')
                    ->wrap(),
                TextColumn::make('mtl_remarks')
                    ->label('Remarks')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('mtl_due_dt')
                    ->label('Due Date')
                    ->dateTime('M d, Y | h:iA')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('workOrder.wo_no')
                    ->label('WO #')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->color('info')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->url(
                        fn ($record) => $record->workOrder
                            ? WorkOrderResource::getUrl('view', ['record' => $record->workOrder->wo_id])
                            : null
                    ),
                TextColumn::make('logBy.full_name')
                    ->label('Logged By')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mtl_dt')
                    ->label('Timestamp')
                    ->dateTime('M d, Y | h:iA')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            // ->recordUrl(fn(Model $record): string => MaintenanceTaskLogResource::getUrl('view', ['record' => $record]))
            ->defaultSort('mtl_dt', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->extraAttributes(['style' => 'display:none']), // hides the button but keeps it functional,
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
