<?php

namespace Tests\Feature;

use App\Mail\DealWonAlertMail;
use App\Mail\InvoiceReminderMail;
use App\Mail\MeetingReminderMail;
use App\Mail\TaskAssignedMail;
use App\Models\SystemSetting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_can_fetch_settings_with_masked_secrets(): void
    {
        $response = $this->getJson('/api/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'smtp',
                ],
            ]);

        $smtp = $response->json('data.smtp');
        $this->assertIsArray($smtp);
        $this->assertEquals('mail.jeotamedia.co.ke', $smtp['mail_host']['value']);
        $this->assertEquals('••••••••', $smtp['mail_password']['value']);
        $this->assertTrue($smtp['mail_password']['is_secret']);
    }

    public function test_can_update_settings_without_overwriting_secret_placeholder(): void
    {
        $originalPassword = SystemSetting::getVal('mail_password');
        $this->assertNotEmpty($originalPassword);

        $response = $this->postJson('/api/settings', [
            'settings' => [
                ['group' => 'smtp', 'key' => 'mail_host', 'value' => 'mail.jeotamedia.co.ke'],
                ['group' => 'smtp', 'key' => 'mail_password', 'value' => '••••••••', 'is_secret' => true],
                ['group' => 'general', 'key' => 'company_name', 'value' => 'Jeota Media Ltd & Studios'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Settings saved successfully.',
            ]);

        $this->assertEquals($originalPassword, SystemSetting::getVal('mail_password'));
        $this->assertEquals('Jeota Media Ltd & Studios', SystemSetting::getVal('company_name'));
    }

    public function test_can_send_smtp_test_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/settings/test-email', [
            'recipient' => 'test@jeotamedia.co.ke',
            'template' => 'general',
            'mail_host' => 'mail.jeotamedia.co.ke',
            'mail_port' => 587,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertStringContainsString('successfully delivered to test@jeotamedia.co.ke', $response->json('message'));
    }

    public function test_can_send_branded_template_emails(): void
    {
        Mail::fake();

        // 1. Task template
        $taskRes = $this->postJson('/api/settings/test-email', [
            'recipient' => 'producer@jeotamedia.co.ke',
            'template' => 'task',
        ]);
        $taskRes->assertStatus(200);
        Mail::assertSent(TaskAssignedMail::class);

        // 2. Meeting template
        $meetRes = $this->postJson('/api/settings/test-email', [
            'recipient' => 'client@jeotamedia.co.ke',
            'template' => 'meeting',
        ]);
        $meetRes->assertStatus(200);
        Mail::assertSent(MeetingReminderMail::class);

        // 3. Invoice template
        $invRes = $this->postJson('/api/settings/test-email', [
            'recipient' => 'accounts@jeotamedia.co.ke',
            'template' => 'invoice',
        ]);
        $invRes->assertStatus(200);
        Mail::assertSent(InvoiceReminderMail::class);

        // 4. Deal template
        $dealRes = $this->postJson('/api/settings/test-email', [
            'recipient' => 'team@jeotamedia.co.ke',
            'template' => 'deal',
        ]);
        $dealRes->assertStatus(200);
        Mail::assertSent(DealWonAlertMail::class);
    }
}
