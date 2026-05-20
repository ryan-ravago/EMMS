<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskUsageType extends Model
{
    protected $table = 'task_usage_types';
    protected $primaryKey = 'tut_id';
    public $timestamps = false;

    protected $fillable = [
        'tut_name',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_tut_id', 'tut_id');
    }
}
