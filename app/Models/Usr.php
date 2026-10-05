<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usr extends Authenticatable implements HasName, FilamentUser
{
    use Notifiable;

    // Tell Laravel to use the connection defined in config/database.php
    protected $connection = 'auth_db';

    protected $table = 'usr';
    protected $primaryKey = 'userId';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $hidden = ['userPassword'];

    protected $casts = [
        'userPassword' => 'hashed',
    ];

    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }

    public function getAuthPassword()
    {
        return $this->userPassword;
    }

    // Lets Filament's reset-password page write to the right column.
    public function getAuthPasswordName()
    {
        return 'userPassword';
    }

    // usr has no remember_token column, but the reset-password page tries to rotate it.
    public function setRememberTokenAttribute(mixed $value): void
    {
        //
    }

    // This method tells Filament what to display in the user menu
    public function getFilamentName(): string
    {
        return "{$this->name}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Only people with an active EMMS account (app_users) can use EMMS's password reset.
        return AppUser::query()
            ->where('user_email', $this->email)
            ->where('is_active', true)
            ->exists();
    }
}
