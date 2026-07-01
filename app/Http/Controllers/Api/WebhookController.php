<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function revenuecat(Request $request)
    {
        $secret = config('services.revenuecat.webhook_secret');
        if ($secret) {
            $signature = $request->header('Authorization');
            $payload = $request->getContent();

            if (! $signature || ! hash_equals(
                hash_hmac('sha256', $payload, $secret),
                $signature
            )) {
                Log::warning('RevenueCat webhook: Invalid signature');

                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        $event = $request->input('event');

        if (! $event) {
            return response()->json(['message' => 'No event payload'], 400);
        }

        $type = $event['type'] ?? '';
        $appUserId = $event['app_user_id'] ?? null;
        $expirationAtMs = $event['expiration_at_ms'] ?? null;

        if (! $appUserId) {
            return response()->json(['message' => 'Missing app_user_id'], 400);
        }

        $user = User::find($appUserId);
        if (! $user) {
            Log::warning("RevenueCat webhook: User $appUserId not found.");

            return response()->json(['message' => 'User not found'], 404);
        }

        // We assume 'Premium' entitlement or just any subscription triggers this
        $expirationDate = $expirationAtMs
            ? Carbon::createFromTimestampMs($expirationAtMs)
            : Carbon::now()->addMonth();

        if (in_array($type, ['INITIAL_PURCHASE', 'RENEWAL'])) {
            UserSubscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'subscription_plan_id' => 1, // Fallback plan ID
                    'starts_at' => Carbon::now(),
                    'ends_at' => $expirationDate,
                    'is_active' => true,
                ]
            );
            Log::info("RevenueCat: Subscription granted/renewed for user {$user->id}");
        } elseif (in_array($type, ['CANCELLATION', 'EXPIRATION'])) {
            UserSubscription::where('user_id', $user->id)->update([
                'is_active' => false,
                'ends_at' => Carbon::now(),
            ]);
            Log::info("RevenueCat: Subscription expired/cancelled for user {$user->id}");
        }

        return response()->json(['success' => true]);
    }
}
