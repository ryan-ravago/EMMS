<?php

namespace App\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityLogPage extends Page implements HasTable
{
    use HasPageShield, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static string|UnitEnum|null $navigationGroup = 'Super Admin';

    protected static ?int $navigationSort = 97;

    protected string $view = 'filament.pages.activity-log';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->with(['causer', 'subject'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('causer.user_fname')
                    ->label('User')
                    ->formatStateUsing(fn ($record): string => $record->causer
                        ? "{$record->causer->user_fname} {$record->causer->user_lname}"
                        : 'System')
                    ->searchable(),

                TextColumn::make('subject_type')
                    ->label('Subject Type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created', 'create', 'approve', 'mac' => 'success',
                        'updated', 'upt', 'reqcom', 'mwo' => 'info',
                        'deleted', 'cancel', 'reject', 'drg' => 'danger',
                        'snz' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->poll('60s');
    }
}
