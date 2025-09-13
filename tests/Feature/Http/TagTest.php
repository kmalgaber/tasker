<?php

namespace Tests\Feature\Http;

use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TagTest extends TestCase
{
    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);
    }

    public function test_index(): void
    {
        // Arrange
        $tag1 = Tag::factory()->create(['name' => 'tag1', 'color' => '#ff0000']);
        $tag2 = Tag::factory()->create(['name' => 'tag2', 'color' => '#00ff00']);
        $tag3 = Tag::factory()->create(['name' => 'tag3', 'color' => '#0000ff']);

        // Act
        $response = $this->actingAs($this->user)->getJson('tags');

        // Assert
        $response->assertSuccessful()->assertJson([
            'data' => [
                [
                    'name' => 'tag1',
                    'color' => '#ff0000',
                ],
                [
                    'name' => 'tag2',
                    'color' => '#00ff00',
                ],
                [
                    'name' => 'tag3',
                    'color' => '#0000ff',
                ],
            ],
        ]);
    }

    public function test_create(): void
    {
        // Act
        $this->actingAs($this->user)->postJson('tags', [
            'name' => 'tag1',
            'color' => '#ff0000',
        ])->assertForbidden();
        $response = $this->actingAs($this->admin)->postJson('tags', [
            'name' => 'tag1',
            'color' => '#ff0000',
        ]);

        // Assert
        $response->assertSuccessful()->assertJson([
            'data' => [
                'name' => 'tag1',
                'color' => '#ff0000',
            ],
        ]);
        $this->assertDatabaseHas('tags', [
            'name' => 'tag1',
            'color' => '#ff0000',
        ]);
    }

    public function test_update(): void
    {
        // Arrange
        $tag = Tag::factory()->create([
            'color' => '#ff0000',
        ]);

        // Act
        $this->actingAs($this->user)->putJson('tags/'.$tag->getKey(), [
            'color' => '#00ff00',
        ])->assertForbidden();
        $response = $this->actingAs($this->admin)->putJson('tags/'.$tag->getKey(), [
            'color' => '#00ff00',
        ]);

        // Assert
        $response->assertSuccessful()->assertJson([
            'data' => [
                'name' => $tag->name,
                'color' => '#00ff00',
            ],
        ]);
        $this->assertDatabaseHas('tags', [
            'name' => $tag->name,
            'color' => '#00ff00',
        ]);
    }

    public function test_delete(): void
    {
        // Arrange
        $tag = Tag::factory()->create([
            'name' => 'tag1',
            'color' => '#ff0000',
        ]);
        $task = Task::factory()->create();
        $task->tags()->sync([$tag->getKey()]);

        // Act
        $this->actingAs($this->user)->deleteJson('tags/'.$tag->getKey())->assertForbidden();
        $this->actingAs($this->admin)->deleteJson('tags/'.$tag->getKey())->assertNoContent();

        // Assert
        $this->assertDatabaseMissing('tags', [
            'name' => $tag->name,
        ]);
    }
}
