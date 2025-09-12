<?php

namespace Tests\Feature\Http;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskTest extends TestCase
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
        $task1 = Task::factory()->create([
            'user_id' => $this->user->getKey(),
            'title' => 'Task 1',
            'description' => 'Task 1 description',
            'status' => TaskStatus::Pending,
            'priority' => TaskPriority::Medium,
        ]);
        $task2 = Task::factory()->create([
            'user_id' => $this->user->getKey(),
            'title' => 'Task 2',
            'description' => 'Task 2 description',
        ]);

        // Act
        $this->getJson('tasks')->assertUnauthorized();
        $this->actingAs($this->admin)->getJson('tasks')->assertSuccessful();

        $response = $this->actingAs($this->user)->getJson('tasks');

        // Assert
        $response->assertSuccessful()->assertJsonCount(2, 'data')->assertJson([
            'data' => [
                [
                    'id' => $task1->getKey(),
                    'user' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'email_verified_at' => $this->user->email_verified_at->toRfc3339String(),
                        'avatar' => $this->user->avatar,
                        'is_admin' => $this->user->is_admin ?? false,
                    ],
                    'title' => 'Task 1',
                    'status' => TaskStatus::Pending->value,
                    'priority' => TaskPriority::Medium->value,
                ]
            ],
        ])->assertJsonMissing([
            'data' => [
                [
                    'description' => 'Task 1 description',
                    'metadata' => [],
                ]
            ],
        ]);
    }

    public function test_index_filtered(): void
    {
        // Arrange
        $tag = Tag::factory()->create([
            'name' => 'test',
        ]);
        $task1 = Task::factory()->create([
            'title' => 'Task 1',
            'description' => 'Task 1 description',
            'status' => TaskStatus::Pending,
            'priority' => TaskPriority::Medium,
        ]);
        $task2 = Task::factory()->create([
            'title' => 'Task 2',
            'description' => 'Task 2 description',
            'status' => TaskStatus::Completed,
            'priority' => TaskPriority::High,
        ]);
        $task3 = Task::factory()->create([
            'assignee_id' => $this->user->getKey(),
            'title' => 'Task 3',
            'description' => 'Task 3 description',
            'status' => TaskStatus::Pending,
            'priority' => TaskPriority::Low,
        ]);

        $task1->tags()->attach([$tag->getKey()]);

        // Act
        $filteredByTag = $this->actingAs($this->user)->getJson('tasks?filter[tags.name]=test');
        $filteredByStatus = $this->actingAs($this->user)->getJson('tasks?filter[status]=completed');
        $filteredByPriority = $this->actingAs($this->user)->getJson('tasks?filter[priority]=high');
        $filteredByAssignee = $this->actingAs($this->user)->getJson('tasks?filter[assignee_id]=' . $this->user->getKey());

        // Assert
        $filteredByTag->assertSuccessful()->assertJsonCount(1, 'data')->assertJson([
            'data' => [
                [
                    'id' => $task1->getKey(),
                ]
            ],
        ])->assertJsonMissing([
            'data' => [
                [
                    'id' => $task2->getKey(),
                ]
            ]
        ])->assertJsonMissing([
            'data' => [
                [
                    'id' => $task3->getKey(),
                ]
            ]
        ]);
        $filteredByStatus->assertSuccessful()->assertJsonCount(1, 'data')->assertJson([
            'data' => [
                [
                    'id' => $task2->getKey(),
                ]
            ],
        ]);
        $filteredByPriority->assertSuccessful()->assertJsonCount(1, 'data')->assertJson([
            'data' => [
                [
                    'id' => $task2->getKey(),
                ]
            ],
        ]);
        $filteredByAssignee->assertSuccessful()->assertJsonCount(1, 'data')->assertJson([
            'data' => [
                [
                    'id' => $task3->getKey(),
                ]
            ],
        ]);
    }

    public function test_index_sorted(): void
    {
        // Arrange
        $task1 = Task::factory()->create([
            'title' => 'Task 1',
            'description' => 'Task 1 description',
            'status' => TaskStatus::Pending,
            'priority' => TaskPriority::Medium,
            'due_date' => now()->addDays(2)->toDateString(),
            'created_at' => now()->subDays(30),
        ]);
        $task2 = Task::factory()->create([
            'title' => 'Task 2',
            'description' => 'Task 2 description',
            'status' => TaskStatus::Completed,
            'priority' => TaskPriority::High,
            'due_date' => now()->addDay()->toDateString(),
            'created_at' => now()->subDays(20),
        ]);
        $task3 = Task::factory()->create([
            'assignee_id' => $this->user->getKey(),
            'title' => 'Task 3',
            'description' => 'Task 3 description',
            'status' => TaskStatus::Pending,
            'priority' => TaskPriority::Low,
            'due_date' => now()->addDays(3)->toDateString(),
            'created_at' => now()->subDays(10),
        ]);

        // Act
        $sortedByCreatedAtDesc = $this->actingAs($this->user)->getJson('tasks?sort=-created_at');
        $sortedByDueDate = $this->actingAs($this->user)->getJson('tasks?sort=due_date');
        $sortedTitle = $this->actingAs($this->user)->getJson('tasks?sort=title');

        // Assert
        $sortedByCreatedAtDesc->assertSuccessful()->assertJson([
            'data' => [
                [
                    'id' => $task3->getKey(),
                ],
                [
                    'id' => $task2->getKey(),
                ],
                [
                    'id' => $task1->getKey(),
                ]
            ]
        ]);
        $sortedByDueDate->assertSuccessful()->assertJson([
            'data' => [
                [
                    'id' => $task2->getKey(),
                ],
                [
                    'id' => $task1->getKey(),
                ],
                [
                    'id' => $task3->getKey(),
                ]
            ]
        ]);
        $sortedTitle->assertSuccessful()->assertJson([
            'data' => [
                [
                    'id' => $task1->getKey(),
                ],
                [
                    'id' => $task2->getKey(),
                ],
                [
                    'id' => $task3->getKey(),
                ]
            ]
        ]);
    }

    public function test_show(): void
    {
        // Arrange
        $task = Task::factory()->create();

        // Act
        $this->getJson('tasks/' . $task->getKey())->assertUnauthorized();
        $this->actingAs($this->admin)->getJson('tasks/' . $task->getKey())->assertSuccessful();
        $response = $this->actingAs($this->user)->getJson('tasks/' . $task->getKey());

        // Assert
        $response->assertSuccessful()->assertJson([
            'data' => [
                'id' => $task->getKey(),
                'user' => [
                    'id' => $task->user_id,
                    'name' => $task->user->name,
                    'email' => $task->user->email,
                    'email_verified_at' => $task->user->email_verified_at->toRfc3339String(),
                    'avatar' => $task->user->avatar,
                    'is_admin' => $task->user->is_admin,
                ],
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'due_date' => $task->due_date,
                'assignee' => null,
                'tags' => [],
                'metadata' => $task->metadata,
            ],
        ]);
    }

    public function test_create(): void
    {
        // Arrange
        $taskData = [
            'title' => 'Test task',
        ];
        $taskDataByAdmin = [
            'title' => 'Test task by admin',
        ];

        // Act
        $this->postJson('tasks', $taskData)->assertUnauthorized();
        $this->actingAs($this->admin)->postJson('tasks', $taskDataByAdmin)->assertSuccessful();
        $response = $this->actingAs($this->user)->postJson('tasks', $taskData);

        // Assert
        $response->assertSuccessful();

        $this->assertDatabaseCount('tasks', 2);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test task',
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::Medium->value,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_with_full_parameters(): void
    {
        // Arrange
        $tag1 = Tag::factory()->create(['name' => 'tag1', 'color' => '#ff0000']);
        $tag2 = Tag::factory()->create(['name' => 'tag2', 'color' => '#00ff00']);
        $tag3 = Tag::factory()->create(['name' => 'tag3', 'color' => '#0000ff']);
        $assignee = User::factory()->create();
        $taskData = [
            'title' => 'Test task',
            'description' => 'Test description',
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
            'due_date' => now()->addDays(5)->toDateString(),
            'assignee_id' => $assignee->getKey(),
            'tags' => ['tag1', 'tag3'],
            'metadata' => [
                'foo' => 'bar',
                'goo' => 'baz',
            ],
        ];

        // Act
        $response = $this->actingAs($this->user)->postJson('tasks', $taskData);

        // Assert
        $response->assertSuccessful()->assertJson([
            'data' => [
                'user' => [
                    'id' => $this->user->getKey(),
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'email_verified_at' => $this->user->email_verified_at->toRfc3339String(),
                    'avatar' => $this->user->avatar,
                    'is_admin' => $this->user->is_admin ?? false,
                ],
                'title' => 'Test task',
                'description' => 'Test description',
                'status' => TaskStatus::InProgress->value,
                'priority' => TaskPriority::High->value,
                'due_date' => now()->addDays(5)->toDateString(),
                'assignee' => [
                    'id' => $assignee->getKey(),
                    'name' => $assignee->name,
                    'email' => $assignee->email,
                    'email_verified_at' => $assignee->email_verified_at->toRfc3339String(),
                    'avatar' => $assignee->avatar,
                    'is_admin' => $assignee->is_admin ?? false,
                ],
                'tags' => [
                    [
                        'name' => 'tag1',
                        'color' => '#ff0000',
                    ],
                    [
                        'name' => 'tag3',
                        'color' => '#0000ff',
                    ]
                ],
                'metadata' => [
                    'foo' => 'bar',
                    'goo' => 'baz',
                ]
            ],
        ]);

        $this->assertDatabaseCount('tasks', 1);
        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->getKey(),
            'title' => 'Test task',
            'description' => 'Test description',
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
            'due_date' => now()->addDays(5)->toDateString(),
            'assignee_id' => $assignee->getKey(),
            'metadata->foo' => 'bar',
            'metadata->goo' => 'baz',
        ]);
        $task = Task::query()->firstWhere('title', 'Test task');
        $this->assertDatabaseHas('tag_task', [
            'task_id' => $task->getKey(),
            'tag_id' => $tag1->getKey(),
        ]);
        $this->assertDatabaseMissing('tag_task', [
            'task_id' => $task->getKey(),
            'tag_id' => $tag2->getKey(),
        ]);
        $this->assertDatabaseHas('tag_task', [
            'task_id' => $task->getKey(),
            'tag_id' => $tag3->getKey(),
        ]);
    }

    public function test_update(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'title' => 'Task to be updated',
            'user_id' => $this->user->id,
            'status' => TaskStatus::Pending->value,
            'priority' => TaskPriority::High->value,
        ]);
        $task2 = Task::factory()->create([
            'title' => 'Task to be updated by admin',
            'user_id' => $this->user->id,
            'status' => TaskStatus::Pending->value,
        ]);
        $updatedTaskData = [
            'status' => TaskStatus::Completed,
        ];
        $otherUser = User::factory()->create();

        // Act
        $this->putJson('tasks/' . $task->getKey(), $updatedTaskData)->assertUnauthorized();
        $this->actingAs($otherUser)->putJson('tasks/' . $task->getKey(), $updatedTaskData)->assertForbidden();
        $this->actingAs($this->admin)->putJson('tasks/' . $task2->getKey(), $updatedTaskData)->assertSuccessful();
        $response = $this->actingAs($this->user)->putJson('tasks/' . $task->getKey(), $updatedTaskData);

        // Assert
        $response->assertSuccessful();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Task to be updated',
            'status' => TaskStatus::Completed->value,
            'priority' => TaskPriority::High->value,
        ]);
    }

    public function test_update_with_full_parameters(): void
    {
        // Arrange
        $tag1 = Tag::factory()->create(['name' => 'tag1', 'color' => '#ff0000']);
        $tag2 = Tag::factory()->create(['name' => 'tag2', 'color' => '#00ff00']);
        $tag3 = Tag::factory()->create(['name' => 'tag3', 'color' => '#0000ff']);
        $task = Task::factory()->create([
            'user_id' => $this->user->getKey(),
        ]);
        $assignee = User::factory()->create();
        $taskData = [
            'title' => 'Test task',
            'description' => 'Test description',
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
            'due_date' => now()->addDays(5)->toDateString(),
            'assignee_id' => $assignee->getKey(),
            'tags' => ['tag1', 'tag3'],
            'metadata' => [
                'foo' => 'bar',
                'goo' => 'baz',
            ],
        ];

        // Act
        $response = $this->actingAs($this->user)->putJson('tasks/' . $task->getKey(), $taskData);

        // Assert
        $response->assertSuccessful()->assertJson([
            'data' => [
                'user' => [
                    'id' => $this->user->getKey(),
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'email_verified_at' => $this->user->email_verified_at->toRfc3339String(),
                    'avatar' => $this->user->avatar,
                    'is_admin' => $this->user->is_admin ?? false,
                ],
                'title' => 'Test task',
                'description' => 'Test description',
                'status' => TaskStatus::InProgress->value,
                'priority' => TaskPriority::High->value,
                'due_date' => now()->addDays(5)->toDateString(),
                'assignee' => [
                    'id' => $assignee->getKey(),
                    'name' => $assignee->name,
                    'email' => $assignee->email,
                    'email_verified_at' => $assignee->email_verified_at->toRfc3339String(),
                    'avatar' => $assignee->avatar,
                    'is_admin' => $assignee->is_admin ?? false,
                ],
                'tags' => [
                    [
                        'name' => 'tag1',
                        'color' => '#ff0000',
                    ],
                    [
                        'name' => 'tag3',
                        'color' => '#0000ff',
                    ]
                ],
                'metadata' => [
                    'foo' => 'bar',
                    'goo' => 'baz',
                ]
            ],
        ]);

        $this->assertDatabaseCount('tasks', 1);
        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'title' => 'Test task',
            'description' => 'Test description',
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
            'due_date' => now()->addDays(5)->toDateString(),
            'assignee_id' => $assignee->getKey(),
            'metadata->foo' => 'bar',
            'metadata->goo' => 'baz',
        ]);
        $task = Task::query()->firstWhere('title', 'Test task');
        $this->assertDatabaseHas('tag_task', [
            'task_id' => $task->getKey(),
            'tag_id' => $tag1->getKey(),
        ]);
        $this->assertDatabaseMissing('tag_task', [
            'task_id' => $task->getKey(),
            'tag_id' => $tag2->getKey(),
        ]);
        $this->assertDatabaseHas('tag_task', [
            'task_id' => $task->getKey(),
            'tag_id' => $tag3->getKey(),
        ]);
    }

    public function test_delete(): void
    {
        // Arrange
        $task = Task::factory()->create([
            'title' => 'Task to be deleted',
            'user_id' => $this->user->id,
        ]);
        $task2 = Task::factory()->create([
            'title' => 'Task to be deleted by admin',
            'user_id' => $this->user->id,
        ]);
        $otherUser = User::factory()->create();

        // Act
        $this->deleteJson('tasks/' . $task->getKey())->assertUnauthorized();
        $this->actingAs($otherUser)->putJson('tasks/' . $task->getKey())->assertForbidden();
        $this->actingAs($this->admin)->deleteJson('tasks/' . $task2->getKey())->assertNoContent();
        $response = $this->actingAs($this->user)->deleteJson('tasks/' . $task->getKey());

        // Assert
        $response->assertNoContent();

        $this->assertSoftDeleted('tasks', [
            'title' => 'Task to be deleted',
        ]);
        $this->assertSoftDeleted('tasks', [
            'title' => 'Task to be deleted by admin',
        ]);
    }
}
