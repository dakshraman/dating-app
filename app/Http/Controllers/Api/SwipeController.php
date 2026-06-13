<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMatch;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->user()->id === (int) $request->swiped_id) {
            return response()->json(['message' => 'Cannot swipe on yourself'], 422);
        }

        $swipe = Swipe::updateOrCreate(
            ['swiper_id' => $request->user()->id, 'swiped_id' => $request->swiped_id],
            ['direction' => $request->direction]
        );

        $matched = false;
        $match = null;

        if ($request->direction === 'like') {
            $reciprocalSwipe = Swipe::where('swiper_id', $request->swiped_id)
                ->where('swiped_id', $request->user()->id)
                ->where('direction', 'like')
                ->first();

            if ($reciprocalSwipe) {
                $matched = true;

                $user1Id = min($request->user()->id, (int) $request->swiped_id);
                $user2Id = max($request->user()->id, (int) $request->swiped_id);

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

    public function matches(Request $request): JsonResponse
    {
        $user = $request->user();

        $matches = UserMatch::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
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
