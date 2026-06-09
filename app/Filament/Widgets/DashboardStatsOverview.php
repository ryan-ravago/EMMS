<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\MaintenanceTask;
use App\Models\WorkOrder;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Dashboard Summary';

    protected ?string $description = 'Quick insights for your role and department.';

    protected function getStats(): array
    {
        if (! Auth::check()) {
            return [];
        }

        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return [
                Stat::make('Total Equipment', Equipment::count())
                    ->icon(Heroicon::CubeTransparent)
                    ->color('primary'),
                Stat::make('Total Inspections', Inspection::count())
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->color('success'),
                Stat::make('Total Maintenance Tasks', MaintenanceTask::count())
                    ->icon(Heroicon::WrenchScrewdriver)
                    ->color('warning'),
                Stat::make('Total Work Orders', WorkOrder::count())
                    ->icon(Heroicon::DocumentCheck)
                    ->color('danger'),
            ];
        }

        if ($user->hasRole('manager')) {
            return [
                Stat::make('Department Inspections', Inspection::where('ins_dep_id', $user->user_dep_id)->count())
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->color('success'),
                Stat::make('Department Maintenance Tasks', MaintenanceTask::where('mt_dep_id', $user->user_dep_id)->count())
                    ->icon(Heroicon::WrenchScrewdriver)
                    ->color('warning'),
                Stat::make('Awaiting Completion', WorkOrder::where('wo_dep_id', $user->user_dep_id)->where('wo_status_id', 'pca')->count())
                    ->icon(Heroicon::Clock)
                    ->color('danger'),
                Stat::make('Technicians in Department', AppUser::role('technician')->where('user_dep_id', $user->user_dep_id)->count())
                    ->icon(Heroicon::Users)
                    ->color('info'),
            ];
        }

        if ($user->hasRole('technician')) {
            return [
                Stat::make('Assigned Work Orders', $user->workOrders()->count())
                    ->icon(Heroicon::DocumentCheck)
                    ->color('primary'),
                Stat::make('In Progress', $user->workOrders()->where('wo_status_id', 'inprog')->count())
                    ->icon(Heroicon::ArrowPath)
                    ->color('warning'),
                Stat::make('Completion Requests', $user->workOrders()->where('wo_status_id', 'pca')->count())
                    ->icon(Heroicon::Clock)
                    ->color('danger'),
                Stat::make('Submitted Reports', $user->reportSubmissions()->count())
                    ->icon(Heroicon::ClipboardDocument)
                    ->color('success'),
            ];
        }

        return [
            Stat::make('Active Equipment', Equipment::count())
                ->icon(Heroicon::CubeTransparent)
                ->color('primary'),
            Stat::make('Open Work Orders', WorkOrder::where('wo_status_id', 'inprog')->count())
                ->icon(Heroicon::DocumentCheck)
                ->color('warning'),
        ];
    }
}
