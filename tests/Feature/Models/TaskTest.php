<?php

namespace Tests\Feature\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class TaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $task = Task::factory()->create([
            'title' => 'Test Task',
            'assignee_id' => User::factory(),
            'due_date' => '2025-10-29',
            'metadata' => ['foo' => 'bar'],
        ]);

        $tags = Tag::factory()->count(3)->create();
        $task->tags()->attach($tags);
    }

    public function test_task_status_casts_to_enum(): void
    {
        // Arrange
        $task = Task::query()->firstWhere('title', 'Test Task');

        // Assert
        $this->assertInstanceOf(TaskStatus::class, $task->status);
    }

    public function test_task_priority_casts_to_enum(): void
    {
        // Arrange
        $task = Task::query()->firstWhere('title', 'Test Task');

        // Assert
        $this->assertInstanceOf(TaskPriority::class, $task->priority);
    }

    public function test_due_date_casts_to_date(): void
    {
        // Arrange
        $task = Task::query()->firstWhere('title', 'Test Task');

        // Assert
        $this->assertInstanceOf(Carbon::class, $task->due_date);
    }

    public function test_task_metadata_casts_to_array(): void
    {
        // Arrange
        $task = Task::query()->firstWhere('title', 'Test Task');

        // Assert
        $this->assertIsArray($task->metadata);
    }

    public function test_user_relationship(): void
    {
        // Arrange
        $task = Task::query()->firstWhere('title', 'Test Task');

        // Assert
        $this->assertInstanceOf(User::class, $task->user);
    }

    public function test_assignee_relationship(): void
    {
        // Arrange
        $task = Task::query()->firstWhere('title', 'Test Task');

        // Assert
        $this->assertInstanceOf(User::class, $task->assignee);
    }

    public function test_tags_relationship(): void
    {
        // Arrange
        $task = Task::query()->firstWhere('title', 'Test Task');

        // Assert
        $this->assertInstanceOf(Collection::class, $task->tags);
        $this->assertInstanceOf(Tag::class, $task->tags[0]);
    }
}
