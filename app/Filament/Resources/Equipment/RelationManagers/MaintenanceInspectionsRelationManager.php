<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\MaintenanceInspections\MaintenanceInspectionResource;
use App\Models\MaintenanceInspection;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The closed maintenance inspections of an asset, matched to it by PRC code.
 */
class MaintenanceInspectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceInspections';

    protected static ?string $title = 'Inspections';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->maintenanceInspections()->visibleTo(auth()->user())->count();
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (auth()->user()?->can('viewAny', MaintenanceInspection::class) ?? false)
            && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inspection_no')
            ->modifyQueryUsing(fn (Builder $query) => $query->visibleTo(auth()->user()))
            ->defaultSort('closed_at', 'desc')
            ->columns([
                TextColumn::make('inspection_no')
                    ->label('Inspection #')
                    ->searchable(),
                TextColumn::make('department_name')
                    ->label('Department')
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false),
                TextColumn::make('checklist_name')
                    ->label('Checklist')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        MaintenanceInspection::STATUS_COMPLETED => 'success',
                        MaintenanceInspection::STATUS_CANCELLED => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('requested_at')
                    ->label('Requested On')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Closed On')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (MaintenanceInspection $record): string => MaintenanceInspectionResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
