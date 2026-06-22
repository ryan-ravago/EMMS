<?php

namespace App\Models;

class Technician extends AppUser
{
    /**
     * The table associated with the model.
     */
    protected $table = 'app_users';

    /**
     * Use the parent's morph class so Spatie roles/permissions
     * resolve correctly against the `model_has_roles` table.
     */
    public function getMorphClass(): string
    {
        return AppUser::class;
    }
}
