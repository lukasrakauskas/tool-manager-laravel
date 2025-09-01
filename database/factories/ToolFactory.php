<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tool>
 */
class ToolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['Drill', 'Saw', 'Hammer', 'Welder', 'Other']),
            'serial' => strtoupper(fake()->bothify('SN-#####-????')),
            'status' => fake()->randomElement(['available', 'maintenance', 'retired']),
            'power_watts' => fake()->numberBetween(100, 3000),
            'size' => fake()->randomElement(['S', 'M', 'L']),
            'attributes' => [
                'brand' => fake()->randomElement(['Makita', 'DeWalt', 'Bosch', 'Milwaukee']),
                'voltage' => fake()->randomElement([12, 18, 24]),
            ],
            'qr_secret' => bin2hex(random_bytes(16)),
        ];
    }
}
