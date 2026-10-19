<?php

namespace Database\Factories;

use App\Models\MaintenanceInspection;
use App\Models\MaintenanceInspectionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceInspectionLog>
 */
class MaintenanceInspectionLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'maintenance_inspection_id' => MaintenanceInspection::factory(),
            'action' => 'Requested',
            'status' => 'Pending - Manager Approval',
            'remarks' => null,
            'performed_by' => fake()->safeEmail(),
            'logged_at' => now()->subDays(2),
        ];
    }
}
