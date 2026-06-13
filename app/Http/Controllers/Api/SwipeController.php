<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMatch;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\DailySwipeUsage;
use App\Models\Swipe;
use App\Models\UserMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SwipeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'swiped_id' => 'required|exists:users,id',
            'direction' => 'required|in:like,nope',
            'is_super_like' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if ($user->id === (int) $request->swiped_id) {
            return response()->json(['message' => 'Cannot swipe on yourself'], 422);
        }

        $isSuperLike = $request->boolean('is_super_like');

        if ($isSuperLike) {
            $remaining = $user->getRemainingSuperLikes();
            if ($remaining <= 0) {
                return response()->json(['message' => 'No super likes remaining'], 429);
            }
        } elseif ($request->direction === 'like') {
            $remaining = $user->getRemainingSwipes();
            if ($remaining <= 0) {
                return response()->json(['message' => 'No swipes remaining. Upgrade to Premium!'], 429);
            }
        }

        $swipe = Swipe::updateOrCreate(
            ['swiper_id' => $user->id, 'swiped_id' => $request->swiped_id],
            [
                'direction' => $request->direction,
                'is_super_like' => $isSuperLike,
            ]
        );

        $usage = DailySwipeUsage::firstOrCreate(
            ['user_id' => $user->id, 'date' => today()],
            ['count' => 0, 'super_like_count' => 0],
        );

        if ($isSuperLike) {
            $usage->increment('super_like_count');
        } elseif ($request->direction === 'like') {
            $usage->increment('count');
        }

        $matched = false;
        $match = null;

        if ($request->direction === 'like') {
            $reciprocalSwipe = Swipe::where('swiper_id', $request->swiped_id)
                ->where('swiped_id', $user->id)
                ->where('direction', 'like')
                ->first();

            if ($reciprocalSwipe) {
                $matched = true;

                $user1Id = min($user->id, (int) $request->swiped_id);
                $user2Id = max($user->id, (int) $request->swiped_id);

                $match = UserMatch::firstOrCreate(
                    ['user1_id' => $user1Id, 'user2_id' => $user2Id],
                    ['matched_at' => now()]
                );

                Conversation::firstOrCreate(
                    ['match_id' => $match->id],
                    ['user1_id' => $user1Id, 'user2_id' => $user2Id]
                );

                $match->load(['user1', 'user2']);
                broadcast(new NewMatch($match));
            }
        }

        return response()->json([
            'swipe' => $swipe,
            'matched' => $matched,
            'match' => $match,
        ]);
    }

    public function undo(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasActiveSubscription()) {
            return response()->json(['message' => 'Premium feature. Upgrade to undo swipes.'], 403);
        }

        $lastSwipe = Swipe::where('swiper_id', $user->id)
            ->latest()
            ->first();

        if (! $lastSwipe) {
            return response()->json(['message' => 'No swipe to undo'], 404);
        }

        $lastSwipe->delete();

        $usage = DailySwipeUsage::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        if ($usage) {
            if ($usage->count > 0) {
                $usage->decrement('count');
            }
            if ($usage->super_like_count > 0) {
                $usage->decrement('super_like_count');
            }
        }

        return response()->json(['message' => 'Last swipe undone']);
    }

    public function likesReceived(Request $request): JsonResponse
    {
        $user = $request->user();

        $likes = Swipe::where('swiped_id', $user->id)
            ->where('direction', 'like')
            ->with('swiper:id,name,profile_photo,bio,birth_date')
            ->latest()
            ->get();

        if (! $user->hasActiveSubscription()) {
            $totalLikes = $likes->count();
            $recentLikes = $likes->take(1)->map(function ($swipe) {
                return [
                    'id' => $swipe->swiper_id,
                    'name' => $swipe->swiper->name,
                    'profile_photo' => $swipe->swiper->profile_photo,
                    'is_super_like' => $swipe->is_super_like,
                ];
            });

            return response()->json([
                'total_likes' => $totalLikes,
                'premium_required' => true,
                'message' => 'Upgrade to Premium to see who liked you',
                'recent_preview' => $recentLikes,
            ]);
        }

        return response()->json(
            $likes->map(function ($swipe) {
                return [
                    'id' => $swipe->swiper_id,
                    'name' => $swipe->swiper->name,
                    'profile_photo' => $swipe->swiper->profile_photo,
                    'bio' => $swipe->swiper->bio,
                    'age' => $swipe->swiper->age(),
                    'is_super_like' => $swipe->is_super_like,
                    'swiped_at' => $swipe->created_at,
                ];
            })
        );
    }

    public function matches(Request $request): JsonResponse
    {
        $user = $request->user();

        $matches = UserMatch::where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id);
        })
            ->whereNull('expires_at')
            ->with(['user1', 'user2', 'conversation'])
            ->orderBy('matched_at', 'desc')
            ->get()
            ->map(function ($match) use ($user) {
                $other = $match->getOtherUser($user);

                return [
                    'id' => $match->id,
                    'matched_at' => $match->matched_at,
                    'user' => [
                        'id' => $other->id,
                        'name' => $other->name,
                        'profile_photo' => $other->profile_photo,
                    ],
                    'conversation_id' => $match->conversation?->id,
                ];
            });

        return response()->json($matches);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $match = UserMatch::where('id', $id)
            ->where(function ($q) use ($request) {
                $q->where('user1_id', $request->user()->id)
                    ->orWhere('user2_id', $request->user()->id);
            })
            ->firstOrFail();

        $match->delete();

        return response()->json(['message' => 'Unmatched']);
    }
}
