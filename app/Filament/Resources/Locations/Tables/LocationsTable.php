<?php

namespace App\Filament\Resources\Locations\Tables;

use Alareqi\FilamentTree\Columns\TreeColumn;
use App\Models\Location;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // The tree needs its indentation and expand buttons, so keep the normal table on phones.
            ->stackedOnMobile(false)
            ->columns([
                TreeColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                // TreeColumn::make('full_path')
                //     ->label('Full Path'),
            ])
            ->tree(
                parentColumn: 'parent_id',
                treeColumn: 'name',
            )
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
                        ->modalDescription('Only locations that are not used anywhere can be deleted.')
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            $inUse = $records->filter(fn (Location $location): bool => $location->isReferenced());

                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->title('Cannot delete locations that are in use')
                                    ->body($inUse->map(fn (Location $location): string => $location->deletionBlockedReason())->implode("\n"))
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}
