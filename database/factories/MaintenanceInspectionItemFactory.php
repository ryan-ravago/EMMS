<?php

namespace Database\Factories;

use App\Models\MaintenanceInspection;
use App\Models\MaintenanceInspectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceInspectionItem>
 */
class MaintenanceInspectionItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'maintenance_inspection_id' => MaintenanceInspection::factory(),
            'task' => fake()->randomElement(['Lights', 'Horn', 'Brakes']),
            'result' => fake()->randomElement(['Passed', 'Failed', 'N/A']),
            'remarks' => null,
        ];
    }
}
