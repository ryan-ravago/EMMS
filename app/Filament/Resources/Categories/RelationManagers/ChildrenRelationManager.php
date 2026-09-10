<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\EquipmentCategory;
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

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('eqmc_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('eqmc_name')
            ->columns([
                TextColumn::make('eqmc_name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordUrl(
                fn(EquipmentCategory $record): string => CategoryResource::getUrl('view', ['record' => $record]),
            )
            ->recordActions([
                // EditAction::make(),
                DissociateAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
