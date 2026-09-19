<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'permissions',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Generate unique slug from role name.
     */
    public static function createUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        if (empty($slug)) {
            $slug = 'custom-role';
        }

        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Catalog of all modular access rights/permissions.
     */
    public static function getPermissionCatalog(): array
    {
        return [
            [
                'group' => 'Dashboard & Analytics',
                'description' => 'Workspace overview, summaries, and financial metric widgets',
                'permissions' => [
                    ['key' => 'dashboard.view', 'label' => 'View Dashboard', 'description' => 'Access executive overview, active projects counter, and team feed'],
                    ['key' => 'dashboard.financials', 'label' => 'View Financial Metrics', 'description' => 'View revenue cards, cashflow ledger, and profit margin statistics'],
                ],
            ],
            [
                'group' => 'Leads & CRM Pipeline',
                'description' => 'Zoho CRM Lead journeys, pipeline stages, and conversion tracking',
                'permissions' => [
                    ['key' => 'leads.view', 'label' => 'View Leads & Pipeline', 'description' => 'Browse lead cards, deal stages, calls log, and meeting records'],
                    ['key' => 'leads.manage', 'label' => 'Manage Leads', 'description' => 'Create new leads, edit lead status, log calls, schedule meetings, and convert leads'],
                    ['key' => 'deals.manage', 'label' => 'Manage Deals & Revenue', 'description' => 'Create deals, adjust deal values, move pipeline stages, and mark won/lost'],
                ],
            ],
            [
                'group' => 'Clients & Contacts',
                'description' => 'Client accounts directory, brand profiles, and corporate contacts',
                'permissions' => [
                    ['key' => 'clients.view', 'label' => 'View Clients', 'description' => 'Browse client directory and client profiles'],
                    ['key' => 'clients.manage', 'label' => 'Manage Clients', 'description' => 'Add new clients, edit corporate details, and archive accounts'],
                    ['key' => 'contacts.manage', 'label' => 'Manage Contacts', 'description' => 'Add, update, and manage corporate & stakeholder contacts'],
                ],
            ],
            [
                'group' => 'Projects & Deliverables',
                'description' => 'Video productions, animations, photography shoots, and milestones',
                'permissions' => [
                    ['key' => 'projects.view', 'label' => 'View Projects', 'description' => 'Access project boards, deliverables, milestones, and project summaries'],
                    ['key' => 'projects.manage', 'label' => 'Manage Projects', 'description' => 'Create projects, edit timelines, update statuses, and delete projects'],
                    ['key' => 'tasks.manage', 'label' => 'Manage Tasks & Timeline', 'description' => 'Create tasks, assign team members, update progress, and set due dates'],
                ],
            ],
            [
                'group' => 'Quotations Engine',
                'description' => 'Client estimates, commercial budget proposals, and invoice conversion',
                'permissions' => [
                    ['key' => 'quotes.view', 'label' => 'View Quotations', 'description' => 'Browse all quotes, status badges, and pricing breakdowns'],
                    ['key' => 'quotes.manage', 'label' => 'Manage & Send Quotes', 'description' => 'Build quotes, dispatch via Email & WhatsApp, and convert to invoices'],
                ],
            ],
            [
                'group' => 'Invoicing & Finance',
                'description' => 'Accounts receivable, payment tracking, and operational expenses',
                'permissions' => [
                    ['key' => 'finance.view', 'label' => 'View Financial Ledger', 'description' => 'Access invoices, expenses, payment statuses, and receipts'],
                    ['key' => 'invoices.manage', 'label' => 'Manage Invoices', 'description' => 'Create invoices, mark as paid, and send payment reminders'],
                    ['key' => 'expenses.manage', 'label' => 'Manage Expenses', 'description' => 'Record operational costs, upload receipts, and categorize spend'],
                    ['key' => 'budget_calculator.access', 'label' => 'Commercial Budget Calculator', 'description' => 'Access video production rate estimator and formula builder'],
                ],
            ],
            [
                'group' => 'Team & Access Control',
                'description' => 'User administration, employee pay, and custom role permissions',
                'permissions' => [
                    ['key' => 'people.view', 'label' => 'View Team Directory', 'description' => 'View team members, contact info, and assigned departments'],
                    ['key' => 'people.manage', 'label' => 'Manage Team Members', 'description' => 'Add team members, edit profiles, adjust pay rates, and remove accounts'],
                    ['key' => 'roles.manage', 'label' => 'Manage Roles & Permissions', 'description' => 'Create custom roles, edit access rights, and assign user permissions'],
                ],
            ],
            [
                'group' => 'Documents & Files',
                'description' => 'Contracts, brand identity kits, proposals, and grant documents',
                'permissions' => [
                    ['key' => 'documents.view', 'label' => 'View & Download Documents', 'description' => 'Browse document repository and download files'],
                    ['key' => 'documents.manage', 'label' => 'Manage Documents', 'description' => 'Upload files, edit document categories, and delete documents'],
                ],
            ],
            [
                'group' => 'Team Chat & Channels',
                'description' => 'Internal communication, direct messaging, and file attachments',
                'permissions' => [
                    ['key' => 'chat.access', 'label' => 'Access Team Chat', 'description' => 'Send messages in team channels, 1-on-1 direct chats, and upload media'],
                ],
            ],
            [
                'group' => 'Calendar & Meetings',
                'description' => 'Production schedules, client shoot dates, and Google Calendar sync',
                'permissions' => [
                    ['key' => 'calendar.view', 'label' => 'View Calendar', 'description' => 'View team schedule, upcoming events, and shoots'],
                    ['key' => 'calendar.manage', 'label' => 'Manage Calendar & Meet', 'description' => 'Create events, generate Google Meet links, and sync calendar'],
                ],
            ],
            [
                'group' => 'System Administration & IT',
                'description' => 'Audit logging, system configurations, and maintenance tools',
                'permissions' => [
                    ['key' => 'audit_logs.view', 'label' => 'View Audit Logs', 'description' => 'Access system audit trail, user logins, and action logs'],
                    ['key' => 'settings.manage', 'label' => 'Manage System Settings', 'description' => 'Configure company info, SMTP email, and API credentials'],
                    ['key' => 'system.upgrade', 'label' => 'Software Upgrade & Maintenance', 'description' => 'Apply software updates, run migrations, clear cache, and create backups'],
                ],
            ],
        ];
    }
}
