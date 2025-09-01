<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Worker>
 */
class WorkerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'external_code' => fake()->optional()->bothify('EMP-####'),
            'status' => fake()->randomElement(['active', 'inactive']),
            'qr_secret' => bin2hex(random_bytes(16)),
        ];
    }
}
