<?php

namespace App\Filament\Resources\RfidRawLogs\Tables;

use App\Models\Location;
use App\Models\RfidRawLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RfidRawLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->paginated([25, 50, 100])
            ->columns([
                TextColumn::make('received_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('tag_id')
                    ->label('Tag')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => $state === 'IN' ? 'success' : 'warning'),
                TextColumn::make('detected_at')
                    ->label('Detected')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('yard.name')
                    ->label('Yard')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('outcome')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => RfidRawLog::outcomeLabel($state))
                    ->color(fn(string $state): string => RfidRawLog::outcomeColor($state))
                    ->sortable(),
                TextColumn::make('ip')
                    ->label('IP address')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('batch_uuid')
                    ->label('Batch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('processed_at')
                    ->label('Processed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('outcome')
                    ->options(RfidRawLog::outcomeLabels()),
                SelectFilter::make('yard_id')
                    ->label('Yard')
                    ->options(fn(): array => Location::query()->orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('status')
                    ->options(['IN' => 'IN', 'OUT' => 'OUT']),
                Filter::make('received')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn(Builder $query, string $date): Builder => $query->where('received_at', '>=', $date . ' 00:00:00'))
                        ->when($data['until'] ?? null, fn(Builder $query, string $date): Builder => $query->where('received_at', '<=', $date . ' 23:59:59'))),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
