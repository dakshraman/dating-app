<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMatch;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\DailySwipeUsage;
use App\Models\Swipe;
use App\Models\User;
use App\Models\UserMatch;
use App\Notifications\NewMatchNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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

        if ($isSuperLike && ! $user->hasActiveSubscription() && $user->getRemainingSuperLikes() <= 0) {
            return response()->json(['message' => 'You have reached your daily super like limit.'], 422);
        }

        if ($request->direction === 'like' && ! $isSuperLike && ! $user->hasActiveSubscription() && $user->getRemainingSwipes() <= 0) {
            return response()->json(['message' => 'You have reached your daily swipe limit.'], 422);
        }

        $swipe = Swipe::updateOrCreate(
            ['swiper_id' => $user->id, 'swiped_id' => $request->swiped_id],
            [
                'direction' => $request->direction,
                'is_super_like' => $isSuperLike,
            ]
        );

        if (! $user->hasActiveSubscription()) {
            if ($isSuperLike) {
                $user->decrement('remaining_super_likes');
            } elseif ($request->direction === 'like') {
                $user->decrement('remaining_swipes');
            }
        }

        if ($request->direction === 'like') {
            $usage = DailySwipeUsage::firstOrCreate(
                ['user_id' => $user->id, 'date' => today()],
                ['count' => 0, 'super_like_count' => 0]
            );

            if ($isSuperLike) {
                $usage->increment('super_like_count');
            } else {
                $usage->increment('count');
            }
        }

        $matched = false;
        $match = null;

        if ($request->direction === 'like') {
            $user1Id = min($user->id, (int) $request->swiped_id);
            $user2Id = max($user->id, (int) $request->swiped_id);

            $reciprocalSwipe = DB::transaction(function () use ($request, $user, $user1Id, $user2Id) {
                $swipedUser = User::lockForUpdate()->findOrFail($request->swiped_id);

                $reciprocalSwipe = Swipe::where('swiper_id', $request->swiped_id)
                    ->where('swiped_id', $user->id)
                    ->where('direction', 'like')
                    ->first();

                if ($reciprocalSwipe) {
                    $match = UserMatch::firstOrCreate(
                        ['user1_id' => $user1Id, 'user2_id' => $user2Id],
                        ['matched_at' => now()]
                    );

                    Conversation::firstOrCreate(
                        ['match_id' => $match->id],
                        ['user1_id' => $user1Id, 'user2_id' => $user2Id]
                    );

                    return $reciprocalSwipe;
                }

                return null;
            });

            if ($reciprocalSwipe) {
                $matched = true;
                $match = UserMatch::where('user1_id', $user1Id)
                    ->where('user2_id', $user2Id)
                    ->with(['user1', 'user2'])
                    ->first();

                broadcast(new NewMatch($match));

                $otherUser = $match->getOtherUser($user);
                if ($otherUser && filled($otherUser->fcm_tokens)) {
                    Notification::send($otherUser, new NewMatchNotification($match));
                }
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

        $lastSwipe = Swipe::where('swiper_id', $user->id)
            ->latest()
            ->first();

        if (! $lastSwipe) {
            return response()->json(['message' => 'No swipe to undo'], 404);
        }

        $direction = $lastSwipe->direction;
        $isSuperLike = $lastSwipe->is_super_like;

        $lastSwipe->delete();

        if (! $user->hasActiveSubscription()) {
            if ($isSuperLike) {
                $user->increment('remaining_super_likes');
            } elseif ($direction === 'like') {
                $user->increment('remaining_swipes');
            }
        }

        if ($direction === 'like') {
            $usage = DailySwipeUsage::where('user_id', $user->id)
                ->whereDate('date', today())
                ->first();

            if ($usage) {
                if ($isSuperLike && $usage->super_like_count > 0) {
                    $usage->decrement('super_like_count');
                } elseif (! $isSuperLike && $usage->count > 0) {
                    $usage->decrement('count');
                }
            }
        }

        return response()->json(['message' => 'Last swipe undone']);
    }

    public function likesReceived(Request $request): JsonResponse
    {
        $user = $request->user();

        $likes = Swipe::where('swiped_id', $user->id)
            ->where('direction', 'like')
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('matches')
                    ->where(function ($q) use ($user) {
                        $q->where('user1_id', $user->id)
                            ->whereColumn('user2_id', 'swipes.swiper_id');
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('user2_id', $user->id)
                            ->whereColumn('user1_id', 'swipes.swiper_id');
                    });
            })
            ->with('swiper:id,name,profile_photo,bio,birth_date,last_active_at')
            ->latest()
            ->get();

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
                    'last_active_at' => $swipe->swiper->last_active_at,
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

    public function likesSent(Request $request): JsonResponse
    {
        $user = $request->user();

        $likes = Swipe::where('swiper_id', $user->id)
            ->where('direction', 'like')
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('matches')
                    ->where(function ($q) use ($user) {
                        $q->where('user1_id', $user->id)
                            ->whereColumn('user2_id', 'swipes.swiped_id');
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('user2_id', $user->id)
                            ->whereColumn('user1_id', 'swipes.swiped_id');
                    });
            })
            ->with('swiped:id,name,profile_photo,bio,birth_date')
            ->latest()
            ->get();

        return response()->json(
            $likes->map(function ($swipe) {
                return [
                    'id' => $swipe->swiped_id,
                    'name' => $swipe->swiped->name,
                    'profile_photo' => $swipe->swiped->profile_photo,
                    'bio' => $swipe->swiped->bio,
                    'age' => $swipe->swiped->age(),
                    'is_super_like' => $swipe->is_super_like,
                    'swiped_at' => $swipe->created_at,
                ];
            })
        );
    }

    public function destroySwipe(Request $request, $swiped_id): JsonResponse
    {
        $swipe = Swipe::where('swiper_id', $request->user()->id)
            ->where('swiped_id', $swiped_id)
            ->firstOrFail();

        $swipe->delete();

        return response()->json(['message' => 'Like withdrawn']);
    }
}
