<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tool;
use App\Models\Worker;
use App\Models\Assignment;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ]);

        $manager = User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'role' => 'Manager',
        ]);

        $viewer = User::factory()->create([
            'name' => 'Viewer',
            'email' => 'viewer@example.com',
            'role' => 'Viewer',
        ]);

        $tools = Tool::factory()->count(15)->create();
        $workers = Worker::factory()->count(8)->create();

        Assignment::factory()->count(5)->create();
    }
}
