<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use App\Models\Message;
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

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_deleted_conversation_is_hidden(): void
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
            'user1_deleted_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/conversations');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_user_can_delete_conversation(): void
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

        $response = $this->actingAs($user)->deleteJson("/api/conversations/{$conversation->id}");

        $response->assertStatus(200);

        $this->assertNotNull($conversation->fresh()->user1_deleted_at);
    }

    public function test_sending_message_restores_deleted_conversation(): void
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
            'user1_deleted_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson("/api/conversations/{$conversation->id}/messages", [
            'content' => 'New message after delete',
        ]);

        $response->assertStatus(201);
        $this->assertNull($conversation->fresh()->user1_deleted_at);
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

    public function test_user_cannot_send_message_when_blocked_by_recipient(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $other->blockedUsers()->attach($user->id);

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
            'content' => 'Can you see this?',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_send_message_when_they_blocked_recipient(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $user->blockedUsers()->attach($other->id);

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
            'content' => 'Can you see this?',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_block_another_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/block', [
            'user_id' => $target->id,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($user->blockedUsers()->where('blocked_id', $target->id)->exists());
    }

    public function test_user_cannot_block_self(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/block', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_unblock_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $user->blockedUsers()->attach($target->id);

        $response = $this->actingAs($user)->deleteJson("/api/user/block/{$target->id}");

        $response->assertStatus(200);
        $this->assertFalse($user->blockedUsers()->where('blocked_id', $target->id)->exists());
    }

    public function test_user_can_get_blocked_users(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $user->blockedUsers()->attach($target->id);

        $response = $this->actingAs($user)->getJson('/api/user/blocks');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $target->id, 'name' => $target->name]);
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

    public function test_user_can_typing(): void
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

        $response = $this->actingAs($user)->postJson("/api/conversations/{$conversation->id}/typing");

        $response->assertStatus(200);
    }

    public function test_user_can_stop_typing(): void
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

        $response = $this->actingAs($user)->deleteJson("/api/conversations/{$conversation->id}/typing");

        $response->assertStatus(200);
    }

    public function test_user_can_get_messages(): void
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

        $conversation->messages()->create([
            'sender_id' => $other->id,
            'content' => 'Hello!',
            'type' => 'text',
        ]);

        $response = $this->actingAs($user)->getJson("/api/conversations/{$conversation->id}/messages");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_user_cannot_get_messages_when_blocked(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $other->blockedUsers()->attach($user->id);

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

        $response = $this->actingAs($user)->getJson("/api/conversations/{$conversation->id}/messages");

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_message(): void
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

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'content' => 'Delete me',
            'type' => 'text',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/conversations/{$conversation->id}/messages/{$message->id}");

        $response->assertStatus(200);
        $this->assertNull(Message::find($message->id));
    }

    public function test_forbidden_user_cannot_access_conversation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $stranger = User::factory()->create();

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

        $response = $this->actingAs($stranger)->getJson("/api/conversations/{$conversation->id}/messages");

        $response->assertStatus(403);
    }
}
