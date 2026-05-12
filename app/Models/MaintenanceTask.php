<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceTask extends Model
{
    protected $table = 'maintenance_tasks';
    protected $primaryKey = 'mt_id';

    protected $fillable = [
        'mt_eqm_id',
        'mt_eqm_log',
        'mt_dep_id',
        'mt_clt_id',
        'mt_clt_log',
        'mt_cli_id',
        'mt_cli_log',
        'mt_status_id',
        'mt_remarks',
        'mt_scheduled_dt',
        'mt_closed_dt'
    ];

    public $timestamps = false;

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'mt_dep_id');
    }

    public function equipmentUnit(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'mt_eqm_id');
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'mt_clt_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class, 'mt_cli_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'mt_status_id');
    }
}
