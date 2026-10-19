<?php

namespace Database\Factories;

use App\Models\AimsInspection;
use App\Models\AimsInspectionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AimsInspectionLog>
 */
class AimsInspectionLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aims_inspection_id' => AimsInspection::factory(),
            'action' => 'Created',
            'status' => 'Pending - Inspection',
            'remarks' => null,
            'performed_by' => fake()->safeEmail(),
            'logged_at' => now()->subDays(2),
        ];
    }
}
