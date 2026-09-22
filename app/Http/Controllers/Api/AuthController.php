<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'role_id' => $user->role_id,
            'role_name' => $user->roleModel?->name ?? ucfirst($user->role),
            'permissions' => $user->allPermissions(),
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

    public function sendPasswordResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No account found with this email address.',
            ], 404);
        }

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($otp),
            'created_at' => now(),
        ]);

        NotificationService::sendPasswordResetOtp([
            'userName' => $user->name,
            'email' => $user->email,
            'otp' => $otp,
            'expiresInMinutes' => 15,
            'ipAddress' => $request->ip(),
        ], $user->email);

        AuditLog::record('AUTH', "Password reset OTP requested for {$user->name} ({$email})", 'User', $user->id, ['ip' => $request->ip()], $request, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'A 6-digit verification code has been sent to your email address.',
            'email' => $email,
            'expires_in_minutes' => 15,
        ]);
    }

    public function verifyPasswordResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record || ! Hash::check($request->otp, $record->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification code. Please check and try again.',
            ], 422);
        }

        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The verification code has expired. Please request a new one.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Verification code confirmed successfully.',
        ]);
    }

    public function resetPasswordWithOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = strtolower(trim($request->email));
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record || ! Hash::check($request->otp, $record->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification code. Please check and try again.',
            ], 422);
        }

        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The verification code has expired. Please request a new code.',
            ], 422);
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found.',
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Invalidate OTP and existing active tokens
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $user->tokens()->delete();

        AuditLog::record('AUTH', "Password reset completed via OTP for {$user->name} ({$email})", 'User', $user->id, ['ip' => $request->ip()], $request, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Your password has been reset successfully. You can now sign in.',
        ]);
    }
}
