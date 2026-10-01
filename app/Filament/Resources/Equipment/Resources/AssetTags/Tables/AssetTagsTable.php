<?php

namespace App\Filament\Resources\Equipment\Resources\AssetTags\Tables;

use App\Models\AssetTag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AssetTagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tag_id')
            ->columns([
                TextColumn::make('tag_id')
                    ->label('Tag')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('logs_count')
                    ->label('Logs')
                    ->counts('logs')
                    ->sortable()
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                    ->authorize(fn(AssetTag $record) => Auth::user()?->can('update', $record) ?? false)
                    ->visible(fn(AssetTag $record): bool => $record->logs_count === 0)
                    ->modalDescription('Only tags without logs can be deleted.')
                    ->after(fn($livewire) => $livewire->dispatch('equipment-tags-updated')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorize(fn() => Auth::user()?->can('update', AssetTag::class) ?? false)
                        ->modalDescription('Only tags without logs can be deleted.')
                        ->after(fn($livewire) => $livewire->dispatch('equipment-tags-updated'))
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            $withLogs = $records->filter(fn(AssetTag $tag): bool => $tag->logs_count > 0);

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
