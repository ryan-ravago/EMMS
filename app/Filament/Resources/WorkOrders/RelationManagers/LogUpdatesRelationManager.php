<?php

namespace App\Filament\Resources\WorkOrders\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class LogUpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'logUpdates';

    protected function getListeners(): array
    {
        return [
            'refreshRelationManager' => '$refresh',
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('wolu_update_note')
                            ->label('Update Note')
                            ->columnSpanFull(),
                        TextEntry::make('by.user_fname')
                            ->label('By')
                            ->formatStateUsing(fn ($record) => "{$record->by->user_fname} {$record->by->user_lname}"),
                        TextEntry::make('wolu_dt')
                            ->label('Date/Time')
                            ->dateTime('M d, Y | h:i A'),
                        TextEntry::make('wolu_attachments')
                            ->label('Attachments')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->html()
                            ->state(function ($record) {
                                if (empty($record->wolu_attachments)) {
                                    return null;
                                }

                                return collect($record->wolu_attachments)
                                    ->map(function ($file) {
                                        $url = Storage::disk('local')->temporaryUrl($file, now()->addMinutes(30));
                                        $name = basename($file);

                                        return "
                                            <div class='flex items-center justify-between gap-3 px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 mb-2'>
                                                <span class='text-xs text-gray-700 dark:text-gray-300 truncate'>{$name}</span>
                                                <a href='{$url}' download='{$name}'
                                                    class='text-xs p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 transition'>
                                                    Download
                                                </a>
                                            </div>
                                        ";
                                    })
                                    ->implode('');
                            }),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('wolu_id')
            ->defaultSort('wolu_dt', 'desc')
            ->columns([
                TextColumn::make('wolu_reply_subject')
                    ->label('Subject')
                    ->wrap()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('wolu_update_note')
                    ->label('Update Note')
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('wolu_attachments')
                    ->label('Attachments')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->overlap(4)
                    ->remainingTextBadge(true)
                    ->imageGallery(), // Enables the gallery viewer,
                TextColumn::make('by.user_fname')
                    ->label('By')
                    ->formatStateUsing(fn ($record) => "{$record->by->user_fname} {$record->by->user_lname}")
                    ->placeholder('-'),
                TextColumn::make('wolu_dt')
                    ->label('Timestamp')
                    ->dateTime('M d, Y | h:i A')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::TwoExtraLarge)
                    ->modalHeading('View Update Note'),
            ])
            ->toolbarActions([]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('wolu_reply_subject')
                    ->label('Subject')
                    ->placeholder('-'),
                TextEntry::make('wolu_update_note')
                    ->label('Update Note')
                    ->columnSpanFull(),
                ImageEntry::make('wolu_attachments')
                    ->label('Attachments')
                    ->columnSpanFull()
                    ->imageGallery(),
                TextEntry::make('wolu_by')
                    ->label('Added by')
                    ->formatStateUsing(fn ($record) => "{$record->by->user_fname} {$record->by->user_lname}"),
                TextEntry::make('wolu_dt')
                    ->label('Timestamp')
                    ->dateTime('M d, Y | h:i A'),
            ]);
    }
}
