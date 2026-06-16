<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inspection extends Model
{
    use LogsActivity;

    protected $table = 'inspections';

    protected $primaryKey = 'ins_id';

    public $timestamps = false;

    protected $fillable = [
        'ins_dep_id',
        'ins_eqm_id',
        'ins_by',
        'ins_dt',
        'ins_submitted_by',
        'ins_submitted_dt',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'ins_dep_id', 'dep_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'ins_eqm_id', 'eqm_id');
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'ins_by', 'user_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'ins_submitted_by', 'user_id');
    }

    public function inspectionItems(): HasMany
    {
        return $this->hasMany(InspectionItem::class, 'insi_ins_id', 'ins_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['ins_dep_id', 'ins_eqm_id', 'ins_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
