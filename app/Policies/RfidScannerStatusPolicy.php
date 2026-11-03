<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RfidScannerStatus;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Who may look at the gate scanner status. It is read-only: the reader server reports it, nobody edits it by hand.
 */
class RfidScannerStatusPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser): ?bool
    {
        return $authUser->hasRole('super_admin') ? true : null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RfidScannerStatusResource');
    }

    public function view(AuthUser $authUser, RfidScannerStatus $rfidScannerStatus): bool
    {
        return $authUser->can('View:RfidScannerStatusResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, RfidScannerStatus $rfidScannerStatus): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, RfidScannerStatus $rfidScannerStatus): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }
}
