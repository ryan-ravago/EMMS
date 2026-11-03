<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RfidRawLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Who may look at the raw reader log. It is read-only: the API writes the rows and the processor updates them,
 * and old rows are removed by the retention prune, never by hand.
 */
class RfidRawLogPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser): ?bool
    {
        return $authUser->hasRole('super_admin') ? true : null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RfidRawLogResource');
    }

    public function view(AuthUser $authUser, RfidRawLog $rfidRawLog): bool
    {
        return $authUser->can('View:RfidRawLogResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, RfidRawLog $rfidRawLog): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, RfidRawLog $rfidRawLog): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }
}
