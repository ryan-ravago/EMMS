<?php

namespace App\Filament\Resources\WorkOrders\RelationManagers;

use App\Models\WorkOrderNote;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('won_note')
                    ->label('Note')
                    ->required()
                    ->autosize()
                    ->rows(3)
                    ->maxLength(255)
                    ->live(debounce: '500ms')
                    ->hint(function (Get $get) {
                        $max = 255;
                        $current = strlen($get('won_note') ?? '');
                        $remaining = $max - $current;
                        return "Remaining: {$remaining} characters";
                    })
                    ->hintColor('primary')
                    ->columnSpanFull(),
                FileUpload::make('won_attachments')
                    ->label('Attachment')
                    ->image()
                    ->imageEditor()
                    ->multiple()
                    ->openable()
                    ->panelLayout('grid')
                    ->reorderable()
                    ->maxSize(2048)
                    ->columnSpanFull()
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('won_note')
                    ->label('Note')
                    ->inlineLabel()
                    ->columnSpanFull(),
                ImageEntry::make('won_attachments')
                    ->label('Attachments')
                    ->inlineLabel()
                    ->placeholder('-')
                    ->columnSpanFull()
                    ->imageGallery(),
                TextEntry::make('creator.fullName')
                    ->label('Noted By')
                    ->inlineLabel()
                    ->columnSpanFull(),
                TextEntry::make('won_created_at')
                    ->label('Date Noted')
                    ->inlineLabel()
                    ->columnSpanFull()
                    ->dateTime('M d Y, h:i A'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['creator']);
            })
            ->recordTitleAttribute('won_id')
            ->columns([
                TextColumn::make('won_note')
                    ->label('Note')
                    ->wrap()
                    ->limit(220)
                    ->tooltip(fn($record) => $record->won_note)
                    ->sortable()
                    ->listWithLineBreaks(),
                ImageColumn::make('won_attachments')
                    ->label('Attachments')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->overlap(4),
                TextColumn::make('creator.fullName')
                    ->label('Noted By')
                    ->sortable(),
                TextColumn::make('won_created_at')
                    ->label('Timestamp')
                    ->dateTime('M d Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('won_created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Note')
                    ->authorize(fn() => auth()->user()->can('create', WorkOrderNote::class))
                    ->modalWidth('2xl')
                    ->createAnother(false)
                    ->modalHeading('Add Note Form')
                    ->modalDescription('Add a note and optional attachments.')
                    ->modalSubmitActionLabel('Submit')
                    ->modalCancelActionLabel('Cancel')
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->mutateDataUsing(function (array $data): array {
                        $data['won_created_by'] = auth()->id();
                        $data['won_created_at'] = now();
                        return $data;
                    }),
                // AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('View Work Order Note'),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
