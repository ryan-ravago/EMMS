<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Models\Department;
use App\Models\MaintenanceTask;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class MaintenanceScheduleWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|HtmlString|null
    {
        return new HtmlString('<a href="'.MaintenanceTaskResource::getUrl().'" class="hover:underline transition">Upcoming Maintenance Tasks</a>');
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        if ($user->hasRole('asset_admin')) {
            return false;
        }
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->department?->dep_code === 'PREV';
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $preventiveDepId = Department::where('dep_code', 'PREV')->value('dep_id');

        return $table
            ->query(
                MaintenanceTask::query()
                    ->where('mt_dep_id', $preventiveDepId)
                    ->where('mt_status_id', 'pnd')
                    ->where('mt_due_dt', '>=', now())
                    ->orderBy('mt_due_dt', 'asc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('equipmentUnit.eqm_name')
                    ->label('Equipment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('task.task_name')
                    ->label('Task'),
                Tables\Columns\TextColumn::make('mt_due_dt')
                    ->label('Due Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn (MaintenanceTask $record): string => $record->status?->status_color ?? 'gray'),
            ]);
    }
}
