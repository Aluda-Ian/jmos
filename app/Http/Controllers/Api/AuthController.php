<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'secondary_email' => $user->secondary_email,
            'title' => $user->title,
            'department' => $user->department,
            'role' => $user->role,
            'type' => $user->type,
            'pay' => $user->pay,
            'phone' => $user->phone,
            'color' => $user->color,
            'initials' => $user->initials,
            'avatar_url' => $user->avatar_url,
            'bio' => $user->bio,
            'google_calendar_email' => $user->google_calendar_email,
            'google_calendar_status' => $user->google_calendar_status,
            'google_calendar_synced_at' => $user->google_calendar_synced_at?->toIso8601String(),
        ];
    }

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

        AuditLog::record('AUTH', "{$user->name} signed into JMOS workspace", 'User', $user->id, ['role' => $user->role], $request, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Signed in successfully.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        AuditLog::record('AUTH', "{$user->name} signed out of JMOS", 'User', $user->id, ['role' => $user->role], $request, $user);

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

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'secondary_email' => 'nullable|email|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        if ($validated['name'] !== $user->name) {
            $words = preg_split('/\s+/', trim($validated['name']));
            $validated['initials'] = strtoupper(substr($words[0] ?? '', 0, 1).substr($words[1] ?? '', 0, 1));
        }

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|file|image|max:10240', // 10MB max
        ]);

        $file = $request->file('avatar');
        $extension = strtolower($file->getClientOriginalExtension());
        $uploadDir = public_path('uploads/avatars');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = 'user_'.$request->user()->id.'_'.uniqid().'.'.($extension ?: 'jpg');
        $file->move($uploadDir, $safeName);

        $user = $request->user();
        $user->avatar_url = asset('uploads/avatars/'.$safeName);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture updated successfully.',
            'avatar_url' => $user->avatar_url,
            'user' => $this->formatUser($user),
        ]);
    }

    public function removeAvatar(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->avatar_url = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture removed.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password does not match our records.',
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully.',
        ]);
    }
}
