<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Exports\EquipmentExporter;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Support\ExportPreviewAction;
use App\Models\Equipment;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListEquipment extends ListRecords
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportPreviewAction::make()
                ->exporter(EquipmentExporter::class)
                ->authorize(fn () => Auth::user()->can('previewExport', Equipment::class)),
            ExportAction::make()
                ->exporter(EquipmentExporter::class)
                ->color('gray')
                ->authorize(fn () => Auth::user()->can('export', Equipment::class)),
        ];
    }

    /** @var array{total:int, active:int, inactive:int}|null */
    protected ?array $tabCounts = null;

    /**
     * One query for all three tab badges instead of three. It starts from the resource query, so the
     * Equipment and Accessories pages count only their own assets, the same rows the table shows.
     */
    protected function tabCounts(): array
    {
        if ($this->tabCounts === null) {
            $row = static::getResource()::getEloquentQuery()
                ->withoutEagerLoads()
                ->selectRaw('COUNT(*) as total, SUM(eqm_is_active = 1) as active, SUM(eqm_is_active = 0) as inactive')
                ->first();

            $this->tabCounts = [
                'total' => (int) $row->total,
                'active' => (int) $row->active,
                'inactive' => (int) $row->inactive,
            ];
        }

        return $this->tabCounts;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn () => $this->tabCounts()['total']),

            'active' => Tab::make('Active')
                ->badge(fn () => $this->tabCounts()['active'])
                ->modifyQueryUsing(fn (Builder $query) => $query->where('eqm_is_active', 1)),

            'inactive' => Tab::make('Inactive')
                ->badge(fn () => $this->tabCounts()['inactive'])
                ->modifyQueryUsing(fn (Builder $query) => $query->where('eqm_is_active', 0)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}
