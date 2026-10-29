<?php

namespace App\Filament\Resources\AssetMovements\Actions;

use App\Models\AssetMovement;
use Carbon\Carbon;
use Closure;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;

/**
 * The four things a person can do to a trip: acknowledge it, fill in a missing time out or time in, or disregard it.
 * Used on the list and on the trip page. Whether an action is offered depends on the trip's state (AssetMovement)
 * and on the person's permission (AssetMovementPolicy); the state change itself is done by AssetMovement.
 */
class VerificationActions
{
    /**
     * @return list<Action>
     */
    public static function all(): array
    {
        return [self::acknowledge(), self::manualOut(), self::manualIn(), self::disregard()];
    }

    public static function acknowledge(): Action
    {
        return Action::make('acknowledge')
            ->label('Acknowledge')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->modalHeading('Acknowledge this trip')
            ->modalDescription('Confirm that the time out and time in were recorded correctly.')
            ->modalSubmitActionLabel('Acknowledge')
            ->visible(fn (AssetMovement $record): bool => $record->canBeAcknowledged())
            ->authorize(fn (AssetMovement $record): bool => self::can('acknowledge', $record))
            ->schema([self::remarks('Remarks')])
            ->action(fn (AssetMovement $record, array $data) => self::perform(
                fn () => $record->acknowledge((int) Auth::id(), $data['remarks']),
                'Trip acknowledged',
            ));
    }

    public static function manualOut(): Action
    {
        return Action::make('manualOut')
            ->label('Manual out')
            ->icon(Heroicon::OutlinedArrowRightStartOnRectangle)
            ->color('warning')
            ->modalHeading('Record the time out manually')
            ->modalDescription('The equipment came back but its time out was not scanned. Enter when it left. Saving this also acknowledges the trip.')
            ->modalSubmitActionLabel('Save time out')
            ->visible(fn (AssetMovement $record): bool => $record->canRecordManualOut())
            ->authorize(fn (AssetMovement $record): bool => self::can('manualOut', $record))
            ->schema([
                DateTimePicker::make('time_out')
                    ->label('Time out')
                    ->seconds(false)
                    ->required()
                    ->maxDate(fn (AssetMovement $record): Carbon => self::earlier(now(), $record->time_in)),
                self::remarks('Reason / remarks'),
            ])
            ->action(fn (AssetMovement $record, array $data) => self::perform(
                fn () => $record->recordManualOut(Carbon::parse($data['time_out']), (int) Auth::id(), $data['remarks']),
                'Time out recorded and trip acknowledged',
            ));
    }

    public static function manualIn(): Action
    {
        return Action::make('manualIn')
            ->label('Manual in')
            ->icon(Heroicon::OutlinedArrowLeftEndOnRectangle)
            ->color('warning')
            ->modalHeading('Record the time in manually')
            ->modalDescription('The equipment is back but its time in was not scanned. Enter when it returned. Saving this also acknowledges the trip.')
            ->modalSubmitActionLabel('Save time in')
            ->visible(fn (AssetMovement $record): bool => $record->canRecordManualIn())
            ->authorize(fn (AssetMovement $record): bool => self::can('manualIn', $record))
            ->schema([
                DateTimePicker::make('time_in')
                    ->label('Time in')
                    ->seconds(false)
                    ->required()
                    ->minDate(fn (AssetMovement $record): Carbon => $record->time_out->copy()->startOfMinute())
                    ->maxDate(now()),
                self::remarks('Reason / remarks'),
            ])
            ->action(fn (AssetMovement $record, array $data) => self::perform(
                fn () => $record->recordManualIn(Carbon::parse($data['time_in']), (int) Auth::id(), $data['remarks']),
                'Time in recorded and trip acknowledged',
            ));
    }

    public static function disregard(): Action
    {
        return Action::make('disregard')
            ->label('Disregard')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->modalHeading('Disregard this trip')
            ->modalDescription('Use this when the scans do not describe a real trip. A disregarded trip cannot be changed afterwards.')
            ->modalSubmitActionLabel('Disregard')
            ->requiresConfirmation()
            ->visible(fn (AssetMovement $record): bool => $record->canBeDisregarded())
            ->authorize(fn (AssetMovement $record): bool => self::can('disregard', $record))
            ->schema([self::remarks('Reason')])
            ->action(fn (AssetMovement $record, array $data) => self::perform(
                fn () => $record->disregard((int) Auth::id(), $data['remarks']),
                'Trip disregarded',
            ));
    }

