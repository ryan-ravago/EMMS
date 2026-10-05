<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\LogsChanges;

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
            'site_name' => 'EMMS',
            'site_primary_color' => 'amber',
        ]);
    }
}
