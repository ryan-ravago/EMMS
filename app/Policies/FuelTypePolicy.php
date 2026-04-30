<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FuelType;
use Illuminate\Auth\Access\HandlesAuthorization;

class FuelTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FuelTypeResource');
    }

    public function view(AuthUser $authUser, FuelType $fuelType): bool
    {
        return $authUser->can('View:FuelTypeResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FuelTypeResource');
    }

    public function update(AuthUser $authUser, FuelType $fuelType): bool
    {
        return $authUser->can('Update:FuelTypeResource');
    }

    public function delete(AuthUser $authUser, FuelType $fuelType): bool
    {
        return $authUser->can('Delete:FuelTypeResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FuelTypeResource');
    }

    public function restore(AuthUser $authUser, FuelType $fuelType): bool
    {
        return $authUser->can('Restore:FuelTypeResource');
    }

    public function forceDelete(AuthUser $authUser, FuelType $fuelType): bool
    {
        return $authUser->can('ForceDelete:FuelTypeResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FuelTypeResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FuelTypeResource');
    }

    public function replicate(AuthUser $authUser, FuelType $fuelType): bool
    {
        return $authUser->can('Replicate:FuelTypeResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FuelTypeResource');
    }

}