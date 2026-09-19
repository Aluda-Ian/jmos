<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations to update and synchronize all user accounts & access roles.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $teamMembers = [
            [
                'email' => 'barny@jeotamedia.co.ke',
                'name' => 'Barny Kiome',
                'title' => 'Founder & Executive Producer',
                'department' => 'Executive & Management',
                'role' => 'owner',
                'type' => 'Full-time',
                'pay' => '60,000/mo',
                'color' => '#C52523',
                'initials' => 'BK',
            ],
            [
                'email' => 'ian@jeotamedia.co.ke',
                'name' => 'Ian Aluda',
                'title' => 'IT Specialist & System Manager',
                'department' => 'Production',
                'role' => 'manager',
                'type' => 'Full-time',
                'pay' => '—',
                'color' => '#2B8A5A',
                'initials' => 'IA',
            ],
            [
                'email' => 'ianaluda27@gmail.com',
                'name' => 'Ian Aluda',
                'title' => 'IT Specialist & System Manager',
                'department' => 'Production',
                'role' => 'manager',
                'type' => 'Full-time',
                'pay' => '—',
                'color' => '#2B8A5A',
                'initials' => 'IA',
            ],
            [
                'email' => 'ianaluda27@students.uonbi.ac.ke',
                'name' => 'Ian Aluda',
                'title' => 'IT Specialist & System Manager',
                'department' => 'Production',
                'role' => 'manager',
                'type' => 'Full-time',
                'pay' => '—',
                'color' => '#2B8A5A',
                'initials' => 'IA',
            ],
            [
                'email' => 'patrick@jeotamedia.co.ke',
                'name' => 'Patrick Mwendwa',
                'title' => 'Sales & Business Development',
                'department' => 'Sales & Marketing',
                'role' => 'sales',
                'type' => 'Per-project',
                'pay' => '—',
                'color' => '#8A5A2B',
                'initials' => 'PM',
            ],
            [
                'email' => 'stephen@jeotamedia.co.ke',
                'name' => 'Stephen Otieno',
                'title' => 'Lead Video Editor',
                'department' => 'Post-Production & 3D',
                'role' => 'team',
                'type' => 'Full-time',
                'pay' => '30,000/mo',
                'color' => '#5A7A2B',
                'initials' => 'SO',
            ],
            [
                'email' => 'amos@jeotamedia.co.ke',
                'name' => 'Amos Muthama',
                'title' => 'Cinematographer & Drone Pilot',
                'department' => 'Video & Cinematography',
                'role' => 'team',
                'type' => 'Per-project',
                'pay' => '5,000/day',
                'color' => '#6E2B8A',
                'initials' => 'AM',
            ],
            [
                'email' => 'lesley@jeotamedia.co.ke',
                'name' => 'Lesley Chacha',
                'title' => 'Copywriter / Social & Client Relations',
                'department' => 'Creative',
                'role' => 'team',
                'type' => 'Per-project',
                'pay' => '150/caption',
                'color' => '#B4780F',
                'initials' => 'LC',
            ],
        ];

        // Explicitly delete Matthew user and all his access rights/notifications if present
        User::where('email', 'matthew@jeotamedia.co.ke')->orWhere('name', 'like', '%Matthew Muange%')->delete();

        foreach ($teamMembers as $member) {
            $user = User::where('email', $member['email'])->first();
            if ($user) {
                $user->update([
                    'role' => $member['role'],
                    'title' => $member['title'],
                    'department' => $member['department'],
                    'type' => $member['type'],
                    'pay' => $member['pay'],
                    'color' => $member['color'],
                    'initials' => $member['initials'],
                ]);
            } else {
                User::create([
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'password' => Hash::make('jeota2024'),
                    'title' => $member['title'],
                    'department' => $member['department'],
                    'role' => $member['role'],
                    'type' => $member['type'],
                    'pay' => $member['pay'],
                    'color' => $member['color'],
                    'initials' => $member['initials'],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
