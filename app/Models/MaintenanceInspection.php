<?php

namespace App\Models;

use Database\Factories\MaintenanceInspectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A closed (completed or cancelled) maintenance inspection copied from AppSheet as plain text.
 * Department, equipment and people are stored as text, not as references.
 */
class MaintenanceInspection extends Model
{
    /** @use HasFactory<MaintenanceInspectionFactory> */
    use HasFactory;

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    public $timestamps = false;

    protected $fillable = [
        'inspection_no',
        'status',
        'department_code',
        'department_name',
        'equipment_prc_code',
        'equipment_name',
        'checklist_name',
        'work_order_no',
        'requested_by',
        'requested_at',
        'asset_admin_manager_note',
        'manager_action',
        'manager_action_by',
        'manager_remarks',
        'technicians',
        'verifiers',
        'tech_action',
        'tech_action_by',
        'tech_remarks',
        'verifier_action',
        'verifier_action_by',
        'verifier_remarks',
        'cancel_remarks',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Whether the user's role alone gives access: super admins, and managers of a maintenance department
     * (Mechanical, Electrical or Preventive). Other roles need the Shield permission.
     */
    public static function roleGrantsAccess(?AppUser $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->hasRole('super_admin')
            || ($user->hasRole('manager') && (bool) $user->department?->is_maintenance);
    }

    /**
     * What a user may see: everything for a super admin, otherwise only their own department's inspections.
     *
     * @param  Builder<MaintenanceInspection>  $query
     * @return Builder<MaintenanceInspection>
     */
    public function scopeVisibleTo(Builder $query, ?AppUser $user): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        $departmentCode = $user->department?->dep_code;

        return $departmentCode === null
            ? $query->whereRaw('1 = 0')
            : $query->where('department_code', $departmentCode);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaintenanceInspectionItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MaintenanceInspectionLog::class)->orderBy('logged_at')->orderBy('id');
    }

    /**
     * The work order raised from this inspection, matched by work order number. This is not a database foreign
     * key: it is null when the work order does not exist (any more), while work_order_no keeps the number.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_no', 'wo_no');
    }

    /**
     * The live equipment record, matched by PRC code. Null when the equipment is gone or has no code.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_prc_code', 'eqm_prc_code');
    }
}
