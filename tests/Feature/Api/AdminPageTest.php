<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Database\Seeders\TestDataSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SubscriptionPlanSeeder::class);
        $this->seed(TestDataSeeder::class);
    }

    public function test_admin_pages_load(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'web');

        $pages = [
            '/admin' => 'Dashboard',
            '/admin/users' => 'Users list',
            '/admin/matches/user-matches' => 'Matches',
            '/admin/conversations' => 'Conversations',
            '/admin/reports' => 'Reports',
            '/admin/interests' => 'Interests',
            '/admin/subscription-plans' => 'Subscription plans',
            '/admin/user-photos' => 'User photos',
            '/admin/profile-boosts' => 'Profile boosts',
            '/admin/user-subscriptions' => 'User subscriptions',
            '/admin/dating-settings' => 'Dating settings',
            '/admin/service-status' => 'Service status',
        ];

        foreach ($pages as $path => $label) {
            $response = $this->get($path);
            $response->assertStatus(200);
        }
    }

    public function test_admin_user_edit_page_loads(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'web');

        $response = $this->get('/admin/users/1/edit');
        $response->assertStatus(200);
    }

    public function test_admin_conversation_edit_page_loads(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'web');

        $response = $this->get('/admin/conversations/1/edit');
        $response->assertStatus(200);
    }
}
