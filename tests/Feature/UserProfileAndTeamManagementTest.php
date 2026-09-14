<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
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
}
