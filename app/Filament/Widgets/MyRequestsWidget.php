<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use App\Models\WorkOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

/**
 * Requestor: the latest work orders they asked for, with where each one stands.
 */
class MyRequestsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|HtmlString|null
    {
        return new HtmlString('<a href="'.e(RequestorWorkOrderResource::getUrl()).'" class="hover:underline transition">My Latest Requests</a>');
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('requestor') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => WorkOrder::query()
                ->with(['equipment', 'status'])
                ->where('wo_created_by', Auth::id())
                ->latest('wo_created_dt')
                ->orderByDesc('wo_id'))
            ->columns([
                TextColumn::make('wo_no')
                    ->label('Work Order')
                    ->description(fn (WorkOrder $record): ?string => $record->wo_title),
                TextColumn::make('equipment.eqm_name')
                    ->label('Equipment')
                    ->placeholder('—'),
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn (WorkOrder $record): string => $record->status?->status_color ?? 'gray'),
                TextColumn::make('wo_created_dt')
                    ->label('Requested')
                    ->since()
                    ->description(fn (WorkOrder $record): ?string => $record->wo_created_dt?->format('M d, Y h:i A')),
            ])
            ->emptyStateHeading('No requests yet')
            ->emptyStateDescription('Work orders you create will appear here.')
            ->recordUrl(fn (WorkOrder $record): string => RequestorWorkOrderResource::getUrl('view', ['record' => $record]))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->poll('120s');
    }
}
