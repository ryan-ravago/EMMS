<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\EquipmentTypes\EquipmentTypeResource;
use App\Filament\Resources\Inspections\InspectionResource;
use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Filament\Resources\Models\ModelResource;
use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use App\Filament\Resources\Technicians\TechnicianResource;
use App\Filament\Resources\TechnicianWorkOrders\TechnicianWorkOrderResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\AppUser;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use App\Models\Inspection;
use App\Models\MaintenanceTask;
use App\Models\WorkOrder;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected static ?int $navigationSort = 1;

    protected ?string $heading = 'Dashboard Summary';

    protected ?string $description = 'Quick insights for your role and department.';

    protected function getStats(): array
    {
        if (! Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $preventiveDepId = Department::where('dep_code', 'PREV')->value('dep_id');

        if ($user->hasRole('asset_admin')) {
            return [
                Stat::make('Total Equipment', Equipment::count())
                    ->icon(Heroicon::CubeTransparent)
                    ->description('All registered assets')
                    ->descriptionIcon(Heroicon::InformationCircle)
                    ->color('primary')
                    ->url(EquipmentResource::getUrl()),
                Stat::make('Models', EquipmentModel::count())
                    ->icon(Heroicon::Squares2x2)
                    ->description('Configured equipment models')
                    ->color('info')
                    ->url(ModelResource::getUrl()),
                Stat::make('Equipment Types', EquipmentType::count())
                    ->icon(Heroicon::RectangleGroup)
                    ->description('Available equipment type records')
                    ->color('success')
                    ->url(EquipmentTypeResource::getUrl()),
                Stat::make('Categories', EquipmentCategory::count())
                    ->icon(Heroicon::Square3Stack3d)
                    ->description('Equipment category taxonomy')
                    ->color('warning')
                    ->url(CategoryResource::getUrl()),
            ];
        }

        if ($user->hasRole('super_admin')) {
            $overdueCount = MaintenanceTask::where('mt_dep_id', $preventiveDepId)->where('mt_due_dt', '<', now())->where('mt_status_id', '!=', 'cmp')->count();

            return [
                Stat::make('Total Equipment', Equipment::count())
                    ->icon(Heroicon::CubeTransparent)
                    ->description('All registered assets')
                    ->descriptionIcon(Heroicon::InformationCircle)
                    ->color('primary')
                    ->url(EquipmentResource::getUrl()),
                Stat::make('Total Inspections', Inspection::count())
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->description('Conducted inspections')
                    ->color('success')
                    ->url(InspectionResource::getUrl()),
                Stat::make('Overdue Maintenance', $overdueCount)
                    ->icon(Heroicon::ExclamationTriangle)
                    ->description($overdueCount > 0 ? 'Urgent attention required' : 'All tasks on track')
                    ->descriptionIcon($overdueCount > 0 ? Heroicon::ArrowTrendingUp : Heroicon::CheckBadge)
                    ->color($overdueCount > 0 ? 'danger' : 'success')
                    ->url(MaintenanceTaskResource::getUrl()),
                Stat::make('Total Work Orders', WorkOrder::count())
                    ->icon(Heroicon::DocumentCheck)
                    ->description('Total created')
                    ->color('info')
                    ->url(WorkOrderResource::getUrl()),
            ];
        }

        if ($user->hasRole('manager')) {
            $stats = [
                Stat::make('Department Inspections', Inspection::where('ins_dep_id', $user->user_dep_id)->count())
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->color('success')
                    ->url(InspectionResource::getUrl()),
            ];

            if ($user->user_dep_id == $preventiveDepId) {
                $overdueTasks = MaintenanceTask::where('mt_dep_id', $user->user_dep_id)->where('mt_due_dt', '<', now())->where('mt_status_id', '!=', 'cmp')->count();
                $stats[] = Stat::make('Overdue Tasks', $overdueTasks)
                    ->icon(Heroicon::ExclamationTriangle)
                    ->description($overdueTasks > 0 ? 'Action required immediately' : 'Department is clear')
                    ->descriptionIcon($overdueTasks > 0 ? Heroicon::ShieldExclamation : Heroicon::CheckCircle)
                    ->color($overdueTasks > 0 ? 'danger' : 'success')
                    ->url(MaintenanceTaskResource::getUrl());
            }

            // $awaiting = WorkOrder::where('wo_dep_id', $user->user_dep_id)->where('wo_status_id', 'pca')->count();
            // $stats[] = Stat::make('Awaiting Completion', $awaiting)
            //     ->icon(Heroicon::Clock)
            //     ->description('Pending approval')
            //     ->color($awaiting > 0 ? 'warning' : 'gray')
            //     ->url(WorkOrderResource::getUrl('index', ['tableFilters[wo_status_id][value]' => 'pca', 'tab' => 'pca']));

            $pendingApproval = WorkOrder::where('wo_dep_id', $user->user_dep_id)->where('wo_status_id', 'pndwor')->count();
            $stats[] = Stat::make('Pending Approval', $pendingApproval)
                ->icon(Heroicon::DocumentPlus)
                ->description('Requested by requestor')
                ->color($pendingApproval > 0 ? 'info' : 'gray')
                ->url(WorkOrderResource::getUrl('index', ['tableFilters[wo_status_id][value]' => 'pndwor', 'tab' => 'pndwor']));

            $stats[] = Stat::make('Technicians', AppUser::role('technician')->where('user_dep_id', $user->user_dep_id)->count())
                ->icon(Heroicon::Users)
                ->description('In your department')
                ->color('info')
                ->url(TechnicianResource::getUrl());

            return $stats;
        }

        if ($user->hasRole('technician')) {
            $stats = [
                Stat::make('Assigned Work Orders', $user->workOrders()->count())
                    ->icon(Heroicon::DocumentCheck)
                    ->description('Total active assignments')
                    ->color('primary')
                    ->url(TechnicianWorkOrderResource::getUrl()),
            ];

            if ($user->user_dep_id == $preventiveDepId) {
                $overdueAssigned = MaintenanceTask::where('mt_dep_id', $user->user_dep_id)
                    ->whereHas('workOrders', function ($query) use ($user) {
                        $query->whereHas('workers', function ($q) use ($user) {
                            $q->where('app_users.user_id', $user->user_id);
                        });
                    })
                    ->where('mt_due_dt', '<', now())
                    ->where('mt_status_id', '!=', 'cmp')
                    ->count();

                $stats[] = Stat::make('Overdue Assigned Tasks', $overdueAssigned)
                    ->icon(Heroicon::ExclamationTriangle)
                    ->description($overdueAssigned > 0 ? 'Your overdue tasks' : 'No overdue tasks')
                    ->descriptionIcon($overdueAssigned > 0 ? Heroicon::ChevronDoubleRight : Heroicon::HandThumbUp)
                    ->color($overdueAssigned > 0 ? 'danger' : 'success')
                    ->url(MaintenanceTaskResource::getUrl());
            }

            $requests = $user->workOrders()->where('wo_status_id', 'pca')->count();
            $stats[] = Stat::make('Completion Requests', $requests)
                ->icon(Heroicon::Clock)
                ->description('Pending manager review')
                ->color($requests > 0 ? 'warning' : 'gray')
                ->url(TechnicianWorkOrderResource::getUrl('index', ['tableFilters[wo_status_id][value]' => 'pca', 'tab' => 'pca']));

            $stats[] = Stat::make('Submitted Reports', $user->reportSubmissions()->count())
                ->icon(Heroicon::ClipboardDocument)
                ->description('Your performance history')
                ->color('success');

            return $stats;
        }

        if ($user->hasRole('requestor')) {
            $totalCreated = WorkOrder::where('wo_created_by', $user->user_id)->count();
            $pendingCount = WorkOrder::where('wo_created_by', $user->user_id)->where('wo_status_id', 'pndwor')->count();
            // "inprog" is "Approved" from requestor's perspective
            $approvedCount = WorkOrder::where('wo_created_by', $user->user_id)->where('wo_status_id', 'inprog')->count();
            $rejectedCount = WorkOrder::where('wo_created_by', $user->user_id)->where('wo_status_id', 'rej')->count();

            return [
                Stat::make('Total Work Orders', $totalCreated)
                    ->icon(Heroicon::DocumentCheck)
                    ->description('All created work orders')
                    ->color('primary')
                    ->url(RequestorWorkOrderResource::getUrl()),
                Stat::make('Pending', $pendingCount)
                    ->icon(Heroicon::Clock)
                    ->description('Awaiting manager review')
                    ->color($pendingCount > 0 ? 'warning' : 'gray')
                    ->url(RequestorWorkOrderResource::getUrl('index', ['tableFilters[wo_status_id][value]' => 'pndwor', 'tab' => 'pndwor'])),
                Stat::make('Approved', $approvedCount)
                    ->icon(Heroicon::CheckBadge)
                    ->description('Work orders approved')
                    ->color('success')
                    ->url(RequestorWorkOrderResource::getUrl('index', ['tableFilters[wo_status_id][value]' => 'inprog', 'tab' => 'approved'])),
                Stat::make('Rejected', $rejectedCount)
                    ->icon(Heroicon::XCircle)
                    ->description('Rejected work orders')
                    ->color('danger')
                    ->url(RequestorWorkOrderResource::getUrl('index', ['tableFilters[wo_status_id][value]' => 'rej', 'tab' => 'rej'])),
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
