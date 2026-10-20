<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\ManagesInspectionSync;
use App\Services\Inspections\InspectionSyncRunner;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use UnitEnum;

/**
 * Syncs closed maintenance inspections from AppSheet. Super admins only.
 */
class InspectionSyncManager extends Page implements HasTable
{
    use InteractsWithForms, InteractsWithTable, ManagesInspectionSync {
        ManagesInspectionSync::form insteadof InteractsWithForms;
        ManagesInspectionSync::table insteadof InteractsWithTable;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Inspection Sync Manager';

    protected static string|UnitEnum|null $navigationGroup = 'Super Admin';

    protected static ?int $navigationSort = 98;

    protected string $view = 'filament.pages.sap-sync-manager';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    protected static function syncKind(): string
    {
        return InspectionSyncRunner::MAINTENANCE;
    }

    protected static function syncSubject(): string
    {
        return 'maintenance inspections';
    }
}
