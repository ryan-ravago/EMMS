<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use LogsChanges;

    protected $fillable = [
        'site_name',
        'site_logo',
        'site_favicon',
        'site_primary_color',
        'site_font_family',
    ];

    /**
     * Get the first (and only) site settings record, or create it.
     */
    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'site_name' => 'EMS',
            'site_primary_color' => 'amber',
        ]);
    }
}
