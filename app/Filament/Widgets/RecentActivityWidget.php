<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ActivityLogPage;
use App\Models\Activity;
use App\Support\Activity\ActivityLabels;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

/**
 * Super admin: the latest entries of the activity log, who did what and when.
 */
class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|HtmlString|null
    {
        return new HtmlString('<a href="'.e(ActivityLogPage::getUrl()).'" class="hover:underline transition">Recent Activity</a>');
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Activity::query()->with('causer')->latest()->orderByDesc('id'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->description(fn (Activity $record): string => $record->created_at->format('M d, Y h:i A')),
                TextColumn::make('causer_id')
                    ->label('User')
                    ->state(fn (Activity $record): string => ActivityLabels::userName($record)),
                TextColumn::make('description')
                    ->label('What happened')
                    ->wrap()
                    ->limit(120),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            // The activity log is the biggest table: Previous / Next paging avoids counting all of its rows.
            ->paginationMode(PaginationMode::Simple)
            ->poll('120s');
    }
}
