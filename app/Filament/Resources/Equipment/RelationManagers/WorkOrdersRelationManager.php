<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\Equipment\Resources\WorkOrders\WorkOrderResource;
use App\Models\WorkOrder;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WorkOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'workOrders';

    protected static ?string $relatedResource = WorkOrderResource::class;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $query = $ownerRecord->workOrders();
        $user = auth()->user();

        if ($user === null) {
            return '0';
        }

        if ($user->hasRole('super_admin')) {
            return (string) $query->count();
        } else if ($user->hasRole('manager')) {
            $query->where('wo_dep_id', $user->user_dep_id);
        }

        return (string) $query->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->authorize(fn(): bool => (bool) $this->getOwnerRecord()->eqm_is_active
                        && (Auth::user()?->can('create', WorkOrder::class) ?? false)),
            ]);
    }
}
