<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'dep_id';

    protected $fillable = ['dep_code', 'dep_name', 'is_maintenance'];

    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(AppUser::class, 'user_dep_id', 'dep_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_dep_id', 'dep_id');
    }

    public function equipmentTaskChecklistTemplates(): HasMany
    {
        return $this->hasMany(EquipmentTaskChecklistTemplate::class, 'etct_dep_id', 'dep_id');
    }

    public function equipmentTasksSchedules(): HasMany
    {
        return $this->hasMany(EquipmentTasksSchedule::class, 'ets_dep_id', 'dep_id');
    }
}
