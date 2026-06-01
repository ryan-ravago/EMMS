<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class AppUser extends Authenticatable implements HasName, FilamentUser
{
    use HasRoles;

    protected $table = 'app_users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_fname',
        'user_mname',
        'user_lname',
        'user_email',
        'user_contact_no',
        'user_fb_profile_link',
        'user_dep_id',
    ];

    public $timestamps = false;

    // This method tells Filament what to display in the user menu
    public function getFullNameAttribute(): string
    {
        return trim("{$this->user_fname} {$this->user_lname}");
    }

    public function getFilamentName(): string
    {
        return "{$this->user_fname} {$this->user_lname}";
        // Or simply: return $this->user_email;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Define your logic here. For now, we'll allow all found users.
        return true;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'user_dep_id');
    }

    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_assignments', 'woa_worker_id', 'woa_wo_id', 'user_id', 'wo_id');
    }

    public function reportSubmissions(): BelongsToMany
    {
        return $this->belongsToMany(
            ReportSubmission::class,
            'worker_reports',
            'wr_worker_id',
            'wr_rs_id',
            'user_id',
            'rs_id'
        );
    }
}
