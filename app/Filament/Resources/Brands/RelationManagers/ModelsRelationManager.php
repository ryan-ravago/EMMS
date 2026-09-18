<?php

namespace App\Filament\Resources\Brands\RelationManagers;

use App\Filament\Resources\Models\ModelResource;
use App\Models\EquipmentModel;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    protected static ?string $title = 'Models';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->models()->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('eqmm_name')
            ->columns([
                TextColumn::make('eqmm_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type.eqmt_name')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
            ])
            ->recordUrl(fn(EquipmentModel $record): string => ModelResource::getUrl('view', ['record' => $record]));
    }
}
