<?php

namespace Tests\Feature\Api;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_profile_visitors_are_tracked(): void
    {
        $user = User::factory()->create([
            'gender' => 'male',
            'birth_date' => '1995-01-01',
            'profile_photo' => 'https://example.com/photo.jpg',
            'is_active' => true,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        $visitor = User::factory()->create([
            'gender' => 'female',
            'birth_date' => '1995-01-01',
            'profile_photo' => 'https://example.com/photo.jpg',
            'is_active' => true,
        ]);

        $this->actingAs($visitor)->getJson("/api/profiles/{$user->id}");

        $response = $this->actingAs($user)->getJson('/api/profile/visitors');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals($visitor->id, $response->json()[0]['id']);
    }

    public function test_compatibility_score_between_users(): void
    {
        $music = Interest::create(['name' => 'music']);
        $travel = Interest::create(['name' => 'travel']);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->interests()->sync([$music->id, $travel->id]);
        $user2->interests()->sync([$music->id]);

        $score = $user1->compatibilityWith($user2);

        $this->assertEquals(50, $score);
    }

    public function test_icebreaker_prompts_on_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/profile/prompts', [
            'prompts' => [
                ['prompt' => 'My ideal weekend', 'answer' => 'Hiking and coffee'],
            ],
        ]);

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('prompts'));
    }

    public function test_banned_user_is_blocked(): void
    {
        $user = User::factory()->create(['is_banned' => true, 'ban_reason' => 'Spam']);

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(403)
            ->assertJsonPath('reason', 'Spam');
    }

    public function test_full_swipe_match_and_chat_flow(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1)->postJson('/api/swipe', [
            'swiped_id' => $user2->id,
            'direction' => 'like',
        ]);

        $response = $this->actingAs($user2)->postJson('/api/swipe', [
            'swiped_id' => $user1->id,
            'direction' => 'like',
        ]);

        $response->assertStatus(200)->assertJsonPath('matched', true);

        $matchId = $response->json('match.id');

        $matchesResponse = $this->actingAs($user1)->getJson('/api/matches');
        $this->assertCount(1, $matchesResponse->json());

        $conversationId = $matchesResponse->json()[0]['conversation_id'];

        $this->actingAs($user1)->postJson("/api/conversations/{$conversationId}/messages", [
            'content' => 'Hey, we matched!',
        ]);

        $messagesResponse = $this->actingAs($user2)->getJson("/api/conversations/{$conversationId}/messages");
        $messagesResponse->assertStatus(200);
        $this->assertCount(1, $messagesResponse->json());
    }
}
