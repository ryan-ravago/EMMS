<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Concerns\LogsChanges;

class Role extends SpatieRole
{
    use LogsChanges;

    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
    ];
}
