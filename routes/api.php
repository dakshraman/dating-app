<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SwipeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/auth/google/mobile', [AuthController::class, 'handleGoogleMobile']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences']);
    Route::post('/profile/photos', [ProfileController::class, 'uploadPhoto']);
    Route::delete('/profile/photos/{id}', [ProfileController::class, 'deletePhoto']);
    Route::put('/profile/interests', [ProfileController::class, 'updateInterests']);
    Route::put('/profile/prompts', [ProfileController::class, 'updatePrompts']);
    Route::get('/profile/visitors', [ProfileController::class, 'visitors']);
    Route::delete('/account', [ProfileController::class, 'destroy']);

    Route::get('/discover', [ProfileController::class, 'discover']);
    Route::get('/profiles/{id}', [ProfileController::class, 'show']);

    Route::post('/swipe', [SwipeController::class, 'store']);
    Route::get('/matches', [SwipeController::class, 'matches']);
    Route::delete('/matches/{id}', [SwipeController::class, 'destroy']);

    Route::get('/conversations', [ChatController::class, 'conversations']);
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markAsRead']);
    Route::post('/conversations/{conversation}/typing', [ChatController::class, 'typing']);

    Route::post('/chat/upload', [ChatController::class, 'uploadMedia']);
    Route::post('/user/last-seen', [ChatController::class, 'updateLastSeen']);
    Route::post('/conversations/{conversation}/messages/{message}/react', [ChatController::class, 'reactToMessage']);
    Route::delete('/conversations/{conversation}/messages/{message}', [ChatController::class, 'deleteMessage']);
    Route::post('/user/block', [ChatController::class, 'blockUser']);
    Route::post('/user/report', [ChatController::class, 'reportUser']);

    Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::post('/subscription/purchase', [SubscriptionController::class, 'purchase']);
    Route::get('/swipe/remaining', [SubscriptionController::class, 'swipeRemaining']);
    Route::post('/swipe/undo', [SwipeController::class, 'undo']);
    Route::get('/likes-received', [SwipeController::class, 'likesReceived']);
    // alias
    Route::get('/likes/me', [SwipeController::class, 'likesReceived']);
    Route::post('/profile/boost', [SubscriptionController::class, 'activateBoost']);
    Route::post('/profile/verification-photo', [SubscriptionController::class, 'uploadVerificationPhoto']);

    Route::put('/user/fcm-token', function (Request $request) {
        $request->validate(['fcm_token' => 'required|string']);
        $tokens = $request->user()->fcm_tokens ?? [];
        $token = $request->fcm_token;
        if (! in_array($token, $tokens)) {
            $tokens[] = $token;
        }
        $request->user()->update(['fcm_tokens' => $tokens]);

        return response()->json(['message' => 'FCM token updated']);
    });

    Route::delete('/user/fcm-token/{token}', function (Request $request, $token) {
        $tokens = $request->user()->fcm_tokens ?? [];
        $tokens = array_values(array_filter($tokens, fn ($t) => $t !== $token));
        $request->user()->update(['fcm_tokens' => $tokens]);

        return response()->json(['message' => 'FCM token removed']);
    });
});
