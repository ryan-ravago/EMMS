<?php

namespace App\Filament\Resources\Roles\RelationManagers;

use App\Filament\Resources\AppUsers\AppUserResource;
use App\Filament\Resources\Roles\Pages\ViewRole;
use App\Models\AppUser;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use STS\FilamentImpersonate\Actions\Impersonate;

/**
 * Read-only list of the users who have the role, shown when viewing a role. Super admins can
 * impersonate from here; the Impersonate action hides itself for everyone else.
 */
class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Users';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewRole::class && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->users()->count();
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['roles', 'department']))
            ->recordTitleAttribute('user_fname')
            ->description('Users with no other role are deleted together with this role.')
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->state(fn (AppUser $record): string => $record->full_name)
                    ->searchable(['user_fname', 'user_lname'])
                    ->sortable(['user_fname', 'user_lname']),
                TextColumn::make('user_email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('department.dep_name')
                    ->label('Department'),
                TextColumn::make('other_roles')
                    ->label('Other roles')
                    ->badge()
                    ->state(fn (AppUser $record): array => $record->roles
                        ->reject(fn (Role $role): bool => $role->is($this->getOwnerRecord()))
                        ->map(fn (Role $role): string => $role->display_name ?: Str::headline($role->name))
                        ->values()
                        ->all())
                    ->placeholder('None'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('user_fname')
            ->recordActions([
                Impersonate::make()
                    ->iconButton()
                    ->tooltip('Impersonate'),
            ])
            ->recordUrl(fn (AppUser $record): ?string => Auth::user()?->can('view', $record)
                ? AppUserResource::getUrl('view', ['record' => $record])
                : null);
    }
}
