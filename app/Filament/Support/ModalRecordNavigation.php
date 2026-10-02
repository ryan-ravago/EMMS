<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Adds Previous / Next buttons to the footer of every table "View" modal, so you can
 * step through the rows without closing the modal. It walks the records currently shown
 * in the table (so the table's filters, search and sort are respected) and reuses the
 * already-loaded page, meaning no extra queries.
 *
 * Registered once in AppServiceProvider via ViewAction::configureUsing().
 */
class ModalRecordNavigation
{
    public static function configure(ViewAction $action): ViewAction
    {
        return $action->extraModalFooterActions(
            fn ($action, $livewire, $record = null): array => static::footerActions($action, $livewire, $record),
        );
    }

    /**
     * @return array<int, Action>
     */
    protected static function footerActions(Action $action, mixed $livewire, mixed $record): array
    {
        if (! ($livewire instanceof HasTable) || ! ($record instanceof Model)) {
            return [];
        }

        $keys = static::visibleRecordKeys($livewire);
        $position = array_search((string) $livewire->getTableRecordKey($record), $keys, true);

        if ($position === false) {
            return [];
        }

        return [
            static::makeAction($action, 'previousRecord', 'Previous', $keys[$position - 1] ?? null),
            static::makeAction($action, 'nextRecord', 'Next', $keys[$position + 1] ?? null),
        ];
    }

    protected static function makeAction(Action $parent, string $name, string $label, ?string $recordKey): Action
    {
        $isNext = $name === 'nextRecord';

        return $parent->makeModalAction($name)
            ->label($label)
            ->icon($isNext ? Heroicon::OutlinedChevronRight : Heroicon::OutlinedChevronLeft)
            ->iconPosition($isNext ? IconPosition::After : IconPosition::Before)
            ->color('gray')
            ->outlined()
            ->disabled($recordKey === null)
            ->action(fn ($livewire) => $livewire->replaceMountedAction(
                $parent->getName(),
                [],
                ['table' => true, 'recordKey' => $recordKey],
            ));
    }

    /**
     * Keys of the records on the table's current page, in display order.
     *
     * @return array<int, string>
     */
    protected static function visibleRecordKeys(HasTable $livewire): array
    {
        $records = $livewire->getTableRecords();

        if (! $records instanceof Collection) {
            $records = method_exists($records, 'getCollection') ? $records->getCollection() : collect($records->items());
        }

        return $records
            ->map(fn (Model | array $record): string => (string) $livewire->getTableRecordKey($record))
            ->values()
            ->all();
    }
}
