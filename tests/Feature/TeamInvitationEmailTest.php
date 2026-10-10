<?php

namespace Tests\Feature;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeamInvitationEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function newMember(): array
    {
        return [
            'name' => 'Amina Otieno',
            'email' => 'amina@example.com',
            'role' => 'team',
            'type' => 'Full-time',
            'password' => 'secret123',
        ];
    }

    public function test_adding_a_team_member_sends_the_invitation_email(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->postJson('/api/users', $this->newMember())
            ->assertCreated()
            ->assertJsonPath('email_sent', true);

        Mail::assertSent(UserInvitationMail::class, fn ($mail) => $mail->hasTo('amina@example.com'));
    }

    public function test_a_failed_invitation_email_is_reported_with_a_setup_link(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 2,
        ]);
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->postJson('/api/users', $this->newMember());

        $response->assertCreated()
            ->assertJsonPath('email_sent', false)
            ->assertJsonPath('setup_url', fn (string $url) => str_contains($url, 'otp='));
        $this->assertNotEmpty($response->json('email_error'));
        $this->assertStringContainsString('could not be sent', $response->json('message'));
        $this->assertDatabaseHas('users', ['email' => 'amina@example.com']);
    }

    public function test_people_list_shows_invite_sent_then_active_after_first_sign_in(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->postJson('/api/users', $this->newMember())->assertCreated();
        $member = User::where('email', 'amina@example.com')->firstOrFail();
        $this->assertNotNull($member->invitation_sent_at);

        $status = fn () => collect($this->actingAs($owner)->getJson('/api/users')->json('data'))->firstWhere('email', 'amina@example.com')['account_status'];
        $this->assertSame('invited', $status());

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/auth/login', ['email' => 'amina@example.com', 'password' => 'secret123'])->assertOk();

        $this->assertNotNull($member->fresh()->activated_at);
        $this->assertSame('active', $status());
    }
}
