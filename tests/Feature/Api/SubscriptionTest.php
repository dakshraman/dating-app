<?php

namespace Tests\Feature\Api;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_view_plans(): void
    {
        SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 19.99,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/subscription/plans');

        $response->assertStatus(200);
    }

    public function test_user_can_check_subscription_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/subscription/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'has_subscription', 'subscription',
                'remaining_swipes', 'remaining_super_likes', 'has_active_boost',
            ]);
    }

    public function test_user_can_purchase_subscription(): void
    {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 19.99,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/api/subscription/purchase', [
            'plan_id' => $plan->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('subscription.plan.name', 'Premium');
    }

    public function test_user_can_activate_boost(): void
    {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 19.99,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/api/profile/boost');

        $response->assertStatus(200)
            ->assertJsonPath('boost.is_active', true);
    }

    public function test_user_cannot_activate_two_boosts(): void
    {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 19.99,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $user->subscription()->create([
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $this->actingAs($user)->postJson('/api/profile/boost');
        $response = $this->actingAs($user)->postJson('/api/profile/boost');

        $response->assertStatus(422);
    }

    public function test_free_user_cannot_activate_boost(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/profile/boost');

        $response->assertStatus(403);
    }

    public function test_user_can_upload_verification_photo(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('verification.jpg');

        $response = $this->actingAs($user)->post('/api/profile/verification-photo', [
            'verification_photo' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['is_verified' => true]);
        $this->assertNotNull($user->fresh()->verification_photo);
        $this->assertTrue((bool) $user->fresh()->is_verified);
    }
}
