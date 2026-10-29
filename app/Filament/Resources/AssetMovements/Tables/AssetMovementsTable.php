<?php

namespace App\Filament\Resources\AssetMovements\Tables;

use App\Filament\Resources\AssetMovements\Actions\VerificationActions;
use App\Filament\Resources\AssetMovements\AssetMovementResource;
use App\Models\AssetMovement;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->extremePaginationLinks()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->defaultSort(fn (Builder $query): Builder => $query->orderByRaw('COALESCE(time_out, time_in) DESC')->orderByDesc('id'))
            ->columns([
                TextColumn::make('asset.eqm_prc_code')
                    ->label('Equipment')
                    ->description(fn (AssetMovement $record): ?string => $record->asset?->eqm_name)
                    ->searchable(['eqm_prc_code', 'eqm_name']),
                TextColumn::make('tag_id')
                    ->label('Tag')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('time_out')
                    ->label('Time Out')
                    ->dateTime('M d, Y h:i A')
                    ->description(fn (AssetMovement $record): ?string => $record->isManualOut() ? 'Entered manually' : null)
                    ->placeholder('Missing')
                    ->color(fn (AssetMovement $record): ?string => $record->time_out === null ? 'danger' : null)
                    ->sortable(),
                TextColumn::make('time_in')
                    ->label('Time In')
                    ->dateTime('M d, Y h:i A')
                    ->description(fn (AssetMovement $record): ?string => $record->isManualIn() ? 'Entered manually' : null)
                    ->placeholder('Missing')
                    ->color(fn (AssetMovement $record): ?string => $record->time_in === null ? 'danger' : null)
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Away For')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (AssetMovement $record): string => $record->status)
                    ->formatStateUsing(fn (string $state): string => AssetMovement::statusLabels()[$state])
                    ->color(fn (string $state): string => match ($state) {
                        AssetMovement::STATUS_ACKNOWLEDGED => 'success',
                        AssetMovement::STATUS_PENDING_ACKNOWLEDGEMENT => 'info',
                        AssetMovement::STATUS_DISREGARDED => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('acknowledgedBy.full_name')
                    ->label('Verified By')
                    ->description(fn (AssetMovement $record): ?string => $record->acknowledged_at?->format('M d, Y h:i A'))
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->recordUrl(fn (AssetMovement $record): string => AssetMovementResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make(VerificationActions::all())
                    ->label('Verify')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->button()
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    VerificationActions::bulkAcknowledge(),
                    VerificationActions::bulkDisregard(),
                ]),
            ]);
    }
}
