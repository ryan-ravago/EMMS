<?php

namespace App\Filament\Pages;

use App\Models\AppUser;
use App\Support\Activity\ActivityLabels;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityLogPage extends Page implements HasTable
{
    use HasPageShield, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static string|UnitEnum|null $navigationGroup = 'Super Admin';

    protected static ?int $navigationSort = 97;

    protected string $view = 'filament.pages.activity-log';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->with('causer')
                    ->latest()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M d, Y h:i A')
                    ->description(fn (Activity $record): string => $record->created_at->diffForHumans())
                    ->sortable(),

                TextColumn::make('causer_id')
                    ->label('User')
                    ->state(fn (Activity $record): string => self::userName($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereIn(
                        'causer_id',
                        AppUser::query()
                            ->where('user_fname', 'like', "%{$search}%")
                            ->orWhere('user_lname', 'like', "%{$search}%")
                            ->pluck('user_id'),
                    )),

                TextColumn::make('event')
                    ->label('Action')
                    ->state(fn (Activity $record): string => ActivityLabels::eventLabel($record->event))
                    ->badge()
                    ->color(fn (Activity $record): string => $record->getExtraProperty('status') === 'failed'
                        ? 'danger'
                        : self::eventColor($record->event)),

                TextColumn::make('subject_type')
                    ->label('Record')
                    ->state(fn (Activity $record): ?string => self::recordName($record))
                    ->placeholder('—')
                    ->description(fn (Activity $record): ?string => $record->subject_type
                        ? ActivityLabels::typeName($record->subject_type)
                        : null)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('properties->subject_label', 'like', "%{$search}%")),

                TextColumn::make('description')
                    ->label('What happened')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('via')
                    ->label('Triggered by')
                    ->state(fn (Activity $record): ?string => $record->getExtraProperty('via'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('batch_uuid')
                    ->label('Bulk')
                    ->state(fn (Activity $record): ?string => $record->batch_uuid ? 'Bulk' : null)
                    ->badge()
                    ->color('warning')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('causer_id')
                    ->label('User')
                    ->searchable()
                    ->options(fn (): array => AppUser::query()
                        ->orderBy('user_fname')
                        ->get(['user_id', 'user_fname', 'user_lname'])
                        ->mapWithKeys(fn (AppUser $user): array => [$user->user_id => trim("{$user->user_fname} {$user->user_lname}")])
                        ->all()),

                SelectFilter::make('event')
                    ->label('Action')
                    ->searchable()
                    ->options(fn (): array => Activity::query()
                        ->whereNotNull('event')
                        ->distinct()
                        ->pluck('event')
                        ->mapWithKeys(fn (string $event): array => [$event => ActivityLabels::eventLabel($event)])
                        ->sort()
                        ->all()),

                SelectFilter::make('subject_type')
                    ->label('Record type')
                    ->searchable()
                    ->options(fn (): array => Activity::query()
                        ->whereNotNull('subject_type')
                        ->distinct()
                        ->pluck('subject_type')
                        ->mapWithKeys(fn (string $type): array => [$type => ActivityLabels::typeName($type)])
                        ->sort()
                        ->all()),

                TernaryFilter::make('batch_uuid')
                    ->label('Bulk actions')
                    ->nullable()
                    ->placeholder('All')
                    ->trueLabel('Bulk actions only')
                    ->falseLabel('Single changes only'),

                Filter::make('created_at')
                    ->label('Date')
                    ->schema([
                        DatePicker::make('from')->label('From')->native(false),
                        DatePicker::make('until')->label('Until')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
                        ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->where('created_at', '<=', Carbon::parse($date)->endOfDay())))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('From '.Carbon::parse($data['from'])->toFormattedDateString())->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Until '.Carbon::parse($data['until'])->toFormattedDateString())->removeField('until');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalHeading('Activity details')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (Activity $record) => view('filament.pages.activity-log-details', self::detailsFor($record))),
            ])
            ->recordAction('details')
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->poll('60s');
    }

    private static function userName(Activity $record): string
    {
        if ($record->causer) {
            return trim("{$record->causer->user_fname} {$record->causer->user_lname}");
        }

        return $record->event === 'scheduled' ? 'Scheduled task' : 'System';
    }

    private static function recordName(Activity $record): ?string
    {
        if (! $record->subject_type) {
            return null;
        }

        return $record->getExtraProperty('subject_label') ?: '#'.$record->subject_id;
    }

    private static function eventColor(?string $event): string
    {
        return match ($event) {
            'created', 'create', 'approve', 'mac' => 'success',
            'updated', 'upt', 'reqcom', 'mwo' => 'info',
            'deleted', 'cancel', 'reject', 'drg' => 'danger',
            'snz' => 'warning',
            'scheduled', 'synced' => 'primary',
            'sync_failed' => 'danger',
            'schedule_changed' => 'info',
            default => 'gray',
        };
    }

    /** Everything the details modal shows, prepared here so the view stays plain markup. */
    private static function detailsFor(Activity $record): array
    {
        $properties = ActivityLabels::properties($record);

        // Whatever is left after the parts that have their own section.
        $extra = $properties
            ->except(['attributes', 'old', 'subject_label', 'via', 'bulk', 'action_id'])
            ->reject(fn ($value) => is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value))
            ->mapWithKeys(fn ($value, $key): array => [Str::headline((string) $key) => ActivityLabels::formatValue($value, (string) $key)])
            ->all();

        $batch = $record->batch_uuid
            ? Activity::query()->where('batch_uuid', $record->batch_uuid)
            : null;

        return [
            'when' => $record->created_at->format('M d, Y h:i:s A').' ('.$record->created_at->diffForHumans().')',
            'user' => self::userName($record),
            'actionLabel' => ActivityLabels::eventLabel($record->event),
            'recordName' => self::recordName($record),
            'recordType' => $record->subject_type ? ActivityLabels::typeName($record->subject_type) : null,
            'via' => $record->getExtraProperty('via'),
            'description' => $record->description,
            'changes' => ActivityLabels::changes($record),
            'extra' => $extra,
            'batchTotal' => $batch?->count() ?? 0,
            'batchItems' => $batch ? (clone $batch)->orderBy('id')->limit(50)->pluck('description')->all() : [],
        ];
    }
}
