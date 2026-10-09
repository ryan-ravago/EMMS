<?php

namespace App\Filament\Support;

use App\Models\AppUser;
use App\Support\UserRelatedRecords;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Delete actions for users that refuse, with a notification saying why, when a user still
 * has related records. Used by every resource that deletes app users.
 */
class UserDeleteActions
{
    /** Users named in a notification before the rest are summarised as "and N more". */
    private const MAX_LISTED_USERS = 5;

    public static function delete(): DeleteAction
    {
        return DeleteAction::make()
            ->before(function (DeleteAction $action, AppUser $record): void {
                $related = UserRelatedRecords::for($record);

                if ($related === []) {
                    return;
                }

                Notification::make()
                    ->title(self::name($record)." can't be deleted")
                    ->body('This user still has related records: '.UserRelatedRecords::describe($related).'. Deactivate the account instead, so this history stays intact.')
                    ->danger()
                    ->persistent()
                    ->send();

                $action->cancel();
            });
    }

    public static function bulkDelete(): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->before(function (DeleteBulkAction $action, Collection $records): void {
                $related = UserRelatedRecords::countsFor($records->map(fn (AppUser $user): mixed => $user->getKey())->all());

                if ($related === []) {
                    return;
                }

                $blocked = $records->filter(fn (AppUser $user): bool => isset($related[$user->getKey()]));

                Notification::make()
                    ->title('No users were deleted')
                    ->body(self::describeUsers($blocked, $related).' still '.($blocked->count() === 1 ? 'has' : 'have').' related records. Deselect them or deactivate them instead.')
                    ->danger()
                    ->persistent()
                    ->send();

                $action->cancel();
            });
    }

    /**
     * "Juan Dela Cruz (12 work orders) and Maria Santos (3 inspections)"
     *
     * @param  Collection<int, AppUser>  $users
     * @param  array<int, array<string, int>>  $related
     */
    public static function describeUsers(Collection $users, array $related): string
    {
        $listed = $users->take(self::MAX_LISTED_USERS)
            ->map(fn (AppUser $user): string => self::name($user).' ('.UserRelatedRecords::describe($related[$user->getKey()] ?? []).')')
            ->values()
            ->all();

        if (($remaining = $users->count() - count($listed)) > 0) {
            $listed[] = $remaining.' more';
        }

        return Arr::join($listed, ', ', ' and ');
    }

    public static function name(AppUser $user): string
    {
        return $user->full_name ?: (string) $user->user_email;
    }
}
