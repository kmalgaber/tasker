<?php

namespace Tests\Feature\Models;

use App\Models\Tag;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class TagTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tag = Tag::factory()->create([
            'name' => 'test'
        ]);
        $tasks = Task::factory()->count(3)->create();

        $tag->tasks()->attach($tasks);
    }

    public function test_tasks_relationship(): void
    {
        // Arrange
        $tag = Tag::query()->firstWhere('name', 'test');

        // Assert
        $this->assertInstanceOf(Collection::class, $tag->tasks);
        $this->assertInstanceOf(Task::class, $tag->tasks[0]);
    }
}
