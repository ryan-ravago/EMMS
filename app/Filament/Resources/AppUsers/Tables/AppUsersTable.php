<?php

namespace App\Filament\Resources\AppUsers\Tables;

use App\Filament\Resources\AppUsers\AppUserResource;
use App\Filament\Support\UserDeleteActions;
use App\Models\AppUser;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class AppUsersTable
{
    private static function isSuperAdmin(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_fname')
                    ->label('First Name')
                    ->searchable(),
                TextColumn::make('user_mname')
                    ->label('Middle Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_lname')
                    ->label('Last Name')
                    ->searchable(),
                TextColumn::make('user_email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('user_contact_no')
                    ->label('Contact')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('department.dep_name')
                    ->sortable(),
                TextColumn::make('roles.display_name')
                    ->label('Roles')
                    ->badge()
                    // ->color('warning')
                    ->separator(',')
                    ->searchable()
                    ->wrap(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                // Super admins only: who is online and when they were last active (see TrackUserActivity).
                TextColumn::make('presence')
                    ->label('Status')
                    ->badge()
                    ->state(fn (AppUser $record): string => $record->isOnline() ? 'Online' : 'Offline')
                    ->color(fn (string $state): string => $state === 'Online' ? 'success' : 'gray')
                    ->icon(fn (string $state): string => $state === 'Online' ? 'heroicon-o-signal' : 'heroicon-o-signal-slash')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('last_seen_at', $direction))
                    ->visible(fn (): bool => self::isSuperAdmin()),
                TextColumn::make('last_active_at')
                    ->label('Last active')
                    ->since()
                    ->dateTimeTooltip('M d, Y h:i A')
                    ->placeholder('Never')
                    ->sortable()
                    ->visible(fn (): bool => self::isSuperAdmin()),
            ])
            // The status of everyone is checked again every minute.
            ->poll('60s')
            ->recordUrl(
                fn (Model $record): string => AppUserResource::getUrl('view', ['record' => $record]),
            )
            ->filters([
                SelectFilter::make('role')
                    ->relationship('roles', 'display_name')
                    ->searchable()
                    ->preload(),
                Filter::make('online')
                    ->label('Online now')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->online())
                    ->visible(fn (): bool => self::isSuperAdmin()),
            ])
            // ->groups([
            //     Group::make('department.dep_name')
            //         ->label('Department')
            // ])
            // ->defaultGroup('department.dep_name')
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit'),
                Impersonate::make()
                    ->iconButton()
                    ->tooltip('Impersonate'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    UserDeleteActions::bulkDelete()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
