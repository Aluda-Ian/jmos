<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a new audit log event.
     */
    public static function record(
        string $action,
        string $description,
        ?string $entityType = null,
        string|int|null $entityId = null,
        array $details = [],
        ?Request $request = null,
        ?User $user = null
    ): self {
        $req = $request ?? request();
        $currentUser = $user ?? $req?->user();

        $userName = $currentUser?->name ?? 'System';
        $userRole = $currentUser?->role ?? 'system';
        $userId = $currentUser?->id;

        // If no authenticated user, check if user details were provided in $details
        if ($userName === 'System' && isset($details['_user_name'])) {
            $userName = $details['_user_name'];
            unset($details['_user_name']);
        }
        if ($userRole === 'system' && isset($details['_user_role'])) {
            $userRole = $details['_user_role'];
            unset($details['_user_role']);
        }

        return static::create([
            'user_id' => $userId,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => strtoupper($action),
            'entity_type' => $entityType,
            'entity_id' => $entityId ? (string) $entityId : null,
            'description' => $description,
            'ip_address' => $req?->ip() ?? '127.0.0.1',
            'user_agent' => $req?->userAgent() ? substr($req->userAgent(), 0, 500) : null,
            'details' => $details ?: null,
        ]);
    }
}
