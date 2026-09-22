<?php

namespace App\Filament\Resources\EquipmentTypes\RelationManagers;

use App\Models\EquipmentModel;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    protected static ?string $inverseRelationship = 'type';

    protected static ?string $title = 'Models';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->models()->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('eqmm_name')
                    ->required()
                    ->maxLength(255),
            ]);
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
                TextColumn::make('brand.eqmb_name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                Action::make('associate_model_to_type')
                    ->authorize(fn(): bool => Auth::user()->hasPermissionTo('Update:EquipmentResource'))
                    ->label('Associate')
                    ->icon(Heroicon::Link)
                    ->modalHeading('Associate Model')
                    ->modalSubmitActionLabel('Associate')
                    ->schema([
                        Select::make('recordIds')
                            ->label('Model')
                            ->multiple()
                            ->preload()
                            ->options(
                                // Only show models that currently DO NOT have a type assigned
                                fn(): array => EquipmentModel::query()
                                    ->whereNull('eqmm_eqmt_id')
                                    ->pluck('eqmm_name', 'eqmm_id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $type = $this->getOwnerRecord();
                        $selectedIds = (array) ($data['recordIds'] ?? []);

                        if (empty($selectedIds)) {
                            return;
                        }

                        // Loop + save() (not a mass update) so EquipmentModel::save()
                        // fires and cascades eqmm_eqmt_id down to every equipment_unit
                        // that references each model.
                        EquipmentModel::query()
                            ->whereIn('eqmm_id', $selectedIds)
                            ->whereNull('eqmm_eqmt_id')
                            ->get()
                            ->each(function (EquipmentModel $model) use ($type): void {
                                $model->eqmm_eqmt_id = $type->getKey();
                                $model->save();
                            });
                    })
                    ->successNotificationTitle('Model associated successfully'),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make()
                    ->action(function (EquipmentModel $record): void {
                        $record->type()->dissociate();
                        $record->save(); // Triggers EquipmentModel::save() -> cascades to Equipment::save()
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->action(function (Collection $records): void {
                            $records->each(function (EquipmentModel $model): void {
                                $model->type()->dissociate();
                                $model->save(); // Triggers EquipmentModel::save() -> cascades to Equipment::save()
                            });
                        }),
                ]),
            ]);
    }
}
