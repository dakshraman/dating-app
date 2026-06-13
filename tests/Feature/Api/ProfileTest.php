<?php

namespace Tests\Feature\Api;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => 'Updated Name',
            'bio' => 'This is my bio',
            'location' => 'New York',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $user->fresh()->name);
        $this->assertEquals('This is my bio', $user->fresh()->bio);
    }

    public function test_user_can_update_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile/preferences', [
            'gender_preference' => 'female',
            'min_age' => 20,
            'max_age' => 40,
            'max_distance' => 100,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'gender_preference' => 'female',
            'max_distance' => 100,
        ]);
    }

    public function test_user_can_add_photos(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/profile/photos', [
            'photo_url' => 'https://example.com/photo.jpg',
            'is_primary' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_photos', [
            'user_id' => $user->id,
            'photo_url' => 'https://example.com/photo.jpg',
        ]);
    }

    public function test_user_can_update_interests(): void
    {
        $user = User::factory()->create();
        Interest::create(['name' => 'music']);

        $response = $this->actingAs($user)->putJson('/api/profile/interests', [
            'interests' => ['music', 'travel'],
        ]);

        $response->assertStatus(200);
        $this->assertCount(2, $user->fresh()->interests);
    }

    public function test_discover_returns_profiles(): void
    {
        $user = User::factory()->create([
            'gender' => 'male',
            'birth_date' => '1995-01-01',
            'profile_photo' => 'https://example.com/photo.jpg',
            'is_active' => true,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $other = User::factory()->create([
            'gender' => 'female',
            'birth_date' => '1998-01-01',
            'profile_photo' => 'https://example.com/photo2.jpg',
            'is_active' => true,
        ]);

        $user->preferences()->create([
            'gender_preference' => 'female',
            'min_age' => 18,
            'max_age' => 99,
        ]);

        $response = $this->actingAs($user)->getJson('/api/discover');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'next_cursor', 'has_more']);
    }

    public function test_user_can_update_prompts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile/prompts', [
            'prompts' => [
                ['prompt' => 'My ideal weekend', 'answer' => 'Hiking and coffee'],
                ['prompt' => 'Two truths and a lie', 'answer' => 'I speak 3 languages'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertCount(2, $user->fresh()->prompts);
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/api/account');

        $response->assertStatus(200);
        $this->assertModelMissing($user);
    }
}
