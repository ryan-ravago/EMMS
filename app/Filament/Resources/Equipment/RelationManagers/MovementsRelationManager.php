<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\AssetMovements\AssetMovementResource;
use App\Models\AssetMovement;
use App\Models\Equipment;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The equipment's trips (time out / time in) as built from its tag scans. Read-only: acknowledging, filling in a
 * missing time or disregarding a trip is done on the trip itself (Time In / Out).
 */
class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Time In / Out';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->movements()->needingAttention()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getBadgeColor(Model $ownerRecord, string $pageClass): ?string
    {
        return 'warning';
    }

    /**
     * Only equipment is tracked (accessories are not), and only for people who may see the trips.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Equipment
            && $ownerRecord->isEquipmentAsset()
            && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->heading('Equipment Trips')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('acknowledgedBy'))
            ->defaultSort(fn (Builder $query): Builder => $query->orderByRaw('COALESCE(time_out, time_in) DESC'))
            ->columns([
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
            ->filters([
                SelectFilter::make('status')
                    ->options(AssetMovement::statusLabels())
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        AssetMovement::STATUS_PENDING_ACKNOWLEDGEMENT => $query->pendingAcknowledgement(),
                        AssetMovement::STATUS_MISSING_TIME_OUT => $query->missingTimeOut(),
                        AssetMovement::STATUS_MISSING_TIME_IN => $query->missingTimeIn(),
                        AssetMovement::STATUS_ACKNOWLEDGED => $query->acknowledged(),
                        AssetMovement::STATUS_DISREGARDED => $query->disregarded(),
                        default => $query,
                    }),
            ])
            ->recordUrl(fn (AssetMovement $record): string => AssetMovementResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (AssetMovement $record): string => AssetMovementResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
