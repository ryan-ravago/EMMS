<?php

namespace App\Filament\Resources\AssetMovements\Actions;

use App\Models\AssetMovement;
use App\Models\Equipment;
use App\Models\Location;
use App\Services\Rfid\MovementNotifier;
use Carbon\Carbon;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * "Create trip": records an equipment or service vehicle going out (and, if it already did, coming back) by hand,
 * for a yard that has no scanner. It makes no scan logs. The rules and the saving are in
 * AssetMovement::recordManualTrip(); whether a person sees the button is the "Create Trip" permission in Shield.
 */
class CreateTripAction
{
    /**
     * @param  Equipment|null  $asset  the equipment to create the trip for (on its own page); null asks which one
     */
    public static function make(?Equipment $asset = null): Action
    {
        return Action::make('createTrip')
            ->label('Create trip')
            ->icon(Heroicon::OutlinedPlusCircle)
            ->color('primary')
            ->modalHeading($asset === null ? 'Create a trip' : "Create a trip for {$asset->eqm_name}")
            ->modalDescription('For a yard without a scanner. Enter when the equipment left, and when it came back if it already has. Nothing is read from a scanner, so this is recorded as entered manually.')
            ->modalSubmitActionLabel('Create trip')
            ->authorize(fn (): bool => Auth::user()?->can('createTrip', AssetMovement::class) ?? false)
            ->schema([
                $asset === null
                    ? Select::make('asset_id')
                        ->label('Equipment or service vehicle')
                        ->searchable()
                        ->required()
                        ->rule('exists:equipment_units,eqm_id')
                        ->placeholder('Search an asset code or name')
                        ->getSearchResultsUsing(fn (string $search): array => self::assetOptions($search))
                        ->getOptionLabelUsing(fn (string $value): ?string => self::assetLabel(Equipment::query()->with('assetType')->find($value)))
                    : Hidden::make('asset_id')->default($asset->getKey()),
                DateTimePicker::make('time_out')
                    ->label('Time out')
                    ->seconds(false)
                    ->default(now())
                    ->required()
                    ->maxDate(fn (): Carbon => now()->endOfMinute()),
                self::yard('yard_out_id', 'Yard it left'),
                // Entering a time in by hand is the Manual In permission (asset admin and super admin by default).
                Toggle::make('already_back')
                    ->label('It has already come back')
                    ->helperText('Leave off if it is still out: the time in is entered later (Manual in) or comes from a scan.')
                    ->visible(fn (): bool => self::canEnterTimeIn())
                    ->live(),
                DateTimePicker::make('time_in')
                    ->label('Time in')
                    ->seconds(false)
                    ->visible(fn (Get $get): bool => (bool) $get('already_back'))
                    ->required(fn (Get $get): bool => (bool) $get('already_back'))
                    ->minDate(fn (Get $get): ?Carbon => $get('time_out') ? Carbon::parse($get('time_out'))->startOfMinute() : null)
                    ->maxDate(fn (): Carbon => now()->endOfMinute()),
                self::yard('yard_in_id', 'Yard it came back to')
                    ->visible(fn (Get $get): bool => (bool) $get('already_back'))
                    ->required(fn (Get $get): bool => (bool) $get('already_back')),
                Textarea::make('remarks')
                    ->label('Reason / remarks')
                    ->required()
                    ->maxLength(1000)
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                $asset = Equipment::query()->movementTracked()->with('assetType')->find($data['asset_id']);

                if ($asset === null) {
                    Notification::make()->title('Only equipment and service vehicles have trips')->danger()->send();

                    return;
                }

                $timeIn = ! empty($data['already_back']) && ! empty($data['time_in']) ? Carbon::parse($data['time_in']) : null;

                // The form hides the time in from people without Manual In; do not rely on that alone.
                if ($timeIn !== null && ! self::canEnterTimeIn()) {
                    Notification::make()->title('You are not allowed to enter a time in')->danger()->send();

                    return;
                }

                try {
                    $trip = AssetMovement::recordManualTrip(
                        $asset,
                        Carbon::parse($data['time_out']),
                        (int) $data['yard_out_id'],
                        $timeIn,
                        $timeIn === null ? null : (int) ($data['yard_in_id'] ?? $data['yard_out_id']),
                        (int) Auth::id(),
                        $data['remarks'],
                    );
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();

                    return;
                }

                // The same email as a scanned time out / time in (one per event, to the people set up for it in Shield).
                app(MovementNotifier::class)->notifyAfterCommit($timeIn === null ? [[$trip, 'out']] : [[$trip, 'out'], [$trip, 'in']]);

                Notification::make()
                    ->title('Trip created')
                    ->body($timeIn === null
                        ? "{$asset->eqm_name} is now out. Enter its time in with Manual in when it returns."
                        : 'The trip was recorded and acknowledged.')
                    ->success()
                    ->send();
            });
    }

    private static function canEnterTimeIn(): bool
    {
        return Auth::user()?->can('manualInAny', AssetMovement::class) ?? false;
    }

    private static function yard(string $name, string $label): SelectTree
    {
        return SelectTree::make($name)
            ->label($label)
            ->query(fn (): Builder => Location::query(), 'name', 'parent_id')
            ->withCount()
            ->searchable()
            ->enableBranchNode()
            ->rule('exists:locations,id')
            ->required();
    }

    /**
     * Active equipment and service vehicles (accessories have no trips), found by code or name.
     *
     * @return array<int|string, string> equipment id => label
     */
    private static function assetOptions(string $search): array
    {
        return Equipment::query()
            ->with('assetType')
            ->movementTracked()
            ->where('eqm_is_active', true)
            ->where(fn (Builder $query) => $query
                ->where('eqm_prc_code', 'like', "%{$search}%")
                ->orWhere('eqm_name', 'like', "%{$search}%"))
            ->orderBy('eqm_prc_code')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Equipment $asset): array => [$asset->getKey() => self::assetLabel($asset)])
            ->all();
    }

    private static function assetLabel(?Equipment $asset): ?string
    {
        if ($asset === null) {
            return null;
        }

        return collect([$asset->eqm_prc_code, $asset->eqm_name])->filter()->implode(' — ').($asset->assetType ? " ({$asset->assetType->name})" : '');
    }
}
