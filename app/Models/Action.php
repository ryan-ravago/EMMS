<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Action extends Model
{
    protected $table = 'actions';
    protected $primaryKey = 'a_id';

    protected $fillable = [
        'a_present_tense',
        'a_past_tense',
    ];

    public $timestamps = false;

    // public function maintenanceTaskLogs(): HasMany
    // {
    //     return $this->hasMany(MaintenanceTaskLog::class, 'mtl_last_act_made');
    // }
}
