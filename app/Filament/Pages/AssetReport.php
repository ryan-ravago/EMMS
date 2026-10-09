<?php

namespace App\Filament\Pages;

use App\Filament\Exports\EquipmentExporter;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Support\ReportPage;
use App\Models\AssetType;
use App\Models\Equipment;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AssetReport extends ReportPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Asset Report';

    protected static ?string $title = 'Asset Report';

    protected static ?string $slug = 'reports/assets';

    protected static ?int $navigationSort = 3;

    protected function exporter(): string
    {
        return EquipmentExporter::class;
    }

    protected function exportModel(): string
    {
        return Equipment::class;
    }

    protected function reportTable(Table $table): Table
    {
        return $table
            ->query(fn () => Equipment::query()->with(['assetType', 'parent.location', 'location.parent', 'lifecycleStatus']))
            ->columns([
                TextColumn::make('eqm_prc_code')
                    ->label('Asset Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assetType.name')
                    ->label('Asset Type')
                    ->badge()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('eqm_is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('lifecycleStatus.status_title')
                    ->label('Lifecycle Status')
                    ->badge()
                    ->color(fn (Equipment $record): ?string => $record->lifecycleStatus?->status_color)
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Location')
                    ->state(fn (Equipment $record): ?string => $record->effectiveLocation()?->full_path)
                    ->placeholder('—'),
            ])
            ->defaultSort('eqm_name')
            ->filters([
                SelectFilter::make('asset_type_id')
                    ->label('Asset Type')
                    ->relationship('assetType', 'name'),
                SelectFilter::make('eqm_is_active')
                    ->label('Status')
                    ->options([1 => 'Active', 0 => 'Inactive']),
                SelectFilter::make('lifecycle_status_id')
                    ->label('Lifecycle Status')
                    ->relationship('lifecycleStatus', 'status_title'),
            ])
            ->recordUrl(fn (Equipment $record): ?string => Auth::user()?->can('view', $record)
                ? EquipmentResource::getUrl('view', ['record' => $record])
                : null);
    }

    public function getSummary(): array
    {
        $lifecycleIds = $this->filteredQuery()
            ->toBase()
            ->whereNotNull('lifecycle_status_id')
            ->distinct()
            ->pluck('lifecycle_status_id')
            ->all();

        return [
            'total' => $this->filteredQuery()->count(),
            'groups' => [
                'By status' => $this->countBy('eqm_is_active', [1 => 'Active', 0 => 'Inactive']),
                'By lifecycle' => $this->countBy('lifecycle_status_id', $this->statusLabels($lifecycleIds)),
                'By asset type' => $this->countBy('asset_type_id', AssetType::query()->orderBy('name')->pluck('name', 'id')->all()),
            ],
        ];
    }
}
