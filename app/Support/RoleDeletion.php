<?php

namespace App\Support;

use App\Models\AppUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Deleting a role also deletes the users it would leave without any role. Users who hold
 * another role keep their account and only lose the deleted one.
 */
class RoleDeletion
{
    /** Roles that can never be deleted. */
    public const PROTECTED_ROLES = ['super_admin'];

    /**
     * @param  Collection<int, Role>  $roles
     * @return EloquentCollection<int, AppUser>
     */
    public static function usersLeftWithoutRole(Collection $roles): EloquentCollection
    {
        $roleIds = $roles->map(fn (Role $role): mixed => $role->getKey())->all();

        if ($roleIds === []) {
            return new EloquentCollection;
        }

        return AppUser::query()
            ->whereHas('roles', fn (Builder $query): Builder => $query->whereKey($roleIds))
            ->whereDoesntHave('roles', fn (Builder $query): Builder => $query->whereKeyNot($roleIds))
            ->get();
    }

    /**
     * @param  Collection<int, Role>  $roles
     * @return Collection<int, Role>
     */
    public static function protectedRoles(Collection $roles): Collection
    {
        return $roles->filter(fn (Role $role): bool => in_array($role->name, self::PROTECTED_ROLES, true))->values();
    }

    /**
     * Deletes the roles and the users they leave without a role. Callers check
     * protectedRoles() and UserRelatedRecords first.
     *
     * @param  Collection<int, Role>  $roles
     * @return int number of users deleted
     */
    public static function delete(Collection $roles): int
    {
        return DB::transaction(function () use ($roles): int {
            $users = self::usersLeftWithoutRole($roles);

            $users->each(fn (AppUser $user): ?bool => $user->delete());
            $roles->each(fn (Role $role): ?bool => $role->delete());

            return $users->count();
        });
    }
}
