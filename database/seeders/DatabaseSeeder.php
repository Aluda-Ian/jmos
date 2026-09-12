<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\ChatParticipant;
use App\Models\ChatThread;
use App\Models\FinanceSetting;
use App\Models\ServiceRecipe;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Team Members (User Accounts)
        $users = [
            ['name' => 'Barny Kiome', 'title' => 'Founder & Executive Producer', 'email' => 'barny@jeotamedia.co.ke', 'role' => 'owner', 'type' => 'Full-time', 'pay' => '60,000/mo', 'color' => '#C52523', 'initials' => 'BK'],
            ['name' => 'Ian Aluda', 'title' => 'IT Specialist & System Manager', 'email' => 'ian@jeotamedia.co.ke', 'role' => 'manager', 'type' => 'Full-time', 'pay' => '—', 'color' => '#2B8A5A', 'initials' => 'IA'],
            ['name' => 'Matthew Muange', 'title' => 'Finance & Accounting', 'email' => 'matthew@jeotamedia.co.ke', 'role' => 'finance', 'type' => 'Full-time', 'pay' => '—', 'color' => '#2B6E8A', 'initials' => 'MM'],
            ['name' => 'Patrick Mwendwa', 'title' => 'Sales & Business Development', 'email' => 'patrick@jeotamedia.co.ke', 'role' => 'sales', 'type' => 'Per-project', 'pay' => '—', 'color' => '#8A5A2B', 'initials' => 'PM'],
            ['name' => 'Stephen Otieno', 'title' => 'Lead Video Editor', 'email' => 'stephen@jeotamedia.co.ke', 'role' => 'team', 'type' => 'Full-time', 'pay' => '30,000/mo', 'color' => '#5A7A2B', 'initials' => 'SO'],
            ['name' => 'Amos Muthama', 'title' => 'Cinematographer & Drone Pilot', 'email' => 'amos@jeotamedia.co.ke', 'role' => 'team', 'type' => 'Per-project', 'pay' => '5,000/day', 'color' => '#6E2B8A', 'initials' => 'AM'],
            ['name' => 'Lesley Chacha', 'title' => 'Copywriter / Social & Client Relations', 'email' => 'lesley@jeotamedia.co.ke', 'role' => 'team', 'type' => 'Per-project', 'pay' => '150/caption', 'color' => '#B4780F', 'initials' => 'LC'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], [
                'name' => $u['name'],
                'password' => Hash::make('jeota2024'),
                'title' => $u['title'],
                'role' => $u['role'],
                'type' => $u['type'],
                'pay' => $u['pay'],
                'color' => $u['color'],
                'initials' => $u['initials'],
            ]);
        }

        // 2. Seed Standard Service Recipes (Blueprints for Project & Task Generation)
        $services = [
            ['name' => 'Brand film', 'code' => 'BF', 'stages' => ['brief', 'concept', 'pre-pro', 'shoot', 'edit', 'color', 'review', 'delivery'], 'deliverables' => 'Final film + 30s cutdowns'],
            ['name' => 'Documentary', 'code' => 'DC', 'stages' => ['brief', 'concept', 'pre-pro', 'shoot', 'edit', 'color', 'review', 'delivery'], 'deliverables' => 'Final film + 30s cutdowns'],
            ['name' => 'Social media reels', 'code' => 'SR', 'stages' => ['brief', 'concept', 'pre-pro', 'shoot', 'edit', 'color', 'review', 'delivery'], 'deliverables' => '1-minute reels'],
            ['name' => 'Corporate photography', 'code' => 'CP', 'stages' => ['brief', 'concept', 'pre-pro', 'shoot', 'edit', 'review', 'delivery'], 'deliverables' => 'Edited photos'],
            ['name' => 'Event coverage', 'code' => 'EC', 'stages' => ['brief', 'pre-pro', 'shoot', 'edit', 'review', 'delivery'], 'deliverables' => '3-min highlight + edited photos'],
            ['name' => 'Podcast production', 'code' => 'PP', 'stages' => ['brief', 'concept', 'pre-pro', 'shoot', 'edit', 'review', 'delivery'], 'deliverables' => 'Trailer + teaser + full episode'],
            ['name' => 'Livestream', 'code' => 'LS', 'stages' => ['brief', 'pre-pro', 'shoot', 'delivery'], 'deliverables' => 'Live stream (YouTube/FB/Zoom)'],
            ['name' => 'Social media management', 'code' => 'SM', 'stages' => ['brief', 'concept', 'content calendar', 'review', 'publish'], 'deliverables' => 'Content calendars + assets'],
        ];

        foreach ($services as $svc) {
            ServiceRecipe::updateOrCreate(['code' => $svc['code']], $svc);
        }

        // 3. Initial Financial Setting (Clean starting balance)
        FinanceSetting::updateOrCreate(['key' => 'brought_forward'], [
            'numeric_value' => 0,
            'text_value' => 'Initial Account Balance',
        ]);

        // 4. Initial System Settings (SMTP & Google Calendar APIs)
        $settings = [
            // SMTP Settings
            ['group' => 'smtp', 'key' => 'mail_host', 'value' => 'mail.jeotamedia.co.ke', 'is_secret' => false],
            ['group' => 'smtp', 'key' => 'mail_port', 'value' => '587', 'is_secret' => false],
            ['group' => 'smtp', 'key' => 'mail_username', 'value' => 'jmos@jeotamedia.co.ke', 'is_secret' => false],
            ['group' => 'smtp', 'key' => 'mail_password', 'value' => '@Munangwe212', 'is_secret' => true],
            ['group' => 'smtp', 'key' => 'mail_encryption', 'value' => 'tls', 'is_secret' => false],
            ['group' => 'smtp', 'key' => 'mail_from_address', 'value' => 'jmos@jeotamedia.co.ke', 'is_secret' => false],
            ['group' => 'smtp', 'key' => 'mail_from_name', 'value' => 'JMOS — Jeota Media', 'is_secret' => false],

            // Google Calendar & Notifications API Settings
            ['group' => 'google_calendar', 'key' => 'google_client_id', 'value' => '', 'is_secret' => false],
            ['group' => 'google_calendar', 'key' => 'google_client_secret', 'value' => '', 'is_secret' => true],
            ['group' => 'google_calendar', 'key' => 'google_calendar_id', 'value' => 'primary', 'is_secret' => false],
            ['group' => 'google_calendar', 'key' => 'google_api_key', 'value' => '', 'is_secret' => true],
            ['group' => 'google_calendar', 'key' => 'google_sync_enabled', 'value' => '1', 'is_secret' => false],

            // General System Settings
            ['group' => 'general', 'key' => 'company_name', 'value' => 'Jeota Media Ltd', 'is_secret' => false],
            ['group' => 'general', 'key' => 'currency', 'value' => 'KES', 'is_secret' => false],
            ['group' => 'general', 'key' => 'timezone', 'value' => 'Africa/Nairobi', 'is_secret' => false],

            // Connect Gava (KRA / eTIMS & iTax) Integration
            ['group' => 'kra', 'key' => 'kra_pin', 'value' => 'P051782390X', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_taxpayer_name', 'value' => 'Jeota Media Ltd', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_etims_branch_id', 'value' => '00', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_etims_device_id', 'value' => 'JMOS-ETIMS-01', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_vat_rate', 'value' => '16', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_wht_rate', 'value' => '5', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_status', 'value' => 'connected', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_last_synced_at', 'value' => now()->toIso8601String(), 'is_secret' => false],
        ];

        foreach ($settings as $st) {
            SystemSetting::updateOrCreate(['key' => $st['key']], $st);
        }

        // 5. Initial Team Chat Channels
        $defaultChannels = [
            ['title' => '#general', 'description' => 'General team discussion, announcements & all-hands updates.'],
            ['title' => '#production', 'description' => 'Shoots, camera gear, on-location crew coordination & editing.'],
            ['title' => '#client-projects', 'description' => 'Live client deliverables, reviews, feedback & status briefs.'],
        ];

        $allUsers = User::all();
        $adminUser = $allUsers->firstWhere('role', 'owner') ?? $allUsers->first();

        foreach ($defaultChannels as $ch) {
            $thread = ChatThread::firstOrCreate(
                ['type' => 'group', 'title' => $ch['title']],
                ['description' => $ch['description'], 'created_by' => $adminUser ? $adminUser->id : null]
            );

            foreach ($allUsers as $u) {
                ChatParticipant::firstOrCreate(
                    ['thread_id' => $thread->id, 'user_id' => $u->id],
                    ['last_read_at' => now(), 'notified_initial_email' => true]
                );
            }
        }

        // 6. Initial Operational Notifications
        $sampleNotifications = [
            [
                'title' => 'Google Calendar & Meet Connected',
                'message' => 'OAuth synchronization modal configured with automated Google Meet video room generation.',
                'type' => 'calendar',
                'link' => 'calendar',
                'read_at' => null,
                'created_at' => now()->subMinutes(15),
            ],
            [
                'title' => 'Software Upgrade Center Live',
                'message' => 'IT Manager upgrade manager and automated zero-data-loss database backups are now active.',
                'type' => 'system',
                'link' => 'settings',
                'read_at' => null,
                'created_at' => now()->subHours(2),
            ],
            [
                'title' => 'Executive Client Briefing',
                'message' => 'New calendar event with Google Meet link has been scheduled on the operations calendar.',
                'type' => 'calendar',
                'link' => 'calendar',
                'read_at' => null,
                'created_at' => now()->subHours(4),
            ],
            [
                'title' => 'Welcome to JMOS v2.4',
                'message' => 'Jeota Media Operating System workspace initialized. Live finance, production recipes, and pipeline tracking ready.',
                'type' => 'system',
                'link' => 'dashboard',
                'read_at' => now()->subDay(),
                'created_at' => now()->subDay(),
            ],
        ];

        foreach ($sampleNotifications as $notif) {
            AppNotification::firstOrCreate(
                ['title' => $notif['title']],
                $notif
            );
        }
    }
}
