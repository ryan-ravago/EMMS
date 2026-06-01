<?php

namespace App\Filament\Resources\WorkOrders\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'reportSubmissions';

    protected static ?string $title = 'Reports';

    protected function getListeners(): array
    {
        return [
            'refreshLogsRelationManager' => '$refresh',
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rs_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rs_id')
            ->columns([
                TextColumn::make('rs_work_date')
                    ->label('Work Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('submittedBy.user_fname')
                    ->label('Submitted by')
                    ->formatStateUsing(fn($record) => "{$record->submittedBy->user_fname} {$record->submittedBy->user_lname}")
                    ->searchable(query: fn($query, $search) => $query->whereHas(
                        'submittedBy',
                        fn($q) => $q
                            ->where('user_fname', 'like', "%{$search}%")
                            ->orWhere('user_lname', 'like', "%{$search}%")
                    )),
                TextColumn::make('workers')
                    ->label('Workers')
                    ->badge()
                    ->listWithLineBreaks()
                    ->icon('heroicon-o-user-circle')
                    ->state(fn($record) => $record->workers->map(fn($w) => "{$w->user_fname} {$w->user_lname}")->toArray()),
                TextColumn::make('rs_submitted_dt')
                    ->label('Timestamp')
                    ->dateTime('M d, Y | h:i A')
                    ->sortable(),
            ])
            ->defaultSort('rs_submitted_dt', 'desc')
            // ->groups([
            //     Group::make('rs_work_date')
            //         ->label('Work Date')
            //         ->date()
            //         ->collapsible(),
            // ])
            // ->defaultGroup('rs_work_date')
            // ->modifyQueryUsing(fn($query) => $query->with(['submittedBy', 'workers']))
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
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
