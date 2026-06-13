<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DatingSetting;
use App\Models\ProfileBoost;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::active()->get()->map(fn ($plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'description' => $plan->description,
            'price' => $plan->price,
            'duration_days' => $plan->duration_days,
            'features' => $plan->features,
        ]);

        return response()->json($plans);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeSubscription = UserSubscription::where('user_id', $user->id)
            ->active()
            ->with('plan')
            ->first();

        return response()->json([
            'has_subscription' => $activeSubscription !== null,
            'subscription' => $activeSubscription ? [
                'plan' => $activeSubscription->plan->name,
                'ends_at' => $activeSubscription->ends_at,
            ] : null,
            'remaining_swipes' => $user->getRemainingSwipes(),
            'remaining_super_likes' => $user->getRemainingSuperLikes(),
            'has_active_boost' => $user->hasActiveBoost(),
        ]);
    }

    public function purchase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        UserSubscription::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $subscription = UserSubscription::create([
            'user_id' => $request->user()->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_days),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Subscription activated',
            'subscription' => $subscription->load('plan'),
        ]);
    }

    public function swipeRemaining(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'remaining_swipes' => $user->getRemainingSwipes(),
            'remaining_super_likes' => $user->getRemainingSuperLikes(),
            'is_premium' => $user->hasActiveSubscription(),
        ]);
    }

    public function activateBoost(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasActiveSubscription()) {
            return response()->json(['message' => 'Subscription required to boost your profile.'], 403);
        }

        if ($user->hasActiveBoost()) {
            return response()->json(['message' => 'Already have an active boost'], 422);
        }

        $settings = DatingSetting::instance();
        $boost = ProfileBoost::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($settings->boost_duration_minutes),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Boost activated',
            'boost' => $boost,
        ]);
    }

    public function uploadVerificationPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $request->user()->update([
            'verification_photo' => $request->photo,
        ]);

        return response()->json(['message' => 'Verification photo uploaded. Pending admin review.']);
    }
}
