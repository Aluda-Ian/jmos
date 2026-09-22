<?php

namespace Tests\Feature;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserProfileAndTeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_can_update_team_member_salary_department_and_role(): void
    {
        $member = User::where('role', '!=', 'owner')->first();
        if (! $member) {
            $member = User::factory()->create([
                'role' => 'team',
                'name' => 'Original Member',
                'department' => 'Creative',
                'pay' => '40,000/mo',
            ]);
        }

        $response = $this->putJson("/api/users/{$member->id}", [
            'name' => 'Updated Member Name',
            'department' => 'Video & Cinematography',
            'role' => 'finance',
            'type' => 'Full-time',
            'pay' => '75,000/mo',
            'phone' => '+254712345678',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Team member updated successfully.',
            ]);

        $member->refresh();
        $this->assertEquals('Updated Member Name', $member->name);
        $this->assertEquals('Video & Cinematography', $member->department);
        $this->assertEquals('finance', $member->role);
        $this->assertEquals('75,000/mo', $member->pay);
        $this->assertEquals('+254712345678', $member->phone);
    }

    public function test_cannot_demote_last_owner(): void
    {
        $owners = User::where('role', 'owner')->get();
        // Delete any extra owners to leave exactly one
        if ($owners->count() > 1) {
            $owners->slice(1)->each->delete();
        }

        $soleOwner = User::where('role', 'owner')->first();

        $response = $this->putJson("/api/users/{$soleOwner->id}", [
            'role' => 'team',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Cannot demote the last owner account. Promote another owner first.',
            ]);

        $soleOwner->refresh();
        $this->assertEquals('owner', $soleOwner->role);
    }

    public function test_user_can_update_own_profile_under_settings(): void
    {
        $user = User::factory()->create([
            'name' => 'Profile Tester',
            'department' => 'Old Dept',
            'phone' => '0700000000',
            'bio' => 'Old bio',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/profile', [
            'name' => 'Grace Wanjiru New',
            'title' => 'Director of Photography',
            'department' => 'Video & Cinematography',
            'phone' => '+254799887766',
            'bio' => 'Award-winning cinematographer and colorist.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profile updated successfully.',
            ]);

        $user->refresh();
        $this->assertEquals('Grace Wanjiru New', $user->name);
        $this->assertEquals('Director of Photography', $user->title);
        $this->assertEquals('Video & Cinematography', $user->department);
        $this->assertEquals('+254799887766', $user->phone);
        $this->assertEquals('Award-winning cinematographer and colorist.', $user->bio);
    }

    public function test_user_can_upload_and_remove_own_avatar(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('my_avatar.jpg', 200, 'image/jpeg');

        // Upload avatar
        $uploadRes = $this->postJson('/api/auth/avatar', [
            'avatar' => $file,
        ]);

        $uploadRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profile picture updated successfully.',
            ]);

        $user->refresh();
        $this->assertNotNull($user->avatar_url);
        $this->assertStringContainsString('uploads/avatars', $user->avatar_url);

        // Remove avatar
        $removeRes = $this->postJson('/api/auth/avatar/remove');

        $removeRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profile picture removed.',
            ]);

        $user->refresh();
        $this->assertNull($user->avatar_url);
    }

    public function test_user_can_update_own_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_secure_password'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/password', [
            'current_password' => 'old_secure_password',
            'password' => 'new_secure_password_123',
            'password_confirmation' => 'new_secure_password_123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password updated successfully.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('new_secure_password_123', $user->password));
    }

    public function test_adding_new_user_sends_password_setup_invitation_email(): void
    {
        Mail::fake();

        $owner = User::where('role', 'owner')->first();
        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/users', [
            'name' => 'Brian Mwangi',
            'email' => 'brian.mwangi@jeotamedia.co.ke',
            'title' => 'Audio Specialist',
            'department' => 'Audio & Sound',
            'role' => 'team',
            'type' => 'Full-time',
            'pay' => '60,000/mo',
            'send_invite_email' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);

        $user = User::where('email', 'brian.mwangi@jeotamedia.co.ke')->first();
        $this->assertNotNull($user);

        // Verify that invitation mail was sent
        Mail::assertSent(UserInvitationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) &&
                   ! empty($mail->data['otp']) &&
                   $mail->data['userName'] === 'Brian Mwangi';
        });

        // Verify reset token record exists in DB
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();
        $this->assertNotNull($tokenRecord);
    }

    public function test_can_resend_password_setup_invitation_email(): void
    {
        Mail::fake();

        $owner = User::where('role', 'owner')->first();
        Sanctum::actingAs($owner);

        $user = User::factory()->create([
            'email' => 'member.resend@jeotamedia.co.ke',
            'name' => 'Resend Candidate',
        ]);

        $response = $this->postJson("/api/users/{$user->id}/resend-invitation");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        Mail::assertSent(UserInvitationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_new_user_can_set_password_using_invitation_otp(): void
    {
        Mail::fake();

        $owner = User::where('role', 'owner')->first();
        Sanctum::actingAs($owner);

        $res = $this->postJson('/api/users', [
            'name' => 'Alice Kemboi',
            'email' => 'alice.kemboi@jeotamedia.co.ke',
            'title' => 'Graphic Designer',
            'department' => 'Creative',
            'role' => 'team',
            'type' => 'Full-time',
            'send_invite_email' => true,
        ]);

        $otp = $res->json('setup_otp');
        $this->assertNotNull($otp);

        // User sets password using OTP
        $resetRes = $this->postJson('/api/auth/reset-password', [
            'email' => 'alice.kemboi@jeotamedia.co.ke',
            'otp' => $otp,
            'password' => 'NewJeotaSecret2026!',
            'password_confirmation' => 'NewJeotaSecret2026!',
        ]);

        $resetRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $user = User::where('email', 'alice.kemboi@jeotamedia.co.ke')->first();
        $this->assertTrue(Hash::check('NewJeotaSecret2026!', $user->password));
    }
}
