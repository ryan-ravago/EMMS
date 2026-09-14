<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Models\Location;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Location')
                    ->required(),
                SelectTree::make('parent_id')
                    ->label('Parent')
                    ->relationship(
                        'parent',
                        'name',
                        'parent_id',
                        // Exclude itself and its own descendants from the query, since hiddenOptions() doesn't filter root-level nodes.
                        modifyQueryUsing: fn (Builder $query, ?Location $record) => $record
                            ? $query->whereNotIn('id', [$record->getKey(), ...$record->getDescendantIds()])
                            : $query,
                        modifyChildQueryUsing: fn (Builder $query, ?Location $record) => $record
                            ? $query->whereNotIn('id', [$record->getKey(), ...$record->getDescendantIds()])
                            : $query,
                    )
                    ->nullable()
                    ->enableBranchNode(),
            ]);
    }
}