    /**
     * Acknowledge many trips at once, with one set of remarks. Only trips with the status "Pending acknowledgement"
     * are acknowledged; every other selected trip is skipped and counted, never forced.
     */
    public static function bulkAcknowledge(): BulkAction
    {
        return BulkAction::make('bulkAcknowledge')
            ->label('Acknowledge selected')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->modalHeading('Acknowledge the selected trips')
            ->modalDescription('Confirm that the time out and time in of every selected trip were recorded correctly. Only trips with the status "Pending acknowledgement" are acknowledged; any other selected trip is skipped.')
            ->modalSubmitActionLabel('Acknowledge')
            ->authorize(fn (): bool => Auth::user()?->can('acknowledgeAny', AssetMovement::class) ?? false)
            ->schema([self::remarks('Remarks')])
            ->fetchSelectedRecords(false)
            ->deselectRecordsAfterCompletion()
            ->action(fn (HasTable $livewire, array $data) => self::performMany(
                $livewire,
                fn (array $ids): int => AssetMovement::acknowledgeMany($ids, (int) Auth::id(), $data['remarks']),
                'acknowledged',
            ));
    }

    /**
     * Disregard many trips at once, with one reason. Trips with the status "Pending acknowledgement", "Missing time in"
     * or "Missing time out" are disregarded; acknowledged and disregarded trips are skipped and counted.
     */
    public static function bulkDisregard(): BulkAction
    {
        return BulkAction::make('bulkDisregard')
            ->label('Disregard selected')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Disregard the selected trips')
            ->modalDescription('Use this when the scans do not describe real trips. Trips with the status "Pending acknowledgement", "Missing time in" or "Missing time out" are disregarded, and they cannot be changed afterwards. Acknowledged and disregarded trips are skipped.')
            ->modalSubmitActionLabel('Disregard')
            ->authorize(fn (): bool => Auth::user()?->can('disregardAny', AssetMovement::class) ?? false)
            ->schema([self::remarks('Reason')])
            ->fetchSelectedRecords(false)
            ->deselectRecordsAfterCompletion()
            ->action(fn (HasTable $livewire, array $data) => self::performMany(
                $livewire,
                fn (array $ids): int => AssetMovement::disregardMany($ids, (int) Auth::id(), $data['remarks']),
                'disregarded',
            ));
    }

    /**
     * Applies a bulk change to the selected trips, 100 at a time, and reports how many went through and how many
     * were skipped. Only the selected ids are read here: the trips themselves are never loaded into memory, and
     * each chunk is one locked read and one UPDATE (see AssetMovement::acknowledgeMany / disregardMany).
     *
     * @param  Closure(array<int, int|string>): int  $changeChunk  changes one chunk of ids and returns how many it changed
     */
    private static function performMany(HasTable $livewire, Closure $changeChunk, string $doneWord): void
    {
        $ids = $livewire->getSelectedTableRecordsQuery(shouldFetchSelectedRecords: false)->pluck('asset_movements.id');

        $done = 0;

        foreach ($ids->chunk(100) as $chunk) {
            $done += $changeChunk($chunk->values()->all());
        }

        $skipped = $ids->count() - $done;

        $notification = Notification::make()
            ->title($done === 1 ? "1 trip {$doneWord}" : "{$done} trips {$doneWord}");

        if ($skipped > 0) {
            $notification->body($skipped === 1 ? '1 trip was skipped because it is not in a state that allows this.' : "{$skipped} trips were skipped because they are not in a state that allows this.");
        }

        ($done > 0 ? $notification->success() : $notification->warning())->send();
    }

    private static function remarks(string $label): Textarea
    {
        return Textarea::make('remarks')
            ->label($label)
            ->required()
            ->maxLength(1000)
            ->rows(3);
    }

    private static function can(string $ability, AssetMovement $record): bool
    {
        return Auth::user()?->can($ability, $record) ?? false;
    }

    private static function earlier(Carbon $a, ?Carbon $b): Carbon
    {
        return $b !== null && $b->lessThan($a) ? $b : $a;
    }

    /**
     * @param  Closure(): void  $change
     */
    private static function perform(Closure $change, string $success): void
    {
        try {
            $change();
        } catch (DomainException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            return;
        }

        Notification::make()->title($success)->success()->send();
    }
}
