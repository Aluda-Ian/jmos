<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use App\Services\NotificationService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_can_fetch_notifications_with_unread_count(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'unread_count',
                'total_count',
                'data' => [
                    '*' => ['id', 'title', 'message', 'type', 'read_at'],
                ],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('unread_count'));
    }

    public function test_can_filter_notifications_by_read_and_unread(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        // Fetch unread
        $unreadResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notifications?filter=unread');
        $unreadResponse->assertStatus(200);
        foreach ($unreadResponse->json('data') as $notif) {
            $this->assertNull($notif['read_at']);
        }

        // Fetch read
        $readResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notifications?filter=read');
        $readResponse->assertStatus(200);
        foreach ($readResponse->json('data') as $notif) {
            $this->assertNotNull($notif['read_at']);
        }
    }

    public function test_can_mark_notification_as_read_and_unread(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $notification = AppNotification::unread()->first();
        $this->assertNotNull($notification);

        // Mark as read
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Notification marked as read',
            ]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);

        // Mark as unread
        $unreadResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/notifications/{$notification->id}/unread");

        $unreadResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Notification marked as unread',
            ]);

        $notification->refresh();
        $this->assertNull($notification->read_at);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/notifications/mark-all-read');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'unread_count' => 0,
            ]);

        $this->assertEquals(0, AppNotification::unread()->count());
    }

    public function test_can_delete_notification(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $notification = AppNotification::first();
        $this->assertNotNull($notification);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_user_can_update_secondary_notification_email(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/secondary-email', [
                'secondary_email' => 'ian.personal@gmail.com',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'secondary_email' => 'ian.personal@gmail.com',
            ]);

        $user->refresh();
        $this->assertEquals('ian.personal@gmail.com', $user->secondary_email);
        $this->assertEquals('ian.personal@gmail.com', NotificationService::resolveSecondaryEmail($user->email));
    }

    public function test_user_can_remove_secondary_notification_email(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $user->update(['secondary_email' => 'old.personal@gmail.com']);
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/secondary-email', [
                'secondary_email' => null,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'secondary_email' => null,
            ]);

        $user->refresh();
        $this->assertNull($user->secondary_email);
    }

    public function test_cannot_set_invalid_secondary_email_format(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/secondary-email', [
                'secondary_email' => 'invalid-email-address',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['secondary_email']);
    }

    public function test_unauthenticated_cannot_update_secondary_email(): void
    {
        $response = $this->postJson('/api/auth/secondary-email', [
            'secondary_email' => 'ian.personal@gmail.com',
        ]);

        $response->assertStatus(401);
    }
}
