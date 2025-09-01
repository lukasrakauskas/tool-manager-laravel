<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool_id' => \App\Models\Tool::factory(),
            'worker_id' => \App\Models\Worker::factory(),
            'assigned_at' => now()->subDays(fake()->numberBetween(0, 10)),
            'due_at' => now()->addDays(fake()->numberBetween(1, 30)),
            'returned_at' => null,
            'status' => 'assigned',
            'condition_out' => fake()->randomElement(['good', 'worn', 'new']),
            'condition_in' => null,
        ];
    }
}
