<?php

namespace Tests\Feature\Observers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskObserverTest extends TestCase
{
    public function test_logs_created_task(): void
    {
        // Arrange
        $user = User::factory()->create();
        $title = fake()->sentence;
        $description = fake()->text;

        // Act
        $this->actingAs($user);
        $task = Task::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => $title,
            'description' => $description,
            'status' => TaskStatus::Pending,
            'priority' => TaskPriority::Low,
        ]);

        // Assert
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Task::class,
            'subject_id' => $task->getKey(),
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'description' => 'task created',
            'event' => 'created',
            'properties->title' => $title,
            'properties->description' => $description,
            'properties->status' => TaskStatus::Pending->value,
            'properties->priority' => TaskPriority::Low->value,
        ]);
    }

    public function test_logs_updated_task(): void
    {
        // Arrange
        $user = User::factory()->create();
        $task = Task::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => fake()->sentence,
            'description' => fake()->text,
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::Low->value,
        ])->fresh();

        // Act
        $this->actingAs($user);
        $task->update([
            'status' => TaskStatus::Completed->value,
        ]);

        // Assert
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Task::class,
            'subject_id' => $task->getKey(),
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'description' => 'task updated',
            'event' => 'updated',
            'properties->status' => TaskStatus::Completed->value,
        ]);
    }

    public function test_logs_deleted_task(): void
    {
        // Arrange
        $title = fake()->sentence;
        $description = fake()->text;
        $user = User::factory()->create();
        $task = Task::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => $title,
            'description' => $description,
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::Low->value,
        ]);

        // Act
        $this->actingAs($user);
        $task->delete();

        // Assert
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Task::class,
            'subject_id' => $task->getKey(),
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'description' => 'task deleted',
            'event' => 'deleted',
            'properties->title' => $title,
            'properties->description' => $description,
            'properties->status' => TaskStatus::Pending->value,
            'properties->priority' => TaskPriority::Low->value,
        ]);
    }

    public function test_logs_restored_task(): void
    {
        // Arrange
        $title = fake()->sentence;
        $description = fake()->text;
        $user = User::factory()->create();
        $task = Task::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => $title,
            'description' => $description,
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::Low->value,
        ]);
        $task->delete();

        // Act
        $this->actingAs($user);
        $task->restore();

        // Assert
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Task::class,
            'subject_id' => $task->getKey(),
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'description' => 'task restored',
            'event' => 'restored',
            'properties->title' => $title,
            'properties->description' => $description,
            'properties->status' => TaskStatus::Pending->value,
            'properties->priority' => TaskPriority::Low->value,
        ]);
    }

    public function test_logs_force_deleted_task(): void
    {
        // Arrange
        $title = fake()->sentence;
        $description = fake()->text;
        $user = User::factory()->create();
        $task = Task::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => $title,
            'description' => $description,
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::Low->value,
        ]);

        // Act
        $this->actingAs($user);
        $task->forceDelete();

        // Assert
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Task::class,
            'subject_id' => $task->getKey(),
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'description' => 'task force deleted',
            'event' => 'force_deleted',
            'properties->title' => $title,
            'properties->description' => $description,
            'properties->status' => TaskStatus::Pending->value,
            'properties->priority' => TaskPriority::Low->value,
        ]);
    }
}
