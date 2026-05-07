<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'dep_id';

    protected $fillable = ['dep_code', 'dep_name'];

    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(AppUser::class, 'user_dep_id', 'dep_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(ChecklistTemplate::class, 'cli_clt_id', 'clt_id');
    }
}
