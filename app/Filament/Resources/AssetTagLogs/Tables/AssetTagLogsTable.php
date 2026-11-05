<?php

namespace App\Filament\Resources\AssetTagLogs\Tables;

use App\Filament\Resources\AssetTagLogs\AssetTagLogLinks;
use App\Models\AssetTagLog;
use App\Models\Location;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The scans table grows with every read, so the list is built for a large table: it opens without loading rows
 * until the page is ready, searches by the start of a tag (an index can be used) and filters on indexed columns
 * (detected_at; yard and status through the unique key). It has full paging (next, last page) with 10 rows by default.
 */
class AssetTagLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('detected_at', 'desc')
            ->paginationMode(PaginationMode::Default)
            ->extremePaginationLinks()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->deferLoading()
            ->searchPlaceholder('Search a tag (start of it) or an asset code / name')
            ->columns([
                TextColumn::make('detected_at')
                    ->label('Detected')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable(),
                TextColumn::make('tag_id')
                    ->label('RFID tag')
                    ->url(fn (AssetTagLog $record): ?string => AssetTagLogLinks::tag($record))
                    ->color(fn (AssetTagLog $record): ?string => AssetTagLogLinks::tag($record) ? 'primary' : null)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(fn (Builder $inner) => $inner
                        ->where('tag_id', 'like', "{$search}%")
                        ->orWhereHas('tag.asset', fn (Builder $asset) => $asset
                            ->where('eqm_prc_code', 'like', "%{$search}%")
                            ->orWhere('eqm_name', 'like', "%{$search}%")))),
                TextColumn::make('tag.asset.eqm_prc_code')
                    ->label('Asset code')
                    ->placeholder('-')
                    ->url(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record))
                    ->color(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record) ? 'primary' : null),
                TextColumn::make('tag.asset.eqm_name')
                    ->label('Asset')
                    ->placeholder('-')
                    ->limit(40)
                    ->url(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record))
                    ->color(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record) ? 'primary' : null),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => $state === 'IN' ? 'success' : 'warning'),
                TextColumn::make('yard.name')
                    ->label('Yard')
                    ->placeholder('-'),
                TextColumn::make('received_at')
                    ->label('Received')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['IN' => 'IN', 'OUT' => 'OUT']),
                SelectFilter::make('yard_id')
                    ->label('Yard')
                    ->options(fn (): array => Location::query()->orderBy('name')->pluck('name', 'id')->all()),
                Filter::make('detected')
                    ->label('Detected')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->where('detected_at', '>=', $date.' 00:00:00'))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->where('detected_at', '<=', $date.' 23:59:59'))),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
