<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::create([
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Basic dating experience with daily limits',
            'price' => 0,
            'duration_days' => 0,
            'features' => [
                'daily_swipes' => 10,
                'super_likes' => 3,
                'boosts' => 0,
                'see_who_likes_you' => false,
                'read_receipts' => false,
            ],
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Unlimited swipes, super likes, boosts, and more!',
            'price' => 19.99,
            'duration_days' => 30,
            'features' => [
                'daily_swipes' => -1,
                'super_likes' => -1,
                'boosts' => 5,
                'see_who_likes_you' => true,
                'read_receipts' => true,
            ],
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Premium Plus',
            'slug' => 'premium-plus',
            'description' => 'Everything in Premium plus priority visibility',
            'price' => 34.99,
            'duration_days' => 30,
            'features' => [
                'daily_swipes' => -1,
                'super_likes' => -1,
                'boosts' => 10,
                'see_who_likes_you' => true,
                'read_receipts' => true,
                'priority_visibility' => true,
            ],
            'is_active' => true,
        ]);
    }
}
