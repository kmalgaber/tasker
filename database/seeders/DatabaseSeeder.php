<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $user = User::factory()->create([
            'id' => '6daab073-63fd-4d0d-b503-d2901af4f56a',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Task::factory()->count(3)->create([
            'user_id' => $user->getKey(),
            'assignee_id' => $user->getKey(),
        ]);
    }
}
