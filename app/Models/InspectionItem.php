<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionItem extends Model
{
    protected $table = 'inspection_items';
    protected $primaryKey = 'insi_id';
    public $timestamps = false;

    protected $fillable = [
        'insi_ins_id',
        'insi_task_id',
        'insi_result',
        'insi_cli_name_for_record',
        'insi_remarks',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'insi_ins_id', 'ins_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'insi_task_id', 'task_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(InspectionResult::class, 'insi_result', 'insr_code');
    }
}
