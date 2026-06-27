<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SwipeController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,60');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,60');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/auth/google/mobile', [AuthController::class, 'handleGoogleMobile']);

Route::post('/webhooks/revenuecat', [WebhookController::class, 'revenuecat']);

Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->middleware('throttle:3,60');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->middleware('throttle:10,60');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:5,60');

Route::middleware(['auth:sanctum', 'last.active', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences']);
    Route::post('/profile/photos', [ProfileController::class, 'uploadPhoto']);
    Route::delete('/profile/photos/{id}', [ProfileController::class, 'deletePhoto']);
    Route::get('/interests', [ProfileController::class, 'getInterests']);
    Route::put('/profile/interests', [ProfileController::class, 'updateInterests']);
    Route::put('/profile/prompts', [ProfileController::class, 'updatePrompts']);
    Route::get('/profile/visitors', [ProfileController::class, 'visitors']);
    Route::delete('/account', [ProfileController::class, 'destroy']);

    Route::get('/discover', [ProfileController::class, 'discover']);
    Route::get('/profiles/{id}', [ProfileController::class, 'show']);

    Route::post('/swipe', [SwipeController::class, 'store'])->middleware('throttle:30,1');
    Route::get('/matches', [SwipeController::class, 'matches']);
    Route::delete('/matches/{id}', [SwipeController::class, 'destroy']);

    Route::get('/conversations', [ChatController::class, 'conversations']);
    Route::delete('/conversations/{conversation}', [ChatController::class, 'deleteConversation']);
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->middleware('throttle:20,1');
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markAsRead']);
    Route::post('/conversations/{conversation}/vanish-mode', [ChatController::class, 'toggleVanishMode']);
    Route::post('/conversations/{conversation}/typing', [ChatController::class, 'typing'])->middleware('throttle:60,1');
    Route::delete('/conversations/{conversation}/typing', [ChatController::class, 'stopTyping'])->middleware('throttle:60,1');

    Route::post('/chat/upload', [ChatController::class, 'uploadMedia']);
    Route::post('/conversations/{conversation}/messages/{message}/react', [ChatController::class, 'reactToMessage']);
    Route::delete('/conversations/{conversation}/messages/{message}', [ChatController::class, 'deleteMessage']);

    Route::post('/user/block', [ChatController::class, 'blockUser']);
    Route::delete('/user/block/{id}', [ChatController::class, 'unblockUser']);
    Route::get('/user/blocks', [ChatController::class, 'blockedUsers']);
    Route::post('/user/report', [ChatController::class, 'reportUser']);

    Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::post('/subscription/purchase', [SubscriptionController::class, 'purchase']);
    Route::get('/swipe/remaining', [SubscriptionController::class, 'swipeRemaining']);
    Route::post('/swipe/undo', [SwipeController::class, 'undo']);
    Route::get('/likes-received', [SwipeController::class, 'likesReceived']);
    Route::get('/likes/me', [SwipeController::class, 'likesReceived']);
    Route::get('/likes-sent', [SwipeController::class, 'likesSent']);
    Route::delete('/likes-sent/{swiped_id}', [SwipeController::class, 'destroySwipe']);
    Route::post('/profile/boost', [SubscriptionController::class, 'activateBoost']);
    Route::post('/profile/verification-photo', [SubscriptionController::class, 'uploadVerificationPhoto']);

    Route::put('/user/fcm-token', function (Request $request) {
        $request->validate(['fcm_token' => 'required|string']);
        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'FCM token updated']);
    });

    Route::delete('/user/fcm-token/{token}', function (Request $request, $token) {
        if ($request->user()->fcm_token === $token) {
            $request->user()->update(['fcm_token' => null]);
        }

        return response()->json(['message' => 'FCM token removed']);
    });
});
Route::middleware("auth:sanctum")->post("/broadcasting/auth", [\Illuminate\Broadcasting\BroadcastController::class, "authenticate"]);
