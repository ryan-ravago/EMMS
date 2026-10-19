<?php

namespace Database\Factories;

use App\Models\MaintenanceInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceInspection>
 */
class MaintenanceInspectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inspection_no' => 'IN-ME-'.fake()->unique()->numerify('#########'),
            'status' => MaintenanceInspection::STATUS_COMPLETED,
            'department_code' => 'MECH',
            'department_name' => 'Mechanical',
            'equipment_prc_code' => fake()->bothify('EQ-###'),
            'equipment_name' => fake()->words(3, true),
            'checklist_name' => 'Checklist 1',
            'requested_by' => fake()->safeEmail(),
            'requested_at' => now()->subDays(2),
            'closed_at' => now()->subDay(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => MaintenanceInspection::STATUS_CANCELLED,
            'cancel_remarks' => 'Wrong entry',
        ]);
    }

    public function forDepartment(string $code, string $name): static
    {
        return $this->state(fn (array $attributes): array => [
            'department_code' => $code,
            'department_name' => $name,
        ]);
    }
}
