<?php

namespace App\Filament\Support;

use App\Models\AppUser;
use App\Support\RoleDeletion;
use App\Support\UserRelatedRecords;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Delete actions for roles. Users whose only role is being deleted are deleted with it, so the
 * delete is refused, with a notification saying why, when the role is protected, when it would
 * delete your own account, or when any of those users still has related records.
 */
class RoleDeleteActions
{
    public static function delete(): DeleteAction
    {
        return DeleteAction::make()
            ->modalDescription(fn (Role $record): string => self::confirmation(collect([$record])))
            ->before(fn (DeleteAction $action, Role $record) => self::guard($action, collect([$record])))
            ->using(function (DeleteAction $action, Role $record): bool {
                $deletedUsers = RoleDeletion::delete(collect([$record]));

                $action->successNotificationTitle(self::successTitle(1, $deletedUsers));

                return true;
            });
    }

    public static function bulkDelete(): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->modalDescription(fn (Collection $records): string => self::confirmation($records))
            ->before(fn (DeleteBulkAction $action, Collection $records) => self::guard($action, $records))
            ->using(function (DeleteBulkAction $action, Collection $records): void {
                $deletedUsers = RoleDeletion::delete($records);

                $action->successNotificationTitle(self::successTitle($records->count(), $deletedUsers));
            });
    }

    /**
     * @param  Collection<int, Role>  $roles
     */
    private static function guard(Action $action, Collection $roles): void
    {
        $single = $roles->count() === 1;
        $title = $single ? 'The '.self::label($roles->first())." role can't be deleted" : 'No roles were deleted';

        $protected = RoleDeletion::protectedRoles($roles);

        if ($protected->isNotEmpty()) {
            $names = Arr::join($protected->map(fn (Role $role): string => self::label($role))->all(), ', ', ' and ');

            self::refuse($action, $title, "{$names} ".($protected->count() === 1 ? 'is a system role' : 'are system roles').' and can never be deleted.');
        }

        $users = RoleDeletion::usersLeftWithoutRole($roles);

        if ($users->contains(fn (AppUser $user): bool => $user->is(Auth::user()))) {
            self::refuse($action, $title, ($single ? 'This is your only role' : 'These are your only roles').', so deleting '.($single ? 'it' : 'them').' would also delete your own account.');
        }

        $related = UserRelatedRecords::countsFor($users->modelKeys());

        if ($related === []) {
            return;
        }

        $blocked = $users->filter(fn (AppUser $user): bool => isset($related[$user->getKey()]));

        self::refuse(
            $action,
            $title,
            'Deleting '.($single ? 'it' : 'them').' would also delete the '.self::users($users->count()).' '.self::onlyRole($single).', but '
                .UserDeleteActions::describeUsers($blocked, $related).' still '.($blocked->count() === 1 ? 'has' : 'have').' related records. '
                .'Give '.($blocked->count() === 1 ? 'that user' : 'those users').' another role first, then try again.',
        );
    }

    private static function refuse(Action $action, string $title, string $body): never
    {
        Notification::make()
            ->title($title)
            ->body($body)
            ->danger()
            ->persistent()
            ->send();

        $action->cancel();
    }

    /**
     * @param  Collection<int, Role>  $roles
     */
    private static function confirmation(Collection $roles): string
    {
        $count = RoleDeletion::usersLeftWithoutRole($roles)->count();

        if ($count === 0) {
            return 'No user accounts will be deleted. Users who also have another role keep their account.';
        }

        return 'This also deletes the '.self::users($count).' '.self::onlyRole($roles->count() === 1).'. Users who also have another role keep their account.';
    }

    private static function successTitle(int $roles, int $deletedUsers): string
    {
        $title = $roles === 1 ? 'Role deleted' : "{$roles} roles deleted";

        if ($deletedUsers === 0) {
            return $title;
        }

        return "{$title}, along with ".self::users($deletedUsers).' who had no other role';
    }

    private static function users(int $count): string
    {
        return $count.' '.Str::plural('user', $count);
    }

    private static function onlyRole(bool $single): string
    {
        return $single ? 'whose only role it is' : 'who have no role outside these';
    }

    private static function label(Role $role): string
    {
        return $role->display_name ?: Str::headline($role->name);
    }
}
