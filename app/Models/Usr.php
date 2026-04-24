<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usr extends Authenticatable implements HasName, FilamentUser
{
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

    // This method tells Filament what to display in the user menu
    public function getFilamentName(): string
    {
        return "{$this->name}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Define your logic here. For now, we'll allow all found users.
        return true;
    }
}
