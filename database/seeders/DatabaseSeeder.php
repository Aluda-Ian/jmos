<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\ChatParticipant;
use App\Models\ChatThread;
use App\Models\Client;
use App\Models\FinanceSetting;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\Project;
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
            ['name' => 'Internal System Development', 'code' => 'ISD', 'stages' => ['backlog', 'architecture', 'sprint', 'testing', 'deployment', 'live'], 'deliverables' => 'Software features + modules + updates'],
            ['name' => 'Internal Operations & R&D', 'code' => 'INT', 'stages' => ['planning', 'execution', 'review', 'completed'], 'deliverables' => 'Internal infrastructure + documentation'],
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
            ['group' => 'smtp', 'key' => 'mail_password', 'value' => env('MAIL_PASSWORD', ''), 'is_secret' => true],
            ['group' => 'smtp', 'key' => 'mail_encryption', 'value' => 'tls', 'is_secret' => false],
            ['group' => 'smtp', 'key' => 'mail_from_address', 'value' => 'jmos@jeotamedia.co.ke', 'is_secret' => false],
            ['group' => 'smtp', 'key' => 'mail_from_name', 'value' => 'JMOS — Jeota Media', 'is_secret' => false],

            // Google Calendar & Notifications API Settings
            ['group' => 'google_calendar', 'key' => 'google_client_id', 'value' => env('GOOGLE_CLIENT_ID', ''), 'is_secret' => false],
            ['group' => 'google_calendar', 'key' => 'google_client_secret', 'value' => env('GOOGLE_CLIENT_SECRET', ''), 'is_secret' => true],
            ['group' => 'google_calendar', 'key' => 'google_calendar_id', 'value' => 'jeotamedia@gmail.com', 'is_secret' => false],
            ['group' => 'google_calendar', 'key' => 'google_api_key', 'value' => '', 'is_secret' => true],
            ['group' => 'google_calendar', 'key' => 'google_sync_enabled', 'value' => '1', 'is_secret' => false],

            // General System Settings
            ['group' => 'general', 'key' => 'company_name', 'value' => 'Jeota Media Ltd', 'is_secret' => false],
            ['group' => 'general', 'key' => 'currency', 'value' => 'KES', 'is_secret' => false],
            ['group' => 'general', 'key' => 'timezone', 'value' => 'Africa/Nairobi', 'is_secret' => false],

            // Connect Gava (KRA / eTIMS & iTax) Integration
            ['group' => 'kra', 'key' => 'kra_pin', 'value' => 'P052209707D', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_taxpayer_name', 'value' => 'Jeota Media Limited', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_etims_branch_id', 'value' => '00', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_etims_device_id', 'value' => 'JMOS-ETIMS-01', 'is_secret' => false],
            ['group' => 'kra', 'key' => 'kra_vat_rate', 'value' => '0', 'is_secret' => false],
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

        $defaultUser = User::where('email', 'ian@jeotamedia.co.ke')->first() ?? User::first();

        foreach ($sampleNotifications as $notif) {
            $notif['user_id'] = $defaultUser?->id;
            AppNotification::firstOrCreate(
                ['title' => $notif['title'], 'user_id' => $notif['user_id']],
                $notif
            );
        }

        // 7. Initial System Audit Trail Logs
        if (AuditLog::count() === 0) {
            $bk = User::where('email', 'barny@jeotamedia.co.ke')->first();
            $ia = User::where('email', 'ian@jeotamedia.co.ke')->first();

            $initialLogs = [
                [
                    'user_id' => $ia?->id,
                    'user_name' => 'Ian Aluda',
                    'user_role' => 'manager',
                    'action' => 'SYSTEM',
                    'entity_type' => 'System',
                    'entity_id' => null,
                    'description' => 'System configuration optimized and automated zero-data-loss backup routines verified',
                    'ip_address' => '197.232.61.18',
                    'created_at' => now()->subMinutes(12),
                ],
                [
                    'user_id' => $bk?->id,
                    'user_name' => 'Barny Kiome',
                    'user_role' => 'owner',
                    'action' => 'AUTH',
                    'entity_type' => 'User',
                    'entity_id' => $bk?->id ? (string) $bk->id : null,
                    'description' => 'Barny Kiome signed into JMOS workspace',
                    'ip_address' => '102.219.208.4',
                    'created_at' => now()->subMinutes(35),
                ],
                [
                    'user_id' => $ia?->id,
                    'user_name' => 'Ian Aluda',
                    'user_role' => 'manager',
                    'action' => 'SYSTEM',
                    'entity_type' => 'System',
                    'entity_id' => null,
                    'description' => 'Cleared application and compiled system caches during maintenance pass',
                    'ip_address' => '197.232.61.18',
                    'created_at' => now()->subHours(2),
                ],
                [
                    'user_id' => $bk?->id,
                    'user_name' => 'Barny Kiome',
                    'user_role' => 'owner',
                    'action' => 'CREATE',
                    'entity_type' => 'Project',
                    'entity_id' => '1',
                    'description' => "Created live project 'Most' for client 'Pankaj Social Service'",
                    'ip_address' => '102.219.208.4',
                    'created_at' => now()->subHours(5),
                ],
                [
                    'user_id' => $bk?->id,
                    'user_name' => 'Barny Kiome',
                    'user_role' => 'owner',
                    'action' => 'CREATE',
                    'entity_type' => 'Shoot',
                    'entity_id' => '1',
                    'description' => "Scheduled production shoot 'Client Briefing — Nairobi Homes' with Google Meet video link",
                    'ip_address' => '102.219.208.4',
                    'created_at' => now()->subHours(6),
                ],
                [
                    'user_id' => $ia?->id,
                    'user_name' => 'Ian Aluda',
                    'user_role' => 'manager',
                    'action' => 'AUTH',
                    'entity_type' => 'User',
                    'entity_id' => $ia?->id ? (string) $ia->id : null,
                    'description' => 'Ian Aluda signed into JMOS workspace',
                    'ip_address' => '197.232.61.18',
                    'created_at' => now()->subHours(8),
                ],
                [
                    'user_id' => null,
                    'user_name' => 'System',
                    'user_role' => 'system',
                    'action' => 'SYSTEM',
                    'entity_type' => 'System',
                    'entity_id' => null,
                    'description' => 'Automated daily database snapshot generated successfully (manual_backup_daily.sql.gz)',
                    'ip_address' => '127.0.0.1',
                    'created_at' => now()->subDay(),
                ],
            ];

            foreach ($initialLogs as $log) {
                AuditLog::create($log);
            }
        }

        // 8. Initial Internal Client & System Development Project
        Client::updateOrCreate(
            ['client_name' => 'Jeota Media (Internal)'],
            [
                'client_type' => 'Direct',
                'contact_person' => 'Barny Kiome',
                'owner' => 'Ian Aluda',
                'service' => 'Internal System Development',
                'project_status' => 'Active',
                'email' => 'info@jeotamedia.co.ke',
                'phone' => '+254700000000',
                'address' => 'Nairobi, Kenya',
                'website' => 'https://jmos.jeotamedia.co.ke',
                'notes' => 'Internal software development, infrastructure, and tools engineering for Jeota Media Ltd.',
            ]
        );

        Project::updateOrCreate(
            ['project_name' => 'JMOS Operating System Development'],
            [
                'client' => 'Jeota Media (Internal)',
                'project_type' => 'Internal System Development',
                'category' => 'internal',
                'project_manager' => 'Ian Aluda',
                'stage' => 'live',
                'status' => 'On track',
                'priority' => 'High',
                'budget' => 0,
                'progress_pct' => 95,
                'deadline' => 'Continuous',
                'notes' => 'Development of this system (JMOS): Central operating system for clients, live production, pipeline deals, finance, Google Meet video sync, and browser push notifications.',
            ]
        );

        // 9. Initial Zoho CRM-style Sample Leads & Calls
        if (Lead::count() === 0) {
            $leadsData = [
                [
                    'first_name' => 'Ian',
                    'last_name' => 'Aluda',
                    'lead_name' => 'Ian Aluda',
                    'company' => 'venda technologies',
                    'title' => 'Chief Technology Officer',
                    'email' => 'ianaluda27@students.uonbi.ac.ke',
                    'phone' => '+254 712 345 678',
                    'lead_source' => 'Web Research',
                    'lead_status' => 'Qualified',
                    'lead_owner' => 'Jeota Media',
                    'rating' => 'Hot',
                    'industry' => 'Tech',
                    'annual_revenue' => 450000.00,
                    'city' => 'Nairobi',
                    'notes' => 'Looking for full-scale commercial brand video and product feature breakdown clips.',
                ],
                [
                    'first_name' => 'Ian',
                    'last_name' => 'Aluda',
                    'lead_name' => 'Ian Aluda',
                    'company' => 'venda technologies',
                    'title' => 'Director',
                    'email' => 'ianaluda27@gmail.com',
                    'phone' => '+254 722 987 654',
                    'lead_source' => 'LinkedIn',
                    'lead_status' => 'In Discussion',
                    'lead_owner' => 'Jeota Media',
                    'rating' => 'Warm',
                    'industry' => 'Tech',
                    'annual_revenue' => 300000.00,
                    'city' => 'Nairobi',
                    'notes' => 'Inbound inquiry on social media reels package and corporate interviews.',
                ],
                [
                    'first_name' => 'Ingashian',
                    'last_name' => 'Gibendi',
                    'lead_name' => 'Ingashian Gibendi',
                    'company' => 'M-KOPA',
                    'title' => 'Brand & Marketing Lead',
                    'email' => 'igibendi@gmail.com',
                    'phone' => '+254 701 112 233',
                    'lead_source' => 'Web Research',
                    'lead_status' => 'New',
                    'lead_owner' => 'Jeota Media',
                    'rating' => 'Hot',
                    'industry' => 'Corporate',
                    'annual_revenue' => 850000.00,
                    'city' => 'Nairobi',
                    'notes' => 'Identified high potential for regional solar & fintech customer success documentary series.',
                ],
            ];

            foreach ($leadsData as $ld) {
                $createdLead = Lead::create($ld);

                // Add sample call for the lead
                LeadCall::create([
                    'lead_id' => $createdLead->id,
                    'call_type' => 'Outbound',
                    'call_status' => 'Completed',
                    'purpose' => 'Discovery',
                    'outcome' => 'Interested',
                    'duration_minutes' => 12,
                    'call_time' => now()->subDays(1),
                    'logged_by' => 'Patrick Mwendwa',
                    'notes' => 'Discussed production scope, timeline expectations, and budget parameters.',
                ]);
            }
        }

        // 10. Seed Fundraising Master Opportunities (from Excel Master File)
        $this->call(FundraisingMasterSeeder::class);
    }
}
