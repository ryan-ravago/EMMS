<?php

namespace App\Filament\Resources\Locations\Pages;

use Alareqi\FilamentTree\Concerns\InteractsWithTreeTable;
use App\Filament\Resources\Locations\LocationResource;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ListLocations extends ListRecords
{
    use InteractsWithTreeTable;

    protected static string $resource = LocationResource::class;

    // Tree icons: a place that contains other places gets a building, a place at the end of
    // a branch gets a map pin (instead of the plugin's folder and document icons).
    public function getTreeExpandedIcon(Model $record): string|BackedEnum
    {
        return Heroicon::OutlinedBuildingOffice2;
    }

    public function getTreeCollapsedIcon(Model $record): string|BackedEnum
    {
        return Heroicon::OutlinedBuildingOffice2;
    }

    public function getTreeLeafIcon(Model $record): string|BackedEnum
    {
        return Heroicon::OutlinedMapPin;
    }

    /**
     * Only the rows that can be seen are loaded.
     *
     * The plugin's own version loads every location as a full model on every render (and every 30-second refresh)
     * and then hides the collapsed ones. Here the whole tree is read as two light columns (id, parent) to work out
     * which rows are visible, and only those rows are loaded as models. A branch is loaded when it is expanded,
     * and a search shows the matches with their parents, the same as before.
     *
     * @return Collection<int|string, Model>
     */
    public function getTableRecords(): Collection
    {
        if ($this->cachedTableRecords instanceof Collection) {
            return $this->cachedTableRecords;
        }

        $configuration = $this->getTreeConfiguration();
        $query = $this->getTable()->getQuery();
        $keyName = $query->getModel()->getKeyName();
        $parentColumn = $configuration->parentColumn;
        $isFiltered = $this->isTreeFiltered();

        /** @var array<string, string|null> $parentOf */
        $parentOf = [];

        foreach ((clone $query)->reorder()->toBase()->orderBy($keyName)->get([$keyName, $parentColumn]) as $row) {
            $parentOf[(string) $row->{$keyName}] = $row->{$parentColumn} === null ? null : (string) $row->{$parentColumn};
        }

        $included = $isFiltered ? $this->treeKeysWithParents($parentOf, $keyName) : null;
        $rootKey = $this->normalizeTreeKey($configuration->rootValue);

        /** @var array<string, array<int, string>> $childrenOf */
        $childrenOf = [];

        foreach ($parentOf as $key => $parentKey) {
            $key = (string) $key;

            if ($included !== null && ! isset($included[$key])) {
                continue;
            }

            // A location whose parent is missing is shown at the top instead of disappearing.
            $hasParent = $parentKey !== null && array_key_exists($parentKey, $parentOf) && ($included === null || isset($included[$parentKey]));

            $childrenOf[$hasParent ? $parentKey : $rootKey][] = $key;
        }

        $this->treeRecordsWithChildren = [];
        $this->treeRecordDepths = [];
        $this->treeRecordLineContinuations = [];

        foreach (array_keys($childrenOf) as $parentKey) {
            if ((string) $parentKey !== $rootKey) {
                $this->treeRecordsWithChildren[(string) $parentKey] = true;
            }
        }

        if (! $this->treeExpansionInitialized) {
            $this->expandedTreeRecords = $configuration->defaultExpanded ? $this->treeRecordsWithChildren : [];
            $this->treeExpansionInitialized = true;
        }

        /** @var array<int, string> $visibleKeys */
        $visibleKeys = [];
        $visited = [];

        $append = function (string $key, int $depth, array $lineContinuations) use (&$append, &$visibleKeys, &$visited, $childrenOf, $isFiltered): void {
            if (isset($visited[$key])) {
                return;
            }

            $visited[$key] = true;
            $visibleKeys[] = $key;
            $this->treeRecordDepths[$key] = $depth;
            $this->treeRecordLineContinuations[$key] = $lineContinuations;

            $isOpen = $isFiltered
                ? ! isset($this->collapsedFilteredTreeRecords[$key])
                : isset($this->expandedTreeRecords[$key]);

            if (! $isOpen) {
                return;
            }

            $children = $childrenOf[$key] ?? [];

            foreach ($children as $index => $childKey) {
                $append($childKey, $depth + 1, [...$lineContinuations, $index < count($children) - 1]);
            }
        };

        foreach ($childrenOf[$rootKey] ?? [] as $rootChildKey) {
            $append($rootChildKey, 0, []);
        }

        $models = new Collection;

        foreach (array_chunk($visibleKeys, 500) as $chunk) {
            $chunkQuery = $this->getTable()->getQuery();

            foreach ($this->getTable()->getVisibleColumns() as $column) {
                $column->applyRelationshipAggregates($chunkQuery);
                $column->applyEagerLoading($chunkQuery);
            }

            $models = $models->concat($chunkQuery->whereKey($chunk)->get());
        }

        $modelsByKey = $models->keyBy(fn (Model $model): string => (string) $model->getKey());

        $records = $query->getModel()->newCollection(
            collect($visibleKeys)
                ->map(fn (string $key): ?Model => $modelsByKey->get($key))
                ->filter()
                ->all()
        )->keyBy(fn (Model $model): string => (string) $model->getKey());

        return $this->cachedTableRecords = $records;
    }

    /**
     * The locations a search or filter finds, plus all of their parents, so a match is shown in its place in the tree.
     *
     * @param  array<string, string|null>  $parentOf
     * @return array<string, true>
     */
    private function treeKeysWithParents(array $parentOf, string $keyName): array
    {
        $matches = $this->getFilteredTableQuery()?->reorder()->pluck($keyName) ?? collect();
        $included = [];

        foreach ($matches as $match) {
            $current = (string) $match;

            while ($current !== null && array_key_exists($current, $parentOf) && ! isset($included[$current])) {
                $included[$current] = true;
                $current = $parentOf[$current];
            }
        }

        return $included;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
