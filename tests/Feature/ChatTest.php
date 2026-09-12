<?php

namespace Tests\Feature;

use App\Mail\NewChatMessageMail;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\ChatThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;

    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::create([
            'name' => 'Barny Kiome',
            'email' => 'barny@jeotamedia.co.ke',
            'password' => Hash::make('secret'),
            'role' => 'owner',
            'initials' => 'BK',
            'color' => '#C52523',
        ]);

        $this->user2 = User::create([
            'name' => 'Stephen Otieno',
            'email' => 'stephen@jeotamedia.co.ke',
            'password' => Hash::make('secret'),
            'role' => 'team',
            'initials' => 'SO',
            'color' => '#5A7A2B',
        ]);
    }

    public function test_can_fetch_chat_threads_and_default_channels(): void
    {
        $response = $this->actingAs($this->user1)->getJson('/api/chat/threads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'current_user',
                'threads',
                'team_members',
            ]);

        $this->assertDatabaseHas('chat_threads', [
            'type' => 'group',
            'title' => '#general',
        ]);
    }

    public function test_can_create_and_fetch_direct_chat_thread(): void
    {
        $response = $this->actingAs($this->user1)->postJson('/api/chat/threads/direct', [
            'recipient_id' => $this->user2->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'thread_id',
            ]);

        $threadId = $response->json('thread_id');
        $this->assertDatabaseHas('chat_participants', [
            'thread_id' => $threadId,
            'user_id' => $this->user1->id,
        ]);
        $this->assertDatabaseHas('chat_participants', [
            'thread_id' => $threadId,
            'user_id' => $this->user2->id,
        ]);
    }

    public function test_sending_initial_message_sends_email_notification(): void
    {
        Mail::fake();

        $threadResponse = $this->actingAs($this->user1)->postJson('/api/chat/threads/direct', [
            'recipient_id' => $this->user2->id,
        ]);
        $threadId = $threadResponse->json('thread_id');

        $msgResponse = $this->actingAs($this->user1)->postJson("/api/chat/threads/{$threadId}/messages", [
            'message' => 'Hey Stephen, please check the color grade on the Acre Insights reel.',
        ]);

        $msgResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => [
                    'is_me' => true,
                    'message' => 'Hey Stephen, please check the color grade on the Acre Insights reel.',
                ],
            ]);

        Mail::assertSent(NewChatMessageMail::class, function ($mail) {
            return $mail->hasTo('stephen@jeotamedia.co.ke');
        });

        // Verify participant is marked as notified
        $this->assertDatabaseHas('chat_participants', [
            'thread_id' => $threadId,
            'user_id' => $this->user2->id,
            'notified_initial_email' => true,
        ]);
    }

    public function test_can_fetch_messages_and_unread_count(): void
    {
        $thread = ChatThread::create([
            'type' => 'direct',
            'created_by' => $this->user1->id,
        ]);

        ChatParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $this->user1->id,
            'last_read_at' => now(),
        ]);

        ChatParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $this->user2->id,
            'last_read_at' => null,
        ]);

        ChatMessage::create([
            'thread_id' => $thread->id,
            'sender_id' => $this->user1->id,
            'message' => 'First team ping!',
        ]);

        // Recipient unread count should be >= 1
        $unreadResponse = $this->actingAs($this->user2)->getJson('/api/chat/unread-count');
        $unreadResponse->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $unreadResponse->json('unread_count'));

        // Fetch messages as recipient (marks thread read)
        $messagesResponse = $this->actingAs($this->user2)->getJson("/api/chat/threads/{$thread->id}/messages");
        $messagesResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'thread',
                'messages',
            ]);
    }

    public function test_direct_messages_can_only_be_seen_and_sent_by_involved_participants(): void
    {
        $user3 = User::create([
            'name' => 'Ian Aluda',
            'email' => 'ian@jeotamedia.co.ke',
            'password' => Hash::make('secret'),
            'role' => 'team',
            'initials' => 'IA',
            'color' => '#1C7A4E',
        ]);

        $directThread = ChatThread::create([
            'type' => 'direct',
            'created_by' => $this->user1->id,
        ]);

        ChatParticipant::create([
            'thread_id' => $directThread->id,
            'user_id' => $this->user1->id,
            'last_read_at' => now(),
        ]);

        ChatParticipant::create([
            'thread_id' => $directThread->id,
            'user_id' => $this->user2->id,
            'last_read_at' => null,
        ]);

        ChatMessage::create([
            'thread_id' => $directThread->id,
            'sender_id' => $this->user1->id,
            'message' => 'Private budget review between Barny and Stephen.',
        ]);

        // 1. Participant (user2) CAN read messages
        $allowedResponse = $this->actingAs($this->user2)->getJson("/api/chat/threads/{$directThread->id}/messages");
        $allowedResponse->assertStatus(200);

        // 2. Non-participant (user3) CANNOT read messages -> 403 Forbidden
        $forbiddenReadResponse = $this->actingAs($user3)->getJson("/api/chat/threads/{$directThread->id}/messages");
        $forbiddenReadResponse->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized. Direct messages are private to involved participants.',
            ]);

        // 3. Non-participant (user3) CANNOT send messages to direct thread -> 403 Forbidden
        $forbiddenSendResponse = $this->actingAs($user3)->postJson("/api/chat/threads/{$directThread->id}/messages", [
            'message' => 'Intruder trying to send message',
        ]);
        $forbiddenSendResponse->assertStatus(403);
    }

    public function test_can_upload_chat_attachment(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('camera_snapshot.jpg', 150, 'image/jpeg');

        $response = $this->actingAs($this->user1)->postJson('/api/chat/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'attachment_name' => 'camera_snapshot.jpg',
                'is_image' => true,
            ]);

        $this->assertNotEmpty($response->json('attachment_url'));
    }

    public function test_sending_message_creates_in_app_notification(): void
    {
        $thread = ChatThread::create([
            'type' => 'direct',
            'created_by' => $this->user1->id,
        ]);

        ChatParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $this->user1->id,
            'last_read_at' => now(),
        ]);

        ChatParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $this->user2->id,
            'last_read_at' => null,
        ]);

        $this->actingAs($this->user1)->postJson("/api/chat/threads/{$thread->id}/messages", [
            'message' => 'Please review the director cut.',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user2->id,
            'type' => 'chat',
            'link' => 'chat',
        ]);
    }
}
