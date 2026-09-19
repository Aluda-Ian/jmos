<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'secondary_email',
        'password',
        'title',
        'department',
        'role',
        'role_id',
        'custom_permissions',
        'type',
        'pay',
        'phone',
        'color',
        'initials',
        'avatar_url',
        'bio',
        'google_calendar_email',
        'google_calendar_status',
        'google_calendar_synced_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'google_calendar_synced_at' => 'datetime',
            'custom_permissions' => 'array',
            'password' => 'hashed',
        ];
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Determine if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Owner has absolute unrestricted access
        if ($this->role === 'owner') {
            return true;
        }

        $allPerms = $this->allPermissions();

        if (in_array('*', $allPerms, true)) {
            return true;
        }

        if (in_array($permission, $allPerms, true)) {
            return true;
        }

        // Check group wildcard, e.g. "leads.*"
        $parts = explode('.', $permission);
        if (count($parts) > 1 && in_array($parts[0].'.*', $allPerms, true)) {
            return true;
        }

        return false;
    }

    /**
     * Get array of all granted permissions for this user.
     */
    public function allPermissions(): array
    {
        if ($this->role === 'owner') {
            return ['*'];
        }

        $permissions = [];

        // 1. Load permissions from assigned Role model
        $role = $this->roleModel ?: Role::where('slug', $this->role)->first();
        if ($role && is_array($role->permissions)) {
            $permissions = $role->permissions;
        } else {
            // Default fallbacks for legacy roles
            $permissions = match ($this->role) {
                'manager' => [
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
                ],
                'finance' => [
                    'dashboard.view', 'dashboard.financials',
                    'clients.view', 'contacts.manage',
                    'quotes.view', 'quotes.manage',
                    'finance.view', 'invoices.manage', 'expenses.manage', 'budget_calculator.access',
                    'documents.view', 'chat.access', 'calendar.view',
                ],
                'sales' => [
                    'dashboard.view',
                    'leads.view', 'leads.manage', 'deals.manage',
                    'clients.view', 'clients.manage', 'contacts.manage',
                    'quotes.view', 'quotes.manage',
                    'projects.view', 'documents.view',
                    'chat.access', 'calendar.view', 'calendar.manage',
                ],
                default => [
                    'dashboard.view',
                    'projects.view', 'tasks.manage',
                    'documents.view', 'chat.access', 'calendar.view',
                ],
            };
        }

        // 2. Merge any user-specific custom permissions
        if (is_array($this->custom_permissions)) {
            $permissions = array_values(array_unique(array_merge($permissions, $this->custom_permissions)));
        }

        return $permissions;
    }
}
