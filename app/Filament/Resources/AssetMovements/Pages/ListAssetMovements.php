<?php

namespace App\Filament\Resources\AssetMovements\Pages;

use App\Filament\Resources\AssetMovements\AssetMovementResource;
use App\Models\AssetMovement;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssetMovements extends ListRecords
{
    protected static string $resource = AssetMovementResource::class;

    /** @var array<string, int>|null */
    protected ?array $tabCounts = null;

    /**
     * One query for every tab badge instead of one per tab.
     *
     * @return array<string, int>
     */
    protected function tabCounts(): array
    {
        if ($this->tabCounts === null) {
            $open = 'disregarded_at IS NULL AND acknowledged_at IS NULL';

            $row = AssetMovement::query()
                ->selectRaw("SUM({$open}) as attention")
                ->selectRaw("SUM({$open} AND time_out IS NOT NULL AND time_in IS NOT NULL) as pending")
                ->selectRaw("SUM({$open} AND time_out IS NULL) as missing_out")
                ->selectRaw("SUM({$open} AND time_in IS NULL) as missing_in")
                ->selectRaw('SUM(disregarded_at IS NULL AND acknowledged_at IS NOT NULL) as acknowledged')
                ->selectRaw('SUM(disregarded_at IS NOT NULL) as disregarded')
                ->toBase()
                ->first();

            $this->tabCounts = collect((array) $row)->map(fn (mixed $count): int => (int) $count)->all();
        }

        return $this->tabCounts;
    }

    public function getTabs(): array
    {
        return [
            'attention' => Tab::make('Needs attention')
                ->badge(fn () => $this->tabCounts()['attention'])
                ->modifyQueryUsing(fn (Builder $query) => $query->needingAttention()),
            'pending' => Tab::make('Pending acknowledgement')
                ->badge(fn () => $this->tabCounts()['pending'])
                ->modifyQueryUsing(fn (Builder $query) => $query->pendingAcknowledgement()),
            'missing_out' => Tab::make('Missing time out')
                ->badge(fn () => $this->tabCounts()['missing_out'])
                ->modifyQueryUsing(fn (Builder $query) => $query->missingTimeOut()),
            'missing_in' => Tab::make('Missing time in')
                ->badge(fn () => $this->tabCounts()['missing_in'])
                ->modifyQueryUsing(fn (Builder $query) => $query->missingTimeIn()),
            'acknowledged' => Tab::make('Acknowledged')
                ->badge(fn () => $this->tabCounts()['acknowledged'])
                ->modifyQueryUsing(fn (Builder $query) => $query->acknowledged()),
            'disregarded' => Tab::make('Disregarded')
                ->badge(fn () => $this->tabCounts()['disregarded'])
                ->modifyQueryUsing(fn (Builder $query) => $query->disregarded()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'attention';
    }
}
