<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'User',
            'email' => 'test@random.com',
        ]);
        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@random.com',
            'is_admin' => true,
        ]);
    }

    /**
     * A basic feature test example.
     */
    public function test_index(): void
    {
        // Arrange
        User::factory()->create([
            'name' => 'Test User',
        ]);

        // Act
        $this->getJson('users')->assertUnauthorized();
        $this->actingAs($this->admin)->getJson('users?keyword=est')->assertSuccessful();
        $response = $this->actingAs($this->user)->getJson('users?keyword=est');

        // Assert
        $response->assertSuccessful()->assertJsonCount(2, 'data')->assertJson([
            'data' => [
                [
                    'id' => $this->user->getKey(),
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'email_verified_at' => $this->user->email_verified_at->toRfc3339String(),
                    'avatar' => $this->user->avatar,
                    'is_admin' => $this->user->is_admin ?? false,
                ]
            ],
        ]);
    }
}
