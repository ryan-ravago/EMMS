<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsChanges;

class Insp extends Model
{
    use LogsChanges;

    protected $table = 'insps';

    protected $primaryKey = 'insp_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /** Too large to be useful in the activity log. */
    protected array $activityExclude = ['checklist_temp_items'];

    protected $fillable = [
        'insp_id',
        'insp_no',
        'insp_dep_id',
        'insp_eqm_id',
        'is_submitted',
        'checklist_template_name',
        'checklist_temp_items',
        'insp_remarks',
        'insp_by',
        'insp_submitted_by',
        'insp_submitted_at',
    ];

    protected $casts = [
        'insp_submitted_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'insp_dep_id', 'dep_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'insp_eqm_id', 'eqm_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspItem::class, 'inspi_insp_id', 'insp_id');
    }
}
