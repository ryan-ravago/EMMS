<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class EquipmentTaskChecklistTemplate extends Model
{
    protected $table = 'equipment_task_checklist_template';
    protected $primaryKey = 'etct_id';
    public $timestamps = false;

    protected $fillable = [
        'etct_dep_id',
        'etct_eqm_id',
        'etct_task_id',
        'etct_created_by',
        'etct_created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (EquipmentTaskChecklistTemplate $template) {
            if (!$template->etct_dep_id && Auth::check()) {
                $template->etct_dep_id = Auth::user()->user_dep_id;
            }

            if (!$template->etct_created_by && Auth::check()) {
                $template->etct_created_by = Auth::id();
            }

            if (!$template->etct_created_at) {
                $template->etct_created_at = now();
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'etct_dep_id', 'dep_id');
    }

    public function equipmentUnit(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'etct_eqm_id', 'eqm_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'etct_task_id', 'task_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'etct_created_by', 'user_id');
    }
}
