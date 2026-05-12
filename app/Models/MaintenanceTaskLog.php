<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTaskLog extends Model
{
    protected $table = 'maintenance_task_logs';
    protected $primaryKey = 'mtl_id';

    protected $fillable = [
        'mtl_mt_id',
        'mtl_status_id',
        'mtl_last_act_made',
        'mtl_remarks',
        'mtl_by',
        'mtl_dt',
    ];

    public $timestamps = false;
}
