<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Interest;
use App\Models\ProfilePrompt;
use App\Models\ProfileVisit;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:500',
            'gender' => 'sometimes|string|in:male,female,other',
            'birth_date' => 'sometimes|date',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'profile_photo' => 'nullable|string',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'mother_tongue' => 'nullable|string|max:255',
            'dietary_preference' => 'nullable|string|in:Vegetarian,Non-Vegetarian,Eggetarian,Vegan',
            'education' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'income_range' => 'nullable|string|max:255',
            'mask_name' => 'sometimes|boolean',
            'incognito_mode' => 'sometimes|boolean',
            'travel_latitude' => 'nullable|numeric',
            'travel_longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only([
            'name', 'bio', 'gender', 'birth_date',
            'location', 'latitude', 'longitude', 'profile_photo',
            'state', 'city', 'religion', 'mother_tongue',
            'dietary_preference', 'education', 'profession', 'income_range',
            'mask_name', 'incognito_mode', 'travel_latitude', 'travel_longitude'
        ]));

        return response()->json($user->fresh()->load(['photos', 'preferences', 'interests', 'prompts']));
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'gender_preference' => 'nullable|string|in:male,female,other',
            'min_age' => 'integer|min:15|max:99',
            'max_age' => 'integer|min:15|max:99',
            'max_distance' => 'nullable|integer|min:1',
            'religion_preference' => 'nullable|string|max:255',
            'mother_tongue_preference' => 'nullable|string|max:255',
            'dietary_preference' => 'nullable|string|in:Vegetarian,Non-Vegetarian,Eggetarian,Vegan',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('min_age') && $request->filled('max_age') && $request->min_age > $request->max_age) {
                $validator->errors()->add('min_age', 'Minimum age cannot be greater than maximum age.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $preferences = $user->preferences()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'gender_preference', 'min_age', 'max_age', 'max_distance',
                'religion_preference', 'mother_tongue_preference', 'dietary_preference',
            ])
        );

        return response()->json($preferences);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo_url' => 'required|string',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photo = $request->user()->photos()->create([
            'photo_url' => $request->photo_url,
            'is_primary' => $request->is_primary ?? false,
            'is_approved' => false,
            'order' => $request->user()->photos()->count(),
        ]);

        if ($request->is_primary) {
            $request->user()->photos()
                ->where('id', '!=', $photo->id)
                ->update(['is_primary' => false]);
        }

        return response()->json($photo, 201);
    }

    public function deletePhoto(Request $request, int $id): JsonResponse
    {
        $photo = $request->user()->photos()->findOrFail($id);
        $photo->delete();

        return response()->json(['message' => 'Photo deleted']);
    }

    public function getInterests(): JsonResponse
    {
        return response()->json(Interest::orderBy('name')->get());
    }

    public function updateInterests(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'interests' => 'present|array',
            'interests.*' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $interestIds = [];
        foreach ($request->interests as $interestName) {
            $interest = Interest::firstOrCreate(
                ['name' => strtolower($interestName)]
            );
            $interestIds[] = $interest->id;
        }

        $request->user()->interests()->sync($interestIds);

        return response()->json($request->user()->interests()->get());
    }

    public function updatePrompts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompts' => 'present|array|max:3',
            'prompts.*.prompt' => 'required|string|max:200',
            'prompts.*.answer' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request) {
            $request->user()->prompts()->delete();

            foreach ($request->prompts as $index => $data) {
                ProfilePrompt::create([
                    'user_id' => $request->user()->id,
                    'prompt' => $data['prompt'],
                    'answer' => $data['answer'],
                    'order' => $index,
                ]);
            }
        });

        return response()->json($request->user()->prompts()->get());
    }

    public function visitors(Request $request): JsonResponse
    {
        $visits = ProfileVisit::where('visited_id', $request->user()->id)
            ->with('visitor:id,name,profile_photo,bio,birth_date')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($visit) => [
                'id' => $visit->visitor->id,
                'name' => $visit->visitor->name,
                'profile_photo' => $visit->visitor->profile_photo,
                'bio' => $visit->visitor->bio,
                'age' => $visit->visitor->age(),
                'visited_at' => $visit->created_at,
            ]);

        return response()->json($visits);
    }

    public function discover(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = User::discoverable($user)
            ->with(['photos' => fn ($q) => $q->where('is_approved', true), 'interests', 'prompts'])
            ->withExists(['profileBoosts as is_boosted' => function ($q) {
                $q->where('is_active', true)
                    ->where('started_at', '<=', now())
                    ->where('expires_at', '>=', now());
            }]);

        $preferences = $user->preferences;

        if ($preferences) {
            if ($preferences->min_age) {
                $minDate = now()->subYears($preferences->max_age ?? 99)->format('Y-m-d');
                $maxDate = now()->subYears($preferences->min_age)->format('Y-m-d');
                $query->whereBetween('birth_date', [$minDate, $maxDate]);
            }

            $searchLat = $user->travel_latitude ?? $user->latitude;
            $searchLng = $user->travel_longitude ?? $user->longitude;

            if ($preferences->max_distance && $searchLat && $searchLng) {
                $haversine = '(6371 * acos(case when (cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))) > 1 then 1 else (cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))) end))';
                $query->whereRaw("{$haversine} <= ?", array_merge(
                    [$searchLat, $searchLng, $searchLat, $searchLat, $searchLng, $searchLat],
                    [$preferences->max_distance]
                ));
            }
        }

        $query->orderByDesc('created_at');

        $profiles = $query->cursorPaginate(20);

        $mapped = collect($profiles->items())->map(function ($profile) use ($user) {
            return [
                'id' => $profile->id,
                'name' => $profile->name,
                'age' => $profile->age(),
                'gender' => $profile->gender,
                'bio' => $profile->bio,
                'location' => $profile->location,
                'state' => $profile->state,
                'city' => $profile->city,
                'profile_photo' => $profile->profile_photo,
                'is_verified' => $profile->is_verified,
                'mask_name' => $profile->mask_name,
                'religion' => $profile->religion,
                'mother_tongue' => $profile->mother_tongue,
                'dietary_preference' => $profile->dietary_preference,
                'education' => $profile->education,
                'profession' => $profile->profession,
                'income_range' => $profile->income_range,
                'photos' => $profile->photos,
                'interests' => $profile->interests,
                'is_boosted' => $profile->is_boosted,
                'last_active_at' => $profile->last_active_at,
                'prompts' => $profile->prompts,
                'compatibility' => $user->compatibilityWith($profile),
            ];
        })->sortByDesc('is_boosted')->values()->all();

        return response()->json([
            'data' => $mapped,
            'next_cursor' => $profiles->nextCursor()?->encode(),
            'has_more' => $profiles->hasMorePages(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $profile = User::with([
            'photos' => fn ($q) => $q->where('is_approved', true),
            'interests',
            'prompts',
        ])->findOrFail($id);

        $user = $request->user();

        if ($user && $user->id !== $profile->id) {
            ProfileVisit::firstOrCreate([
                'visitor_id' => $user->id,
                'visited_id' => $profile->id,
            ]);
        }

        $compatibility = $user
            ? $user->compatibilityWith($profile)
            : null;

        return response()->json([
            'id' => $profile->id,
            'name' => $profile->name,
            'age' => $profile->age(),
            'gender' => $profile->gender,
            'bio' => $profile->bio,
            'location' => $profile->location,
            'state' => $profile->state,
            'city' => $profile->city,
            'profile_photo' => $profile->profile_photo,
            'is_verified' => $profile->is_verified,
            'mask_name' => $profile->mask_name,
            'religion' => $profile->religion,
            'mother_tongue' => $profile->mother_tongue,
            'dietary_preference' => $profile->dietary_preference,
            'education' => $profile->education,
            'profession' => $profile->profession,
            'income_range' => $profile->income_range,
            'photos' => $profile->photos,
            'interests' => $profile->interests,
            'prompts' => $profile->prompts,
            'compatibility' => $compatibility,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->photos()->delete();
            $user->preferences()->delete();
            $user->prompts()->delete();
            $user->sentSwipes()->delete();
            $user->receivedSwipes()->delete();
            $user->reports()->delete();
            $user->blockedUsers()->detach();
            $user->blockedByUsers()->detach();
            $user->profileBoosts()->delete();
            $user->dailySwipeUsage()->delete();

            ProfileVisit::where('visitor_id', $user->id)
                ->orWhere('visited_id', $user->id)
                ->delete();

            UserSubscription::where('user_id', $user->id)->delete();

            UserMatch::where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id)
                ->delete();

            Conversation::where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id)
                ->delete();

            $user->delete();
        });

        return response()->json(['message' => 'Account deleted successfully']);
    }
}
