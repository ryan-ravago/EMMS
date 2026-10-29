<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\WorkOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

/**
 * Manager: the department's work orders that are waiting for the manager, oldest first - new requests to
 * approve and completion requests to confirm.
 */
class ApprovalsQueueWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|HtmlString|null
    {
        return new HtmlString('<a href="'.e(WorkOrderResource::getUrl()).'" class="hover:underline transition">Waiting for Your Approval</a>');
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('manager') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => WorkOrder::query()
                ->with(['equipment', 'createdBy', 'status'])
                ->where('wo_dep_id', Auth::user()?->user_dep_id)
                ->whereIn('wo_status_id', WorkOrder::AWAITING_MANAGER_STATUSES)
                ->orderBy('wo_created_dt')
                ->orderBy('wo_id'))
            ->columns([
                TextColumn::make('wo_no')
                    ->label('Work Order')
                    ->description(fn (WorkOrder $record): ?string => $record->wo_title),
                TextColumn::make('equipment.eqm_name')
                    ->label('Equipment')
                    ->placeholder('—'),
                TextColumn::make('createdBy.full_name')
                    ->label('Requested By')
                    ->placeholder('—'),
                TextColumn::make('status.status_title')
                    ->label('Waiting For')
                    ->badge()
                    ->color(fn (WorkOrder $record): string => $record->status?->status_color ?? 'gray'),
                TextColumn::make('wo_created_dt')
                    ->label('Created')
                    ->since()
                    ->description(fn (WorkOrder $record): ?string => $record->wo_created_dt?->format('M d, Y h:i A')),
            ])
            ->emptyStateHeading('Nothing is waiting for you')
            ->emptyStateDescription('New requests and completion requests of your department will appear here.')
            ->recordUrl(fn (WorkOrder $record): string => WorkOrderResource::getUrl('view', ['record' => $record]))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->poll('120s');
    }
}
