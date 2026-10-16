<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Edit logs used to store location ids in changes.location_id; they now store the location path
     * (e.g. "Main Yard > Bay 1"), so history no longer depends on the location record existing.
     * Numeric values are treated as ids; text values are already names and are left as they are.
     */
    public function up(): void
    {
        $locations = DB::table('locations')->get(['id', 'name', 'parent_id'])->keyBy('id');

        $pathFor = function (mixed $value) use ($locations): mixed {
            if (! is_int($value) && ! (is_string($value) && ctype_digit($value))) {
                return $value;
            }

            $id = (int) $value;

            if (! $locations->has($id)) {
                return "Deleted location #{$id}";
            }

            $names = [];
            $seen = [];

            for ($location = $locations->get($id); $location !== null && ! isset($seen[$location->id]); $location = $locations->get($location->parent_id)) {
                $seen[$location->id] = true;
                array_unshift($names, $location->name);
            }

            return implode(' > ', $names);
        };

        DB::table('asset_edit_logs')
            ->orderBy('id')
            ->chunkById(500, function ($logs) use ($pathFor): void {
                foreach ($logs as $log) {
                    $changes = json_decode($log->changes, true);

                    if (! is_array($changes) || ! isset($changes['location_id']) || ! is_array($changes['location_id'])) {
                        continue;
                    }

                    $changes['location_id']['old'] = $pathFor($changes['location_id']['old'] ?? null);
                    $changes['location_id']['new'] = $pathFor($changes['location_id']['new'] ?? null);

                    DB::table('asset_edit_logs')->where('id', $log->id)->update(['changes' => json_encode($changes)]);
                }
            });
    }

    /**
     * Paths cannot be turned back into ids reliably, so this migration is not reversed.
     */
    public function down(): void
    {
        //
    }
};
