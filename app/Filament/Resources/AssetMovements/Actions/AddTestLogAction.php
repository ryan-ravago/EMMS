<?php

namespace App\Filament\Resources\AssetMovements\Actions;

use App\Models\AssetMovement;
use App\Models\AssetTag;
use App\Models\AssetTagLog;
use App\Models\Location;
use App\Services\Rfid\MovementRecorder;
use Carbon\Carbon;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * "Add test scan": stores one scan log for an equipment tag, exactly as if a reader had sent it, so the trips
 * (and this screen) can be tried without a physical reader. Whether a person sees it is the "Add Test Log"
 * permission in Shield, which no role has by default.
 */
class AddTestLogAction
{
    /** What the trip recorder did with the scan, in words. */
    private const OUTCOMES = [
        'opened' => 'A new trip was opened.',
        'closed' => 'The open trip was closed with this time in.',
        'in_only' => 'A trip with only a time in was created (no open trip to close).',
        'ignored' => 'It belongs to a trip that is already recorded (another tag of the same equipment).',
        'skipped' => 'It did not change any trip.',
    ];

    public static function make(): Action
    {
        return Action::make('addTestLog')
            ->label('Add test scan')
            ->icon(Heroicon::OutlinedBeaker)
            ->color('gray')
            ->modalHeading('Add a test scan')
            ->modalDescription('Stores a real scan log, as if a reader had sent it, and updates the equipment trips. It is not marked as a test and cannot be removed afterwards.')
            ->modalSubmitActionLabel('Add scan')
            ->authorize(fn (): bool => Auth::user()?->can('addTestLog', AssetMovement::class) ?? false)
            ->schema([
                Select::make('tag_id')
                    ->label('Tag')
                    ->searchable()
                    ->required()
                    ->rule('exists:asset_tags,tag_id')
                    ->placeholder('Search a tag or an equipment code')
                    ->getSearchResultsUsing(fn (string $search): array => self::tagOptions($search))
                    ->getOptionLabelUsing(fn (string $value): ?string => self::tagLabel($value)),
                Select::make('status')
                    ->options(['OUT' => 'Out', 'IN' => 'In'])
                    ->default('OUT')
                    ->required()
                    ->native(false),
                DateTimePicker::make('detected_at')
                    ->label('Detected at')
                    ->default(now())
                    ->required()
                    ->maxDate(fn (): Carbon => now()->endOfMinute()),
                SelectTree::make('yard_id')
                    ->label('Yard (reader location)')
                    ->query(fn (): Builder => Location::query(), 'name', 'parent_id')
                    ->withCount()
                    ->searchable()
                    ->enableBranchNode()
                    ->default(fn (): ?int => Location::query()->orderBy('id')->value('id'))
                    ->rule('exists:locations,id')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $attributes = [
                    'tag_id' => $data['tag_id'],
                    'yard_id' => (int) $data['yard_id'],
                    'status' => $data['status'],
                    'detected_at' => Carbon::parse($data['detected_at'])->format('Y-m-d H:i:s'),
                ];

                if (AssetTagLog::query()->where($attributes)->exists()) {
                    Notification::make()->title('That scan already exists')->warning()->send();

                    return;
                }

                $outcome = DB::transaction(function () use ($attributes): string {
                    $log = (new AssetTagLog)->forceFill($attributes);
                    $log->save();

                    $result = app(MovementRecorder::class)->record([$log]);

                    activity()
                        ->event('created')
                        ->withProperties($attributes)
                        ->log("Added a test {$attributes['status']} scan for tag {$attributes['tag_id']}");

                    return (string) array_key_first(array_filter($result));
                });

                Notification::make()
                    ->title('Test scan added')
                    ->body(self::OUTCOMES[$outcome] ?? self::OUTCOMES['skipped'])
                    ->success()
                    ->send();
            });
    }

    /**
     * Active tags of equipment (accessories are not tracked), found by tag id, equipment code or name.
     *
     * @return array<string, string> tag id => label
     */
    private static function tagOptions(string $search): array
    {
        return AssetTag::query()
            ->with('asset')
            ->where('is_deleted', false)
            ->whereHas('asset', fn ($asset) => $asset->equipmentAssets())
            ->where(fn ($query) => $query
                ->where('tag_id', 'like', "%{$search}%")
                ->orWhereHas('asset', fn ($asset) => $asset
                    ->where('eqm_prc_code', 'like', "%{$search}%")
                    ->orWhere('eqm_name', 'like', "%{$search}%")))
            ->orderBy('tag_id')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (AssetTag $tag): array => [$tag->tag_id => self::label($tag)])
            ->all();
    }

    private static function tagLabel(string $tagId): ?string
    {
        $tag = AssetTag::query()->with('asset')->find($tagId);

        return $tag === null ? null : self::label($tag);
    }

    private static function label(AssetTag $tag): string
    {
        return $tag->tag_id.' — '.($tag->asset?->eqm_prc_code ?? 'no equipment');
    }
}
