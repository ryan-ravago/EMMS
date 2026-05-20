<?php

namespace App\Filament\Resources\MaintenanceTasks\RelationManagers;

use App\Filament\Resources\MaintenanceTaskLogs\MaintenanceTaskLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MaintenanceTaskLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceTaskLogs';
    protected static ?string $relatedResource = MaintenanceTaskLogResource::class;

    protected function getListeners(): array
    {
        return ['refreshRelationManager' => '$refresh'];
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ])
            ->extraAttributes([
                'style' => 'margin-top: 30px;'
            ]);
    }
}
