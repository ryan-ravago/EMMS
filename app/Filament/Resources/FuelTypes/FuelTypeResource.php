<?php

namespace App\Filament\Resources\FuelTypes;

use App\Filament\Resources\FuelTypes\Pages\CreateFuelType;
use App\Filament\Resources\FuelTypes\Pages\EditFuelType;
use App\Filament\Resources\FuelTypes\Pages\ListFuelTypes;
use App\Filament\Resources\FuelTypes\Pages\ViewFuelType;
use App\Filament\Resources\FuelTypes\Schemas\FuelTypeForm;
use App\Filament\Resources\FuelTypes\Schemas\FuelTypeInfolist;
use App\Filament\Resources\FuelTypes\Tables\FuelTypesTable;
use App\Models\FuelType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FuelTypeResource extends Resource
{
    protected static ?string $model = FuelType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'fuel_name';
    protected static ?string $navigationLabel = 'Fuel Types';
    protected static ?string $modelLabel = 'Fuel Type';
    protected static ?string $pluralModelLabel = 'Fuel Types';

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        // 1. If Super Admin, always keep it at the top level (no group)
        if ($user->hasRole('super_admin')) {
            return null;
        }

        // 2. Count roles. If they only have 1 role (or 0), don't show a group folder.
        // Assuming you are using Spatie Permissions or a 'roles' relationship
        if ($user->roles()->count() < 2) {
            return null;
        }

        // 3. If they have 2+ roles, assign the group based on priority:
        if ($user->hasRole('manager')) {
            return 'Manager';
        }

        if ($user->hasRole('custodian')) {
            return 'Custodian';
        }

        return null;
    }

    public static function form(Schema $schema): Schema
    {
        return FuelTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FuelTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FuelTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFuelTypes::route('/'),
            'create' => CreateFuelType::route('/create'),
            'view' => ViewFuelType::route('/{record}'),
            'edit' => EditFuelType::route('/{record}/edit'),
        ];
    }
}
