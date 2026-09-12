<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email or password did not match.',
            ], 401);
        }

        $token = $user->createToken('jmos_api_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Signed in successfully.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'secondary_email' => $user->secondary_email,
                'title' => $user->title,
                'role' => $user->role,
                'type' => $user->type,
                'pay' => $user->pay,
                'color' => $user->color,
                'initials' => $user->initials,
                'google_calendar_email' => $user->google_calendar_email,
                'google_calendar_status' => $user->google_calendar_status,
                'google_calendar_synced_at' => $user->google_calendar_synced_at?->toIso8601String(),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'secondary_email' => $user->secondary_email,
                'title' => $user->title,
                'role' => $user->role,
                'type' => $user->type,
                'pay' => $user->pay,
                'color' => $user->color,
                'initials' => $user->initials,
                'google_calendar_email' => $user->google_calendar_email,
                'google_calendar_status' => $user->google_calendar_status,
                'google_calendar_synced_at' => $user->google_calendar_synced_at?->toIso8601String(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Signed out successfully.',
        ]);
    }

    public function updateSecondaryEmail(Request $request): JsonResponse
    {
        $request->validate([
            'secondary_email' => 'nullable|email|max:255',
        ]);

        $user = $request->user();
        $user->secondary_email = $request->secondary_email ?: null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Secondary notification email updated.',
            'secondary_email' => $user->secondary_email,
        ]);
    }
}
