<?php

namespace App\Filament\Resources\Equipment\Resources\Insps\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('inspi_task')
                    ->default(null),
                Select::make('inspi_result')
                    ->options(['Passed' => 'Passed', 'Failed' => 'Failed', 'N/A' => 'N/ a'])
                    ->default(null),
                Textarea::make('inspi_remarks')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('inspi_id')
                            ->label('Task ID')
                            ->inlineLabel(),
                        TextEntry::make('inspi_task')
                            ->label('Task')
                            ->inlineLabel(),
                        TextEntry::make('inspi_result')
                            ->label('Result')
                            ->badge()
                            ->inlineLabel(),
                        TextEntry::make('inspi_remarks')
                            ->label('Remarks')
                            ->inlineLabel(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inspi_id')
            ->heading('Inspection Tasks')
            ->columns([
                TextColumn::make('inspi_id')
                    ->label('Task ID')
                    ->searchable(),
                TextColumn::make('inspi_task')
                    ->label('Task')
                    ->searchable(),
                TextColumn::make('inspi_result')
                    ->label('Result')
                    ->badge(),
                TextColumn::make('inspi_remarks')
                    ->label('Remarks')
                    ->placeholder('—')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Inspection Task Details')
                    ->modalWidth('md'),
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
