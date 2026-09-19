<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('color', 20)->default('#C52523');
                $table->json('permissions')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'role_id')) {
                    $table->unsignedBigInteger('role_id')->nullable()->after('role');
                }
                if (! Schema::hasColumn('users', 'custom_permissions')) {
                    $table->json('custom_permissions')->nullable()->after('role_id');
                }
            });
        }

        // Seed default system roles
        $defaultRoles = [
            [
                'name' => 'Owner / Executive Producer',
                'slug' => 'owner',
                'description' => 'Unrestricted super-administrator with full access to all workspace financial, operational, and system resources.',
                'color' => '#C52523',
                'is_system' => true,
                'permissions' => json_encode(['*']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Operations & IT Manager',
                'slug' => 'manager',
                'description' => 'Comprehensive management access across production projects, clients, quotes, team administration, and system maintenance.',
                'color' => '#2B8A5A',
                'is_system' => true,
                'permissions' => json_encode([
                    'dashboard.view', 'dashboard.financials',
                    'leads.view', 'leads.manage', 'deals.manage',
                    'clients.view', 'clients.manage', 'contacts.manage',
                    'projects.view', 'projects.manage', 'tasks.manage',
                    'quotes.view', 'quotes.manage',
                    'finance.view', 'budget_calculator.access',
                    'people.view', 'people.manage', 'roles.manage',
                    'documents.view', 'documents.manage',
                    'chat.access', 'calendar.view', 'calendar.manage',
                    'audit_logs.view', 'settings.manage', 'system.upgrade',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finance & Accounting',
                'slug' => 'finance',
                'description' => 'Access to financial ledgers, invoice dispatching, expense records, budget calculation, and revenue reports.',
                'color' => '#2B6E8A',
                'is_system' => true,
                'permissions' => json_encode([
                    'dashboard.view', 'dashboard.financials',
                    'clients.view', 'contacts.manage',
                    'quotes.view', 'quotes.manage',
                    'finance.view', 'invoices.manage', 'expenses.manage', 'budget_calculator.access',
                    'documents.view', 'chat.access', 'calendar.view',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sales & Business Development',
                'slug' => 'sales',
                'description' => 'Access to sales pipeline, leads generation, quotation builder, client directory, and deals.',
                'color' => '#8A5A2B',
                'is_system' => true,
                'permissions' => json_encode([
                    'dashboard.view',
                    'leads.view', 'leads.manage', 'deals.manage',
                    'clients.view', 'clients.manage', 'contacts.manage',
                    'quotes.view', 'quotes.manage',
                    'projects.view', 'documents.view',
                    'chat.access', 'calendar.view', 'calendar.manage',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Creative & Production Team',
                'slug' => 'team',
                'description' => 'Standard member access for project task execution, timeline tracking, team chat, calendar, and documents.',
                'color' => '#5A7A2B',
                'is_system' => true,
                'permissions' => json_encode([
                    'dashboard.view',
                    'projects.view', 'tasks.manage',
                    'documents.view', 'chat.access', 'calendar.view',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultRoles as $roleData) {
            $existing = DB::table('roles')->where('slug', $roleData['slug'])->first();
            if (! $existing) {
                DB::table('roles')->insert($roleData);
            } else {
                DB::table('roles')->where('slug', $roleData['slug'])->update([
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'color' => $roleData['color'],
                    'is_system' => true,
                    'permissions' => $roleData['permissions'],
                    'updated_at' => now(),
                ]);
            }
        }

        // Link existing users to roles based on their role slug
        $roles = DB::table('roles')->get()->keyBy('slug');
        foreach ($roles as $slug => $role) {
            DB::table('users')->where('role', $slug)->whereNull('role_id')->update(['role_id' => $role->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'custom_permissions')) {
                    $table->dropColumn('custom_permissions');
                }
                if (Schema::hasColumn('users', 'role_id')) {
                    $table->dropColumn('role_id');
                }
            });
        }

        Schema::dropIfExists('roles');
    }
};
