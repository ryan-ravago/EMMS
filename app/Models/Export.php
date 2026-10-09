<?php

namespace App\Models;

use Filament\Actions\Exports\Models\Export as FilamentExport;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Filament's Export resolves its user through the logged-in user's class, which a queue
 * worker doesn't have, so it falls back to App\Models\User and the export jobs crash.
 * Pin the relation to AppUser. Bound over Filament's model in AppServiceProvider.
 */
class Export extends FilamentExport
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'user_id', 'user_id');
    }
}
