<?php

namespace App\Filament\Resources\Insps;

use App\Filament\Resources\Insps\Pages\CreateInsp;
use App\Filament\Resources\Insps\Pages\EditInsp;
use App\Filament\Resources\Insps\Pages\ListInsps;
use App\Filament\Resources\Insps\Pages\ViewInsp;
use App\Filament\Resources\Insps\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Insps\Schemas\InspForm;
use App\Filament\Resources\Insps\Schemas\InspInfolist;
use App\Filament\Resources\Insps\Tables\InspsTable;
use App\Models\Insp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class InspResource extends Resource
{
    protected static ?string $model = Insp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Inspections';

    protected static ?string $recordTitleAttribute = 'insp_no';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $authUser = Auth::user();

        if ($authUser === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($authUser->hasRole('super_admin')) {
            return $query;
        }

        if ($authUser->user_dep_id === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('insp_dep_id', $authUser->user_dep_id);
    }

    public static function form(Schema $schema): Schema
    {
        return InspForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InspInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InspsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'items' => ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInsps::route('/'),
            'create' => CreateInsp::route('/create'),
            'view' => ViewInsp::route('/{record}'),
            'edit' => EditInsp::route('/{record}/edit'),
        ];
    }
}
