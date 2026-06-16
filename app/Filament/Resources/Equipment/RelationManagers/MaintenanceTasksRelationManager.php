<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Models\MaintenanceTask;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MaintenanceTasksRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceTasks';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $query = $ownerRecord->maintenanceTasks();
        if (! Auth::user()->hasRole('super_admin')) {
            $query->where('mt_dep_id', Auth::user()->user_dep_id);
        }

        return (string) $query->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mt_task_log')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('mt_task_log')
            ->columns([
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn (MaintenanceTask $record): string => $record->status->status_color)
                    ->icon(fn (MaintenanceTask $record): string => $record->status->status_icon)
                    ->sortable(),

                TextColumn::make('mt_task_log')
                    ->label('Task')
                    ->searchable(),

                TextColumn::make('mt_due_dt')
                    ->label('Due Date')
                    ->dateTime('M j, Y h:i A')
                    ->sortable(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (! Auth::user()->hasRole('super_admin')) {
                    $query->where('mt_dep_id', Auth::user()->user_dep_id);
                }
            })
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordUrl(fn (Model $record): string => MaintenanceTaskResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Model $record): string => MaintenanceTaskResource::getUrl('view', ['record' => $record])),
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
