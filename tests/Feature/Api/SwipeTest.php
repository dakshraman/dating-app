<?php

namespace Tests\Feature\Api;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SwipeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_swipe_like(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/swipe', [
            'swiped_id' => $target->id,
            'direction' => 'like',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('swipe.direction', 'like');
    }

    public function test_user_can_swipe_nope(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/swipe', [
            'swiped_id' => $target->id,
            'direction' => 'nope',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('swipe.direction', 'nope');
    }

    public function test_mutual_like_creates_match(): void
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

        $response->assertStatus(200)
            ->assertJsonPath('matched', true);
    }

    public function test_cannot_swipe_on_self(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/swipe', [
            'swiped_id' => $user->id,
            'direction' => 'like',
        ]);

        $response->assertStatus(422);
    }

    public function test_premium_user_can_undo_swipe(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $plan = SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 19.99,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $this->actingAs($user)->postJson('/api/swipe', [
            'swiped_id' => $target->id,
            'direction' => 'nope',
        ]);

        $response = $this->actingAs($user)->postJson('/api/swipe/undo');

        $response->assertStatus(200);
    }

    public function test_free_user_cannot_undo_swipe(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)->postJson('/api/swipe', [
            'swiped_id' => $target->id,
            'direction' => 'nope',
        ]);

        $response = $this->actingAs($user)->postJson('/api/swipe/undo');

        $response->assertStatus(403);
    }

    public function test_user_can_super_like(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/swipe', [
            'swiped_id' => $target->id,
            'direction' => 'like',
            'is_super_like' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('swipe.is_super_like', true);
    }

    public function test_user_can_get_matches(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/matches');

        $response->assertStatus(200);
    }

    public function test_user_can_get_likes_received(): void
    {
        $user = User::factory()->create();
        $liker = User::factory()->create();

        $this->actingAs($liker)->postJson('/api/swipe', [
            'swiped_id' => $user->id,
            'direction' => 'like',
        ]);

        $response = $this->actingAs($user)->getJson('/api/likes-received');

        $response->assertStatus(200);
    }

    public function test_user_can_see_remaining_swipes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/swipe/remaining');

        $response->assertStatus(200)
            ->assertJsonStructure(['remaining_swipes', 'remaining_super_likes', 'is_premium']);
    }
}
