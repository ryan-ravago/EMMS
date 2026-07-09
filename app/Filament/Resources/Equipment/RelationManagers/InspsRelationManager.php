<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\Equipment\Resources\Insps\InspsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InspsRelationManager extends RelationManager
{
    protected static string $relationship = 'insps';

    protected static ?string $relatedResource = InspsResource::class;

    protected static ?string $title = 'Inspections';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $query = $ownerRecord->insps();
        $user = auth()->user();

        if ($user === null) {
            return '0';
        }

        if ($user->hasRole('super_admin')) {
            return (string) $query->count();
        } else if ($user->hasRole('manager')) {
            $query->where('insp_dep_id', $user->user_dep_id);
        }

        return (string) $query->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
