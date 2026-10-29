<?php

namespace App\Support\Activity;

use App\Models\Equipment;

/**
 * Writes the activity log entry for an accessory being allocated to, or unallocated from, an equipment unit.
 *
 * Equipment only logs its name and active flag automatically, so a change of parent_id never reached the
 * activity log. Every allocation path calls this, one entry per accessory; bulk actions are grouped into a
 * batch by ActivityLogging, so the Activity Log page shows them together.
 *
 * The equipment is stored by name (as it was at the time), not by id, like the rest of the history.
 */
class AccessoryAllocationLog
{
    public const ALLOCATED = 'allocated';

    public const UNALLOCATED = 'unallocated';

    public static function allocated(Equipment $accessory, Equipment $equipment): void
    {
        $equipmentLabel = ActivityLabels::subjectLabel($equipment);

        self::write(
            self::ALLOCATED,
            $accessory,
            'Allocated "'.ActivityLabels::subjectLabel($accessory).'" to "'.$equipmentLabel.'"',
            old: null,
            new: $equipmentLabel,
        );
    }

    public static function unallocated(Equipment $accessory, Equipment $formerEquipment): void
    {
        $equipmentLabel = ActivityLabels::subjectLabel($formerEquipment);

        self::write(
            self::UNALLOCATED,
            $accessory,
            'Unallocated "'.ActivityLabels::subjectLabel($accessory).'" from "'.$equipmentLabel.'"',
            old: $equipmentLabel,
            new: null,
        );
    }

    private static function write(string $event, Equipment $accessory, string $description, ?string $old, ?string $new): void
    {
        activity()
            ->performedOn($accessory)
            ->event($event)
            ->withProperties([
                'attributes' => ['parent_equipment' => $new],
                'old' => ['parent_equipment' => $old],
            ])
            ->log($description);
    }
}
