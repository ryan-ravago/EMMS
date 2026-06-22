<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InspectionItem extends Model
{
    use LogsActivity;

    protected $table = 'inspection_items';

    protected $primaryKey = 'insi_id';

    public $timestamps = false;

    protected $fillable = [
        'insi_no',
        'insi_ins_id',
        'insi_task_id',
        'insi_status_id',
        'insi_result',
        'insi_cli_name_for_record',
        'insi_remarks',
        'insi_closed_dt',
    ];

    protected $casts = [
        'insi_closed_dt' => 'datetime',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'insi_ins_id', 'ins_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'insi_task_id', 'task_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'insi_status_id', 'status_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(InspectionResult::class, 'insi_result', 'insr_code');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InspectionItemLog::class, 'inil_insi_id', 'insi_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'wo_insi_id', 'insi_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Inspection')
            ->setDescriptionForEvent(fn (string $eventName) => "Inspection Item has been {$eventName}")
            ->dontSubmitEmptyLogs();
    }
}
