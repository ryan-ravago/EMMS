<?php

namespace App\Filament\Resources\InspectionItems\RelationManagers;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\InspectionItemLog;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('inil_action_made')
                    ->required(),
                TextInput::make('inil_status_log')
                    ->required(),
                Textarea::make('inil_remarks')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('inil_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('inil_dt')
                    ->required(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('inil_action_made')
                    ->label('Action'),
                TextEntry::make('status.status_title')
                    ->label('Status')
                    ->color(fn (InspectionItemLog $record) => $record->status->status_color)
                    ->icon(fn (InspectionItemLog $record) => $record->status->status_icon)
                    ->badge(),
                TextEntry::make('workOrder.wo_no')
                    ->label('WO #')
                    ->color('info')
                    ->placeholder('—')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->url(
                        fn ($record) => $record->workOrder
                            ? WorkOrderResource::getUrl('view', ['record' => $record->workOrder->wo_id])
                            : null
                    ),
                TextEntry::make('user.user_fname')
                    ->label('Logged by')
                    ->formatStateUsing(fn ($record) => $record->user
                        ? "{$record->user->user_fname} {$record->user->user_lname}"
                        : '—'),
                TextEntry::make('inil_dt')
                    ->label('Date & Time')
                    ->dateTime('M d, Y | h:i A'),
                TextEntry::make('inil_remarks')
                    ->label('Remarks')
                    ->placeholder('—')
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inil_id')
            ->columns([
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn (InspectionItemLog $record) => $record->status->status_color)
                    ->icon(fn (InspectionItemLog $record) => $record->status->status_icon)
                    ->searchable(),
                TextColumn::make('inil_action_made')
                    ->label('Last Action Made')
                    ->searchable(),
                TextColumn::make('user.user_fname')
                    ->label('Logged by')
                    ->formatStateUsing(fn ($record) => $record->user
                        ? "{$record->user->user_fname} {$record->user->user_lname}"
                        : '—'),
                TextColumn::make('inil_remarks')
                    ->label('Remarks')
                    ->placeholder('—')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->inil_remarks),
                TextColumn::make('inil_dt')
                    ->label('Timestamp')
                    ->dateTime('M d, Y | h:i A')
                    ->sortable(),
                TextColumn::make('workOrder.wo_no')
                    ->label('WO #')
                    ->sortable()
                    ->color('info')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition('after')
                    ->url(
                        fn ($record) => $record->workOrder
                            ? WorkOrderResource::getUrl('view', ['record' => $record->workOrder->wo_id])
                            : null
                    ),
            ])
            ->defaultSort('inil_dt', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
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
