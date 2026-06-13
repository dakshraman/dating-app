<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'gender' => 'male',
            'birth_date' => '1995-01-01',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('user_preferences', ['user_id' => $response['user']['id']]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson(['id' => $user->id]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertCount(0, $user->tokens);
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}
