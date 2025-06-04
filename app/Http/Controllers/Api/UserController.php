<?php

// app/Http/Controllers/Api/UserController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    // Signup
   public function signupOrLogin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|min:6',
        'user_type' => 'required|in:new,old',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => true, 'message' => $validator->errors()->first()], 422);
    }

    $email = $request->email;
    $password = $request->password;
    $userType = $request->user_type;

    if ($userType === 'new') {
        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            return response()->json(['error' => true, 'message' => 'Email already exists. Use old type to login.'], 409);
        }

        // Create new user with OTP
        $otp = rand(100000, 999999);

        $user = User::create([
            'email' => $email,
            'password' => Hash::make($password),
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Signup successful. OTP sent.',
            'otp' => $otp
        ]);

    } elseif ($userType === 'old') {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['error' => true, 'message' => 'Invalid email or password'], 401);
        }

        // Login successful
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'error' => false,
            'message' => 'Login successful',
            'token' => $token
        ]);
    }

    return response()->json(['error' => true, 'message' => 'Invalid user type'], 422);
}


    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || $user->otp !== $request->otp || now()->gt($user->otp_expires_at)) {
            return response()->json(['error' => true, 'message' => 'Invalid or expired OTP'], 400);
        }

        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'OTP verified successfully']);
    }

    // Forgot Password - Send OTP
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Email not found'], 404);
        }

        $otp = rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        return response()->json(['message' => 'OTP sent', 'otp' => $otp]);
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric',
            'password' => 'required|min:6'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || $user->otp !== $request->otp || now()->gt($user->otp_expires_at)) {
            return response()->json(['error' => true, 'message' => 'Invalid or expired OTP'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'Password reset successfully']);
    }

    // Get All Users (Protected)
    public function list()
    {
        $users = User::select('id', 'email', 'created_at')->get();
        return response()->json(['users' => $users]);
    }
}
