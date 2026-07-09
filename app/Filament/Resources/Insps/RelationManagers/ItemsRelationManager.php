<?php

namespace App\Filament\Resources\Insps\RelationManagers;

use App\Filament\Resources\InspItems\InspItemResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $relatedResource = InspItemResource::class;

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
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Inspection Task Details')  // Modal title
                    ->modalWidth('md')  // 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'
                    // ->modalIcon('heroicon-o-eye') // Custom heading icon
                    ->modalSubmitActionLabel('Close')  // Change button text
                    ->infolist([
                        Section::make()
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
                    ]),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
