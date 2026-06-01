<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ReportSubmission extends Model
{
    protected $table = 'report_submissions';
    protected $primaryKey = 'rs_id';
    public $timestamps = false;

    protected $fillable = [
        'rs_wo_id',
        'rs_work_date',
        'rs_submitted_by',
        'rs_submitted_dt',
    ];

    // protected $casts = [
    //     'rs_work_date'     => 'date',
    //     'rs_submitted_dt'  => 'datetime',
    // ];

    // Belongs to WorkOrder
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'rs_wo_id', 'wo_id');
    }

    // Belongs to AppUser (submitter)
    public function submittedBy()
    {
        return $this->belongsTo(AppUser::class, 'rs_submitted_by', 'user_id');
    }

    // Has many workers through worker_reports
    // Belongs to many workers
    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(
            AppUser::class,
            'worker_reports',
            'wr_rs_id',
            'wr_worker_id',
            'rs_id',
            'user_id'
        );
    }
}
