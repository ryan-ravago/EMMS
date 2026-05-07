<?php

namespace App\Filament\Resources\ChecklistTemplates;

use App\Filament\Resources\ChecklistTemplates\Pages\CreateChecklistTemplate;
use App\Filament\Resources\ChecklistTemplates\Pages\EditChecklistTemplate;
use App\Filament\Resources\ChecklistTemplates\Pages\ListChecklistTemplates;
use App\Filament\Resources\ChecklistTemplates\Pages\ViewChecklistTemplate;
use App\Filament\Resources\ChecklistTemplates\RelationManagers\EquipmentUnitsRelationManager;
use App\Filament\Resources\ChecklistTemplates\Schemas\ChecklistTemplateForm;
use App\Filament\Resources\ChecklistTemplates\Schemas\ChecklistTemplateInfolist;
use App\Filament\Resources\ChecklistTemplates\Tables\ChecklistTemplatesTable;
use App\Models\ChecklistTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChecklistTemplateResource extends Resource
{
    protected static ?string $model = ChecklistTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'clt_name';

    public static function form(Schema $schema): Schema
    {
        return ChecklistTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChecklistTemplateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChecklistTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EquipmentUnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChecklistTemplates::route('/'),
            'create' => CreateChecklistTemplate::route('/create'),
            'view' => ViewChecklistTemplate::route('/{record}'),
            'edit' => EditChecklistTemplate::route('/{record}/edit'),
        ];
    }
}
