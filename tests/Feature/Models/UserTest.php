<?php

namespace Tests\Feature\Models;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'email' => 'test@test.test',
        ]);
    }

    public function test_is_admin_casts_to_boolean(): void
    {
        // Arrange
        $user = User::query()->firstWhere('email', 'test@test.test');

        // Assert
        $this->assertIsBool($user->is_admin);
    }

    public function test_tasks_relationship()
    {
        // Arrange
        $user = User::query()->firstWhere('email', 'test@test.test');
        $tasks = Task::factory()->count(3)->create([
            'user_id' => $user->getKey(),
        ]);

        // Assert
        $this->assertEquals(3, $user->tasks->count());
        $this->assertInstanceOf(Collection::class, $user->tasks);
        $this->assertInstanceOf(Task::class, $user->tasks[0]);
    }

    public function test_assigned_tasks_relationship()
    {
        // Arrange
        $user = User::query()->firstWhere('email', 'test@test.test');
        $tasks = Task::factory()->count(3)->create([
            'assignee_id' => $user->getKey(),
        ]);

        // Assert
        $this->assertEquals(3, $user->assignedTasks->count());
        $this->assertInstanceOf(Collection::class, $user->assignedTasks);
        $this->assertInstanceOf(Task::class, $user->assignedTasks[0]);
    }
}
