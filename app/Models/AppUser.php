<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
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
}
