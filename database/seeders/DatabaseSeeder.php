<?php

namespace Database\Seeders;

use App\Models\Tag;
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
        User::factory()->create([
            'id' => '6daab073-63fd-4d0d-b503-d2901af4f56b',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
        $user = User::factory()->create([
            'id' => '6daab073-63fd-4d0d-b503-d2901af4f56a',
            'name' => 'Test User',
            'email' => 'user@example.com',
        ]);
        User::factory()->create([
            'id' => '6daab073-63fd-4d0d-b503-d2901af4f57b',
            'name' => 'Another User',
            'email' => 'anotheruser@example.com',
        ]);

        $onboardingTag = Tag::factory()->create([
            'name' => 'onboarding',
            'color' => '#22bb11',
        ]);

        $transactionsTag = Tag::factory()->create([
            'name' => 'transactions',
            'color' => '#99aa11',
        ]);

        Tag::factory()->create([
            'name' => 'referral',
            'color' => '#11aa99',
        ]);
        $taskWithTags = Task::factory()->create([
            'user_id' => $user->getKey(),
        ]);
        $taskWithTags->tags()->attach([$onboardingTag->getKey(), $transactionsTag->getKey()]);

        Task::factory()->count(3)->create([
            'user_id' => $user->getKey(),
            'assignee_id' => $user->getKey(),
        ]);
    }
}
