<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use DevAdamlar\LaravelOidc\Testing\ActingAs;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use ActingAs;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'id' => '6daab073-63fd-4d0d-b503-d2901af4f56a',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_request_to_public_endpoint_gives_successful(): void
    {
        $response = $this->getJson('/api/public');

        $response->assertStatus(200);
    }
    public function test_request_to_protected_endpoint_without_token_gives_unauthorized(): void
    {
        $response = $this->getJson('/api/protected');

        $response->assertStatus(401);
    }
    public function test_request_to_protected_endpoint_with_token_gives_successful(): void
    {
        // Arrange
        $user = User::query()->first();

        // Act
        $response = $this->actingAs($user)->getJson('/api/protected');

        // Assert
        $response->assertStatus(200);
    }
}
