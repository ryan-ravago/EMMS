<?php

namespace App\Filament\Resources\EquipmentTaskChecklistTemplates;

use App\Filament\Resources\EquipmentTaskChecklistTemplates\Pages\CreateEquipmentTaskChecklistTemplate;
use App\Filament\Resources\EquipmentTaskChecklistTemplates\Pages\EditEquipmentTaskChecklistTemplate;
use App\Filament\Resources\EquipmentTaskChecklistTemplates\Pages\ListEquipmentTaskChecklistTemplates;
use App\Filament\Resources\EquipmentTaskChecklistTemplates\Schemas\EquipmentTaskChecklistTemplateForm;
use App\Filament\Resources\EquipmentTaskChecklistTemplates\Tables\EquipmentTaskChecklistTemplatesTable;
use App\Models\EquipmentTaskChecklistTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EquipmentTaskChecklistTemplateResource extends Resource
{
    protected static ?string $model = EquipmentTaskChecklistTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return EquipmentTaskChecklistTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentTaskChecklistTemplatesTable::configure($table);
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
            'index' => ListEquipmentTaskChecklistTemplates::route('/'),
            'create' => CreateEquipmentTaskChecklistTemplate::route('/create'),
            'edit' => EditEquipmentTaskChecklistTemplate::route('/{record}/edit'),
        ];
    }
}
