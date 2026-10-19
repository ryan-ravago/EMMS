<?php

namespace Database\Factories;

use App\Models\AimsInspection;
use App\Models\AimsInspectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AimsInspectionItem>
 */
class AimsInspectionItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aims_inspection_id' => AimsInspection::factory(),
            'task' => fake()->randomElement(['Lights', 'Horn', 'Brakes']),
            'result' => fake()->randomElement(['Passed', 'Failed', 'N/A']),
            'remarks' => null,
        ];
    }
}
