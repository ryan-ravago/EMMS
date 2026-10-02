<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adds Previous / Next buttons to a ViewRecord page header.
 *
 * Neighbours are found with the resource's own query (so role/department scoping
 * and nested-resource parents are respected) using keyset pagination: one small
 * indexed query per direction, no list is loaded into memory.
 *
 * To follow a list's default sort, override getRecordNavigationOrder().
 */
trait HasRecordNavigation
{
    /** @var array<int, Action>|null */
    protected ?array $recordNavigationActions = null;

    /** @var array<string, string|int|null> */
    protected array $adjacentRecordKeys = [];

    /**
     * @return array{0: string, 1: 'asc'|'desc'}  [column, direction] matching the list's sort
     */
    protected function getRecordNavigationOrder(): array
    {
        return [$this->getRecord()->getKeyName(), 'asc'];
    }

    protected function getRecordNavigationQuery(): Builder
    {
        $resource = static::getResource();

        $query = $resource::getEloquentQuery()->setEagerLoads([]);

        if ($parentRecord = $this->getParentRecord()) {
            $query = $resource::scopeEloquentQueryToParent($query, $parentRecord);
        }

        return $query;
    }

    /**
     * @param  'previous'|'next'  $direction
     */
    protected function getAdjacentRecordKey(string $direction): string | int | null
    {
        if (! array_key_exists($direction, $this->adjacentRecordKeys)) {
            $this->adjacentRecordKeys[$direction] = $this->findAdjacentRecordKey($direction);
        }

        return $this->adjacentRecordKeys[$direction];
    }

    protected function findAdjacentRecordKey(string $direction): string | int | null
    {
        $record = $this->getRecord();

        [$column, $sort] = $this->getRecordNavigationOrder();

        $keyName = $record->getKeyName();
        $value = $record->getAttribute($column);

        // "Next" = the next row down the list, so it depends on the list's sort direction.
        $towardLarger = ($direction === 'next') === ($sort === 'asc');
        $operator = $towardLarger ? '>' : '<';
        $order = $towardLarger ? 'asc' : 'desc';

        $qualifiedKey = $record->qualifyColumn($keyName);
        $query = $this->getRecordNavigationQuery();

        if ($column === $keyName || $value === null) {
            $query
                ->where($qualifiedKey, $operator, $record->getKey())
                ->orderBy($qualifiedKey, $order);
        } else {
            $qualifiedColumn = $record->qualifyColumn($column);

            // Keyset condition with the primary key as tie-breaker, so records that
            // share the same timestamp are neither skipped nor repeated.
            $query
                ->where(fn (Builder $query) => $query
                    ->where($qualifiedColumn, $operator, $value)
                    ->orWhere(fn (Builder $query) => $query
                        ->where($qualifiedColumn, $value)
                        ->where($qualifiedKey, $operator, $record->getKey())))
                ->orderBy($qualifiedColumn, $order)
                ->orderBy($qualifiedKey, $order);
        }

        return $query->value($qualifiedKey);
    }

    protected function makeRecordNavigationAction(string $direction): Action
    {
        $isNext = $direction === 'next';
        $key = $this->getAdjacentRecordKey($direction);

        return Action::make($direction . 'Record')
            ->label($isNext ? 'Next' : 'Previous')
            ->tooltip($isNext ? 'Go to the next record' : 'Go to the previous record')
            ->icon($isNext ? Heroicon::OutlinedChevronRight : Heroicon::OutlinedChevronLeft)
            ->iconPosition($isNext ? IconPosition::After : IconPosition::Before)
            ->color('gray')
            ->outlined()
            ->url($key === null ? null : static::getResource()::getUrl('view', ['record' => $key], shouldGuessMissingParameters: true))
            ->disabled($key === null);
    }

    public function getCachedHeaderActions(): array
    {
        $this->recordNavigationActions ??= array_map(
            fn (Action $action): Action => $this->cacheAction($action),
            [
                $this->makeRecordNavigationAction('previous'),
                $this->makeRecordNavigationAction('next'),
            ],
        );

        return [
            ...$this->recordNavigationActions,
            ...parent::getCachedHeaderActions(),
        ];
    }
}
