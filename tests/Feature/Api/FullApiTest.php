<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 19.99,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    public function test_all_api_routes()
    {
        // Create users directly with factories
        $user1 = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'phone' => '1234567890',
            'gender' => 'male',
            'birth_date' => '1995-01-01',
        ]);
        $user1->preferences()->create([
            'gender_preference' => 'female',
            'min_age' => 18,
            'max_age' => 99,
            'max_distance' => 500,
        ]);

        $user2 = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
            'phone' => '9876543210',
            'gender' => 'female',
            'birth_date' => '1998-05-15',
        ]);
        $user2->preferences()->create([
            'gender_preference' => 'male',
            'min_age' => 18,
            'max_age' => 99,
            'max_distance' => 500,
        ]);

        // Create Sanctum tokens
        $token1 = $user1->createToken('test')->plainTextToken;
        $token2 = $user2->createToken('test')->plainTextToken;

        // ===== AUTH: LOGIN =====
        $res = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $this->assertEquals(200, $res->status(), 'LOGIN');

        $res = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);
        $this->assertEquals(401, $res->status(), 'LOGIN bad password');

        // ===== AUTH: GET USER =====
        $res = $this->actingAs($user1)->getJson('/api/user');
        $this->assertEquals(200, $res->status(), 'GET USER');
        $this->assertEquals('Test User', $res->json('name'));

        // ===== PROFILE: UPDATE =====
        $res = $this->putJson('/api/profile', [
            'name' => 'Test Updated',
            'bio' => 'Hello world!',
            'location' => 'Mumbai',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ]);
        $this->assertEquals(200, $res->status(), 'UPDATE PROFILE');
        $this->assertEquals('Test Updated', $res->json('name'));

        // ===== PROFILE: PREFERENCES =====
        $res = $this->putJson('/api/profile/preferences', [
            'gender_preference' => 'female',
            'min_age' => 20,
            'max_age' => 40,
            'max_distance' => 100,
        ]);
        $this->assertEquals(200, $res->status(), 'UPDATE PREFS');

        // ===== PROFILE: PHOTOS =====
        $res = $this->postJson('/api/profile/photos', [
            'photo_url' => 'https://example.com/photo1.jpg',
            'is_primary' => true,
        ]);
        $this->assertEquals(201, $res->status(), 'ADD PHOTO');
        $photoId = $res->json('id');

        $res = $this->postJson('/api/profile/photos', [
            'photo_url' => 'https://example.com/photo2.jpg',
        ]);
        $this->assertEquals(201, $res->status(), 'ADD PHOTO 2');

        // ===== PROFILE: INTERESTS =====
        $res = $this->putJson('/api/profile/interests', [
            'interests' => ['music', 'travel', 'photography'],
        ]);
        $this->assertEquals(200, $res->status(), 'UPDATE INTERESTS');
        $this->assertCount(3, $res->json());

        // ===== PROFILE: PROMPTS =====
        $res = $this->putJson('/api/profile/prompts', [
            'prompts' => [
                ['prompt' => 'My ideal weekend', 'answer' => 'Hiking and coffee'],
                ['prompt' => 'Two truths and a lie', 'answer' => 'I speak 3 languages'],
            ],
        ]);
        $this->assertEquals(200, $res->status(), 'UPDATE PROMPTS');

        // ===== PROFILE: VIEW OWN =====
        $res = $this->getJson('/api/user');
        $this->assertEquals(200, $res->status());
        $this->assertNotNull($res->json('photos'));

        // ===== DISCOVER =====
        $res = $this->getJson('/api/discover');
        $this->assertEquals(200, $res->status(), 'DISCOVER');
        $this->assertIsArray($res->json('data'));

        // ===== SWIPE: LIKE =====
        $res = $this->postJson('/api/swipe', [
            'swiped_id' => $user2->id,
            'direction' => 'like',
        ]);
        $this->assertEquals(200, $res->status(), 'SWIPE LIKE');
        $this->assertFalse($res->json('matched'));

        // ===== SWIPE: MUTUAL MATCH =====
        $this->actingAs($user2);
        $res = $this->postJson('/api/swipe', [
            'swiped_id' => $user1->id,
            'direction' => 'like',
        ]);
        $this->assertEquals(200, $res->status(), 'SWIPE2 LIKE');
        $this->assertTrue($res->json('matched'), 'Should match now!');

        // ===== MATCHES =====
        $this->actingAs($user1);
        $res = $this->getJson('/api/matches');
        $this->assertEquals(200, $res->status(), 'MATCHES');
        $this->assertGreaterThanOrEqual(1, count($res->json()));
        $matchId = $res->json('0.id');
        $convId = $res->json('0.conversation_id');

        // ===== LIKES RECEIVED =====
        $this->actingAs($user2);
        $res = $this->getJson('/api/likes-received');
        $this->assertEquals(200, $res->status(), 'LIKES RECEIVED');

        // ===== LIKES/ME =====
        $this->actingAs($user1);
        $res = $this->getJson('/api/likes/me');
        $this->assertEquals(200, $res->status(), 'LIKES/ME');

        // ===== SWIPE REMAINING =====
        $res = $this->getJson('/api/swipe/remaining');
        $this->assertEquals(200, $res->status(), 'SWIPE REMAINING');
        $this->assertArrayHasKey('remaining_swipes', $res->json());

        // ===== CANNOT SWIPE ON SELF =====
        $res = $this->postJson('/api/swipe', [
            'swiped_id' => $user1->id,
            'direction' => 'like',
        ]);
        $this->assertEquals(422, $res->status(), 'SELF SWIPE');

        // ===== CHAT =====
        $this->assertNotNull($convId, 'No conversation from match');

        // Send message
        $res = $this->postJson('/api/conversations/' . $convId . '/messages', [
            'content' => 'Hey there!',
            'type' => 'text',
        ]);
        $this->assertEquals(201, $res->status(), 'SEND MSG');
        $msgId = $res->json('id');

        // Get messages
        $res = $this->getJson('/api/conversations/' . $convId . '/messages');
        $this->assertEquals(200, $res->status(), 'GET MSGS');
        $this->assertGreaterThanOrEqual(1, count($res->json()));

        // Send image (as user2)
        $this->actingAs($user2);
        $res = $this->postJson('/api/conversations/' . $convId . '/messages', [
            'content' => 'https://example.com/img.jpg',
            'type' => 'image',
        ]);
        $this->assertEquals(201, $res->status(), 'SEND IMG');

        // Mark as read (as user2)
        $res = $this->postJson('/api/conversations/' . $convId . '/read');
        $this->assertEquals(200, $res->status(), 'MARK READ');

        // Typing (as user1)
        $this->actingAs($user1);
        $res = $this->postJson('/api/conversations/' . $convId . '/typing');
        $this->assertEquals(200, $res->status(), 'TYPING');

        // React to message
        $res = $this->postJson('/api/conversations/' . $convId . '/messages/' . $msgId . '/react', [
            'metadata' => ['emoji' => '❤️'],
        ]);
        $this->assertEquals(200, $res->status(), 'REACT');

        // Delete message
        $res = $this->deleteJson('/api/conversations/' . $convId . '/messages/' . $msgId);
        $this->assertEquals(200, $res->status(), 'DELETE MSG');

        // ===== VIEW OTHER PROFILE =====
        $res = $this->getJson('/api/profiles/' . $user2->id);
        $this->assertEquals(200, $res->status(), 'VIEW PROFILE');
        $this->assertEquals('Jane Doe', $res->json('name'));

        // ===== VISITORS =====
        $this->actingAs($user2);
        $res = $this->getJson('/api/profile/visitors');
        $this->assertEquals(200, $res->status(), 'VISITORS');
        $this->assertGreaterThanOrEqual(1, count($res->json()));

        // ===== BLOCK & REPORT =====
        $this->actingAs($user1);
        $res = $this->postJson('/api/user/block', ['user_id' => $user2->id]);
        $this->assertEquals(200, $res->status(), 'BLOCK');

        $res = $this->postJson('/api/user/report', ['user_id' => $user2->id]);
        $this->assertEquals(200, $res->status(), 'REPORT');

        // ===== LAST SEEN =====
        $res = $this->postJson('/api/user/last-seen');
        $this->assertEquals(200, $res->status(), 'LAST SEEN');

        // ===== FCM TOKEN =====
        $res = $this->putJson('/api/user/fcm-token', ['fcm_token' => 'test-fcm-token-123']);
        $this->assertEquals(200, $res->status(), 'FCM TOKEN');

        $res = $this->deleteJson('/api/user/fcm-token/test-fcm-token-123');
        $this->assertEquals(200, $res->status(), 'DEL FCM');

        // ===== SUBSCRIPTIONS =====
        $res = $this->getJson('/api/subscription/plans');
        $this->assertEquals(200, $res->status(), 'SUB PLANS');
        $this->assertGreaterThanOrEqual(1, count($res->json()));

        $res = $this->getJson('/api/subscription/status');
        $this->assertEquals(200, $res->status(), 'SUB STATUS');
        $this->assertFalse($res->json('has_subscription'));

        // Purchase subscription
        $res = $this->postJson('/api/subscription/purchase', ['plan_id' => 1]);
        $this->assertEquals(200, $res->status(), 'PURCHASE');
        $this->assertTrue($res->json('subscription.is_active'));

        // Activate boost
        $res = $this->postJson('/api/profile/boost');
        $this->assertEquals(200, $res->status(), 'BOOST');
        $this->assertTrue($res->json('boost.is_active'));

        // Cannot boost twice
        $res = $this->postJson('/api/profile/boost');
        $this->assertEquals(422, $res->status(), 'DUAL BOOST');

        // Free user cannot boost
        $this->actingAs($user2);
        $res = $this->postJson('/api/profile/boost');
        $this->assertEquals(403, $res->status(), 'FREE BOOST');

        // Verification photo
        $this->actingAs($user1);
        $res = $this->postJson('/api/profile/verification-photo', ['photo' => 'https://example.com/verif.jpg']);
        $this->assertEquals(200, $res->status(), 'VERIF PHOTO');

        // ===== UNDO SWIPE =====
        $res = $this->postJson('/api/swipe/undo');
        $this->assertEquals(200, $res->status(), 'UNDO SWIPE');

        // ===== DELETE PHOTO =====
        $res = $this->deleteJson('/api/profile/photos/' . $photoId);
        $this->assertEquals(200, $res->status(), 'DEL PHOTO');

        // ===== UNMATCH =====
        $res = $this->deleteJson('/api/matches/' . $matchId);
        $this->assertEquals(200, $res->status(), 'UNMATCH');

        // ===== DELETE ACCOUNT =====
        $this->actingAs($user2);
        $res = $this->deleteJson('/api/account');
        $this->assertEquals(200, $res->status(), 'DEL ACCOUNT');
    }
}
