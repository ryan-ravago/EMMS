<?php

namespace App\Models;

use Database\Factories\AimsInspectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A closed (completed or cancelled) AIMS inspection copied from AppSheet as plain text.
 * Equipment, lessee, jobsite, operator and people are stored as text, not as references.
 */
class AimsInspection extends Model
{
    /** @use HasFactory<AimsInspectionFactory> */
    use HasFactory;

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    public const DEPARTMENT_CODE = 'AIMS';

    public $timestamps = false;

    protected $fillable = [
        'inspection_no',
        'status',
        'equipment_prc_code',
        'equipment_name',
        'checklist_name',
        'lessee',
        'jobsite',
        'operator',
        'day_duration',
        'inspected_by',
        'inspected_at',
        'remarks',
        'requested_by',
        'requested_at',
        'completion_remarks',
        'cancel_remarks',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'day_duration' => 'integer',
            'inspected_at' => 'datetime',
            'requested_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * AIMS inspections are for super admins and the AIMS department only.
     */
    public static function isVisibleTo(?AppUser $user): bool
    {
        return $user !== null
            && ($user->hasRole('super_admin') || strtoupper((string) $user->department?->dep_code) === self::DEPARTMENT_CODE);
    }

    /**
     * @param  Builder<AimsInspection>  $query
     * @return Builder<AimsInspection>
     */
    public function scopeVisibleTo(Builder $query, ?AppUser $user): Builder
    {
        return static::isVisibleTo($user) ? $query : $query->whereRaw('1 = 0');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AimsInspectionItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AimsInspectionLog::class)->orderBy('logged_at')->orderBy('id');
    }

    /**
     * The live equipment record, matched by PRC code. Null when the equipment is gone or has no code.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_prc_code', 'eqm_prc_code');
    }
}
