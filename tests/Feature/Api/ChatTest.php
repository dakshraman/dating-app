<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_get_conversations(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $match = UserMatch::create([
            'user1_id' => min($user->id, $other->id),
            'user2_id' => max($user->id, $other->id),
            'matched_at' => now(),
        ]);

        Conversation::create([
            'match_id' => $match->id,
            'user1_id' => $match->user1_id,
            'user2_id' => $match->user2_id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/conversations');

        $response->assertStatus(200);
    }

    public function test_user_can_send_message(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $match = UserMatch::create([
            'user1_id' => min($user->id, $other->id),
            'user2_id' => max($user->id, $other->id),
            'matched_at' => now(),
        ]);

        $conversation = Conversation::create([
            'match_id' => $match->id,
            'user1_id' => $match->user1_id,
            'user2_id' => $match->user2_id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/conversations/{$conversation->id}/messages", [
            'content' => 'Hello there!',
        ]);

        $response->assertStatus(201);
    }

    public function test_user_can_block_another_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/block', [
            'user_id' => $target->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_report_another_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/report', [
            'user_id' => $target->id,
            'reason' => 'Inappropriate behavior',
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_update_last_seen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/last-seen');

        $response->assertStatus(200);
    }
}
