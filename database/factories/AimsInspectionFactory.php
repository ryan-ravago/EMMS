<?php

namespace Database\Factories;

use App\Models\AimsInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AimsInspection>
 */
class AimsInspectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inspection_no' => 'IN-AI-'.fake()->unique()->numerify('#########'),
            'status' => AimsInspection::STATUS_COMPLETED,
            'equipment_prc_code' => fake()->bothify('EQ-###'),
            'equipment_name' => fake()->words(3, true),
            'checklist_name' => 'Template 2',
            'lessee' => fake()->company(),
            'jobsite' => fake()->city(),
            'operator' => fake()->name(),
            'day_duration' => fake()->numberBetween(1, 7),
            'inspected_by' => fake()->firstName(),
            'inspected_at' => now()->subDay(),
            'requested_by' => fake()->safeEmail(),
            'requested_at' => now()->subDays(2),
            'closed_at' => now()->subDay(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AimsInspection::STATUS_CANCELLED,
            'cancel_remarks' => 'Wrong entry',
        ]);
    }
}
