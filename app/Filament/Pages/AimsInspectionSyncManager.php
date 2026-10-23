<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\ManagesInspectionSync;
use App\Models\AimsInspection;
use App\Services\Inspections\InspectionSyncRunner;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use UnitEnum;

/**
 * Syncs closed AIMS inspections from AppSheet. For Asset Admins, Asset Managers and super admins.
 */
class AimsInspectionSyncManager extends Page implements HasTable
{
    use InteractsWithForms, InteractsWithTable, ManagesInspectionSync {
        ManagesInspectionSync::form insteadof InteractsWithForms;
        ManagesInspectionSync::table insteadof InteractsWithTable;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'AIMS Inspection Sync Manager';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.sync-manager';

    public static function canAccess(): bool
    {
        return AimsInspection::isVisibleTo(auth()->user());
    }

    /** Under "Super Admin" for super admins (next to the other sync managers); loose in the menu for everyone else. */
    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return auth()->user()?->hasRole('super_admin') ? 'Super Admin' : null;
    }

    public static function getNavigationSort(): ?int
    {
        return auth()->user()?->hasRole('super_admin') ? 97 : static::$navigationSort;
    }

    protected static function syncKind(): string
    {
        return InspectionSyncRunner::AIMS;
    }

    protected static function syncSubject(): string
    {
        return 'AIMS inspections';
    }
}
