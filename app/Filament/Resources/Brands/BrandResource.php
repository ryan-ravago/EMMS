<?php

namespace App\Filament\Resources\Brands;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Filament\Resources\Brands\Pages\EditBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Filament\Resources\Brands\Pages\ViewBrand;
use App\Filament\Resources\Brands\Schemas\BrandForm;
use App\Filament\Resources\Brands\Schemas\BrandInfolist;
use App\Filament\Resources\Brands\Tables\BrandsTable;
use App\Models\EquipmentBrand;
use App\Models\OPRC;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = EquipmentBrand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'eqmb_name';
    protected static ?string $navigationLabel = 'Brands';
    protected static ?string $modelLabel = 'Brand';
    protected static ?string $pluralModelLabel = 'Brands';

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
        return BrandForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BrandInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrandsTable::configure($table);
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
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'view' => ViewBrand::route('/{record}'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}
