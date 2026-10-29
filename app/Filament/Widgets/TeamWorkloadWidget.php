<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Technicians\TechnicianResource;
use App\Models\AppUser;
use App\Models\WorkOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

/**
 * Manager: how many open work orders each technician of the department is carrying.
 */
class TeamWorkloadWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|HtmlString|null
    {
        return new HtmlString('<a href="'.e(TechnicianResource::getUrl()).'" class="hover:underline transition">Team Workload</a>');
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('manager') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => AppUser::query()
                ->role('technician')
                ->where('user_dep_id', Auth::user()?->user_dep_id)
                ->withCount([
                    'workOrders as open_work_orders_count' => fn ($query) => $query->whereIn('wo_status_id', WorkOrder::OPEN_STATUSES),
                    'workOrders as awaiting_review_count' => fn ($query) => $query->where('wo_status_id', 'pca'),
                ])
                ->orderByDesc('open_work_orders_count')
                ->orderBy('user_fname')
                ->limit(10))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Technician')
                    ->state(fn (AppUser $record): string => $record->full_name),
                TextColumn::make('open_work_orders_count')
                    ->label('Open Work Orders')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray'),
                TextColumn::make('awaiting_review_count')
                    ->label('Waiting for Your Review')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),
            ])
            ->emptyStateHeading('No technicians')
            ->emptyStateDescription('There are no technicians in your department yet.')
            ->paginated(false)
            ->poll('60s');
    }
}
