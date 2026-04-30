<?php

namespace App\Filament\Resources\AppUsers\Pages;

use App\Filament\Resources\AppUsers\AppUserResource;
use App\Models\AppUser;
use App\Models\Department;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAppUsers extends ListRecords
{
    protected static string $resource = AppUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All'),
        ];

        $departments = Department::all();

        foreach ($departments as $department) {
            $tabs[$department->dep_code] = Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_dep_id', $department->dep_id))
                ->badge(
                    // This queries the count specifically for this tab
                    AppUser::where('user_dep_id', $department->dep_id)->count()
                )
                ->badgeColor('warning');;
        }

        return $tabs;
    }
}
