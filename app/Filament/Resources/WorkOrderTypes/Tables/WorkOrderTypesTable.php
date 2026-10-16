<?php

namespace App\Filament\Resources\WorkOrderTypes\Tables;

use App\Models\WorkOrderType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WorkOrderTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('workOrders'))
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('work_orders_count')
                    ->label('Work Orders')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('type')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription('Only types that are not used by any work order can be deleted.')
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            $inUse = $records->filter(fn (WorkOrderType $type): bool => $type->work_orders_count > 0);

                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->title('Cannot delete types that are used by work orders')
                                    ->body($inUse->pluck('type')->implode(', '))
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}
