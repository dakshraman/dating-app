<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;

        // Clean up expired OTPs
        DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('expires_at', '<', Carbon::now())
            ->delete();

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Mail::to($email)->send(new SendOtpMail($otp));

        return response()->json(['message' => 'OTP sent to your email.']);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->whereNull('used_at')
            ->first();

        if (! $record) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        if (Carbon::now()->greaterThan($record->expires_at)) {
            return response()->json(['message' => 'OTP has expired.'], 422);
        }

        // Mark OTP as verified (reserve it for reset)
        DB::table('password_reset_otps')
            ->where('id', $record->id)
            ->update(['used_at' => Carbon::now()]);

        return response()->json(['message' => 'OTP verified successfully.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // OTP must have been verified (used_at set) and not expired
        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->whereNotNull('used_at')
            ->first();

        if (! $record) {
            return response()->json(['message' => 'Please verify your OTP first.'], 422);
        }

        if (Carbon::now()->greaterThan($record->expires_at)) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->update([
            'password' => $request->password,
        ]);

        // Invalidate all existing tokens (force re-login)
        $user->tokens()->delete();

        // Clean up all OTPs for this email
        DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->delete();

        return response()->json(['message' => 'Password reset successfully. Please log in with your new password.']);
    }
}
