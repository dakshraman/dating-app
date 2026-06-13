<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only([
            'name', 'bio', 'gender', 'birth_date',
            'location', 'latitude', 'longitude', 'profile_photo',
        ]));

        return response()->json($user->fresh()->load(['photos', 'preferences', 'interests']));
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'gender_preference' => 'nullable|string|in:male,female,other',
            'min_age' => 'integer|min:18|max:99',
            'max_age' => 'integer|min:18|max:99',
            'max_distance' => 'integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $preferences = $user->preferences()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['gender_preference', 'min_age', 'max_age', 'max_distance'])
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
            'order' => $request->user()->photos()->count(),
        ]);

        if ($request->is_primary) {
            $request->user()->photos()
                ->where('id', '!=', $photo->id)
                ->update(['is_primary' => false]);
        }

        return response()->json($photo, 201);
    }

    public function deletePhoto(Request $request, $id): JsonResponse
    {
        $photo = $request->user()->photos()->findOrFail($id);
        $photo->delete();

        return response()->json(['message' => 'Photo deleted']);
    }

    public function updateInterests(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'interests' => 'required|array',
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

        return response()->json($request->user()->interests);
    }

    public function discover(Request $request): JsonResponse
    {
        $user = $request->user();
        $profiles = User::discoverable($user)
            ->with(['photos', 'interests'])
            ->take(20)
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'age' => $profile->age(),
                    'bio' => $profile->bio,
                    'location' => $profile->location,
                    'profile_photo' => $profile->profile_photo,
                    'photos' => $profile->photos,
                    'interests' => $profile->interests,
                ];
            });

        return response()->json($profiles);
    }

    public function show($id): JsonResponse
    {
        $profile = User::with(['photos', 'interests'])->findOrFail($id);

        return response()->json([
            'id' => $profile->id,
            'name' => $profile->name,
            'age' => $profile->age(),
            'bio' => $profile->bio,
            'location' => $profile->location,
            'profile_photo' => $profile->profile_photo,
            'photos' => $profile->photos,
            'interests' => $profile->interests,
        ]);
    }
}
