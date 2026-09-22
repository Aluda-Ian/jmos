<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_can_request_password_reset_otp_with_valid_email(): void
    {
        Mail::fake();

        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'ian@jeotamedia.co.ke',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'email' => 'ian@jeotamedia.co.ke',
                'expires_in_minutes' => 15,
            ]);

        $tokenRecord = DB::table('password_reset_tokens')->where('email', 'ian@jeotamedia.co.ke')->first();
        $this->assertNotNull($tokenRecord);

        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use ($user, $tokenRecord) {
            $this->assertEquals($user->email, $mail->data['email']);
            $this->assertNotEmpty($mail->data['otp']);
            $this->assertTrue(Hash::check($mail->data['otp'], $tokenRecord->token));

            return $mail->hasTo('ian@jeotamedia.co.ke');
        });
    }

    public function test_requesting_otp_with_unknown_email_returns_not_found(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'unknown.user@jeotamedia.co.ke',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'No account found with this email address.',
            ]);

        Mail::assertNothingSent();
    }

    public function test_can_verify_valid_otp(): void
    {
        $email = 'ian@jeotamedia.co.ke';
        $otp = '458921';

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($otp), 'created_at' => now()]
        );

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'otp' => $otp,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Verification code confirmed successfully.',
            ]);
    }

    public function test_verify_otp_fails_with_invalid_code(): void
    {
        $email = 'ian@jeotamedia.co.ke';
        $otp = '458921';

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($otp), 'created_at' => now()]
        );

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'otp' => '999999',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid verification code. Please check and try again.',
            ]);
    }

    public function test_verify_otp_fails_when_expired(): void
    {
        $email = 'ian@jeotamedia.co.ke';
        $otp = '458921';

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($otp), 'created_at' => now()->subMinutes(20)]
        );

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'otp' => $otp,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The verification code has expired. Please request a new one.',
            ]);
    }

    public function test_can_reset_password_with_valid_otp_and_login_with_new_password(): void
    {
        $email = 'stephen@jeotamedia.co.ke';
        $otp = '789123';
        $newPassword = 'BrandNewPassword2026!';

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($otp), 'created_at' => now()]
        );

        $resetResponse = $this->postJson('/api/auth/reset-password', [
            'email' => $email,
            'otp' => $otp,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $resetResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Token record should be removed
        $tokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();
        $this->assertNull($tokenRecord);

        // Verify user can now log in with the new password
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => $newPassword,
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Old password no longer works
        $oldLoginResponse = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'jeota2024',
        ]);

        $oldLoginResponse->assertStatus(401);
    }

    public function test_reset_password_fails_with_mismatched_confirmation(): void
    {
        $email = 'stephen@jeotamedia.co.ke';
        $otp = '789123';

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($otp), 'created_at' => now()]
        );

        $resetResponse = $this->postJson('/api/auth/reset-password', [
            'email' => $email,
            'otp' => $otp,
            'password' => 'NewPassword123',
            'password_confirmation' => 'DifferentPassword456',
        ]);

        $resetResponse->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
