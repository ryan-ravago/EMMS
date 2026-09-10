<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\AssetType;
use App\Models\Equipment;
use App\Models\EquipmentBrand;
use App\Models\EquipmentModel;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EquipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'equipments';

    protected static ?string $inverseRelationship = 'categories';

    protected static ?string $title = 'Equipment Units';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->equipments()->count() ?: null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('eqm_name')
                ->label('Equipment Name')
                ->required()
                ->maxLength(255),
            ToggleButtons::make('asset_type_id')
                ->label('Asset Type')
                ->inline()
                ->options(AssetType::orderBy('name')->pluck('name', 'id'))
                ->colors([
                    1 => 'warning',
                    2 => 'success',
                ])
                ->required(),
            Select::make('eqm_eqmm_id')
                ->label('Model')
                ->options(EquipmentModel::orderBy('eqmm_name')->pluck('eqmm_name', 'eqmm_id'))
                ->searchable()
                ->preload()
                ->native(false),
            Select::make('eqm_brand_id')
                ->label('Brand')
                ->options(EquipmentBrand::orderBy('eqmb_name')->pluck('eqmb_name', 'eqmb_id'))
                ->searchable()
                ->preload()
                ->native(false),
            TextInput::make('eqm_plate_num')
                ->label('Plate #')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(
                fn(Equipment $record): string => "{$record->eqm_prc_code} - {$record->eqm_name}"
            )
            ->recordTitleAttribute('eqm_name')
            ->columns([
                TextColumn::make('eqm_prc_code')
                    ->label('Equipment Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipmentModel.eqmm_name')
                    ->label('Model')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('brand.eqmb_name')
                    ->label('Brand')
                    ->sortable()
                    ->placeholder('—'),
                IconColumn::make('eqm_is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('eqm_is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordUrl(
                fn(Equipment $record): string => EquipmentResource::getUrl('view', ['record' => $record]),
            )
            ->headerActions([
                CreateAction::make()
                    ->label('New Equipment')
                    ->modalHeading('Create Equipment Unit'),
                AttachAction::make()
                    ->label('Add Existing')
                    ->modalHeading('Attach Equipment to Category')
                    ->modalSubmitActionLabel('Attach')
                    ->preloadRecordSelect()
                    ->multiple() // Enables multi-select in the modal
                    ->recordSelectSearchColumns(['eqm_name', 'eqm_prc_code'])
                    ->before(function (AttachAction $action, array $data) {
                        $equipmentIds = (array) ($data['recordId'] ?? []);
                        $category = $this->getOwnerRecord();

                        if (empty($equipmentIds)) {
                            return;
                        }

                        // 1. Fetch submitted equipment items
                        $equipments = Equipment::whereIn('eqm_id', $equipmentIds)->get();

                        if ($equipments->count() !== count($equipmentIds)) {
                            throw ValidationException::withMessages([
                                'recordId' => 'One or more selected equipment records do not exist.',
                            ]);
                        }

                        // 2. Business Logic: Check active status across all items
                        $inactive = $equipments->filter(fn(Equipment $item) => ! $item->eqm_is_active);

                        if ($inactive->isNotEmpty()) {
                            throw ValidationException::withMessages([
                                'recordId' => 'Inactive equipment cannot be assigned (' . $inactive->pluck('eqm_name')->implode(', ') . ').',
                            ]);
                        }

                        // 3. Prevent duplicate attachments
                        $alreadyAttachedIds = $category->equipments()
                            ->whereIn('equipment_units.eqm_id', $equipmentIds)
                            ->pluck('equipment_units.eqm_id')
                            ->toArray();

                        if (! empty($alreadyAttachedIds)) {
                            $duplicates = $equipments->whereIn('eqm_id', $alreadyAttachedIds)->pluck('eqm_name')->implode(', ');

                            throw ValidationException::withMessages([
                                'recordId' => "The following equipment items are already attached: {$duplicates}.",
                            ]);
                        }
                    })
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
