<?php

namespace App\Filament\Resources\Equipment\Resources\AssetTags\Tables;

use App\Models\AppUser;
use App\Models\AssetTag;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AssetTagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tag_id')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['assignedBy', 'scannedBy']))
            ->columns([
                TextColumn::make('tag_id')
                    ->label('Tag')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_deleted')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Retired' : 'Active')
                    ->color(fn (bool $state): string => $state ? 'gray' : 'success')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('assigned_by')
                    ->label('Assigned By')
                    ->state(fn (AssetTag $record): ?string => $record->assignedBy?->activityLabel())
                    ->placeholder('—')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'assignedBy',
                        fn (Builder $user) => $user->where('user_fname', 'like', "%{$search}%")->orWhere('user_lname', 'like', "%{$search}%"),
                    ))
                    ->toggleable(),
                TextColumn::make('assigned_date')
                    ->label('Assigned On')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('scanned_by')
                    ->label('Scanned By')
                    ->state(fn (AssetTag $record): ?string => $record->scannedBy?->activityLabel())
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scanned_date')
                    ->label('Scanned On')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('logs_count')
                    ->label('Logs')
                    ->counts('logs')
                    ->sortable()
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('assigned_by')
                    ->label('Assigned By')
                    ->relationship('assignedBy', 'user_fname')
                    ->getOptionLabelFromRecordUsing(fn (AppUser $user): string => $user->activityLabel())
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_deleted')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Retired')
                    ->falseLabel('Active'),
            ])
            ->recordActions([
                ViewAction::make(),
                // A retired tag stays on its asset with all its logs; it is only marked as no longer in use.
                Action::make('retire')
                    ->label('Retire')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (AssetTag $record): bool => ! $record->is_deleted)
                    ->authorize(fn (AssetTag $record): bool => Auth::user()?->can('update', $record) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Retire this tag?')
                    ->modalDescription('The tag stays on the asset and keeps its logs, but is marked as no longer in use. You can reactivate it later.')
                    ->action(function (AssetTag $record): void {
                        $record->update(['is_deleted' => true]);

                        Notification::make()->title('Tag retired')->success()->send();
                    }),
                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (AssetTag $record): bool => $record->is_deleted)
                    ->authorize(fn (AssetTag $record): bool => Auth::user()?->can('update', $record) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Reactivate this tag?')
                    ->action(function (AssetTag $record): void {
                        $record->update(['is_deleted' => false]);

                        Notification::make()->title('Tag reactivated')->success()->send();
                    }),
                DeleteAction::make()
                    ->authorize(fn (AssetTag $record) => Auth::user()?->can('update', $record) ?? false)
                    ->visible(fn (AssetTag $record): bool => $record->logs_count === 0)
                    ->modalDescription('Only tags without logs can be deleted.')
                    ->after(fn ($livewire) => $livewire->dispatch('equipment-tags-updated')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retire')
                        ->label('Retire selected')
                        ->icon('heroicon-o-archive-box')
                        ->authorizeIndividualRecords('update')
                        ->requiresConfirmation()
                        ->modalHeading('Retire the selected tags?')
                        ->modalDescription('The tags stay on their assets and keep their logs, but are marked as no longer in use.')
                        ->action(function (Collection $records): void {
                            // One save per tag, so each change is written to the activity log.
                            $records->reject(fn (AssetTag $tag): bool => $tag->is_deleted)->each->update(['is_deleted' => true]);

                            Notification::make()->title('Tags retired')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('reactivate')
                        ->label('Reactivate selected')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->authorizeIndividualRecords('update')
                        ->requiresConfirmation()
                        ->modalHeading('Reactivate the selected tags?')
                        ->action(function (Collection $records): void {
                            $records->filter(fn (AssetTag $tag): bool => $tag->is_deleted)->each->update(['is_deleted' => false]);

                            Notification::make()->title('Tags reactivated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('update')
                        ->modalDescription('Only tags without logs can be deleted.')
                        ->after(fn ($livewire) => $livewire->dispatch('equipment-tags-updated'))
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            $withLogs = $records->filter(fn (AssetTag $tag): bool => $tag->logs_count > 0);

                            if ($withLogs->isNotEmpty()) {
                                Notification::make()
                                    ->title('Cannot delete tags that already have logs')
                                    ->body($withLogs->pluck('tag_id')->implode(', '))
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}
