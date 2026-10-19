<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\AimsInspections\AimsInspectionResource;
use App\Models\AimsInspection;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The closed AIMS inspections of an asset, matched to it by PRC code. Only shown to the AIMS department and super admins.
 */
class AimsInspectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'aimsInspections';

    protected static ?string $title = 'AIMS Inspections';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->aimsInspections()->visibleTo(auth()->user())->count();
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return AimsInspection::isVisibleTo(auth()->user())
            && (auth()->user()?->can('viewAny', AimsInspection::class) ?? false)
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
                TextColumn::make('lessee')
                    ->placeholder('—'),
                TextColumn::make('jobsite')
                    ->placeholder('—'),
                TextColumn::make('operator')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AimsInspection::STATUS_COMPLETED => 'success',
                        AimsInspection::STATUS_CANCELLED => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('inspected_at')
                    ->label('Inspected On')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Closed On')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (AimsInspection $record): string => AimsInspectionResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
