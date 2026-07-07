<?php

namespace App\Filament\Resources\Insps\RelationManagers;

use App\Filament\Resources\InspItems\InspItemResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
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
            ->columns([
                TextColumn::make('inspi_id')
                    ->label('Item ID')
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
