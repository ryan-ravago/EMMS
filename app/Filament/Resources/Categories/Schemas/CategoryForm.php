<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\EquipmentCategory;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('eqmc_name')
                    ->label('Tag Name')
                    ->required(),
                // ->unique(table: 'equipment_categories', column: 'eqmc_name', ignoreRecord: true),
                SelectTree::make('eqmc_parent_id')
                    ->label('Parent')
                    ->relationship(
                        'parent',
                        'eqmc_name',
                        'eqmc_parent_id',
                        // Exclude itself and its own descendants from the query, since hiddenOptions() doesn't filter root-level nodes.
                        modifyQueryUsing: fn (Builder $query, ?EquipmentCategory $record) => $record
                            ? $query->whereNotIn('eqmc_id', [$record->getKey(), ...$record->getDescendantIds()])
                            : $query,
                        modifyChildQueryUsing: fn (Builder $query, ?EquipmentCategory $record) => $record
                            ? $query->whereNotIn('eqmc_id', [$record->getKey(), ...$record->getDescendantIds()])
                            : $query,
                    )
                    ->nullable()
                    ->enableBranchNode(),
            ]);
    }
}
