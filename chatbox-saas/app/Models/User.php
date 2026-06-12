<?php

namespace App\Models;

use App\Models\UserSessionLog;
use App\Services\RoleSyncService;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_PLATFORM_ADMIN = 'platform_admin';

    public const ROLE_COMPANY_ADMIN = 'company_admin';

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_AGENT = 'agent';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_FINANCIAL = 'financial';

    public const ROLE_TECHNICAL_SUPPORT = 'technical_support';

    public const ROLE_CLIENT = 'client';

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'presence_status',
        'max_simultaneous_chats',
        'last_active_at',
        'current_session_id',
    ];

    public bool $is_impersonating = false;
    public ?string $impersonation_level = null;
    public ?int $original_company_id = null;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_active_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === self::ROLE_PLATFORM_ADMIN && !$this->is_impersonating) {
            return true;
        }

        if ($this->is_impersonating) {
            if ($this->impersonation_level === 'view_only') {
                return str_starts_with($permission, 'view_');
            }
            if ($this->impersonation_level === 'view_edit') {
                return !str_starts_with($permission, 'delete_') && !str_starts_with($permission, 'manage_financeiro');
            }
            if ($this->impersonation_level === 'view_fix') {
                // Allows edit but not delete, allows managing settings
                return !str_starts_with($permission, 'delete_');
            }
            if ($this->impersonation_level === 'full_access') {
                return true;
            }
        }

        if ($this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists()) {
            return true;
        }

        $mappedRole = app(RoleSyncService::class)->resolvePermissionsForUserRole($this->role);

        if ($mappedRole === null) {
            return false;
        }

        return $mappedRole->permissions()->where('name', $permission)->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->role === self::ROLE_PLATFORM_ADMIN && !$this->is_impersonating) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active'
            && in_array($this->role, [
                self::ROLE_PLATFORM_ADMIN,
                self::ROLE_COMPANY_ADMIN,
                self::ROLE_SUPERVISOR,
                self::ROLE_AGENT,
                self::ROLE_MANAGER,
                self::ROLE_FINANCIAL,
                self::ROLE_TECHNICAL_SUPPORT,
                self::ROLE_CLIENT,
            ], true);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'assignee_id');
    }

    public function isPlatformAdmin(): bool
    {
        if ($this->is_impersonating) {
            return false;
        }
        return $this->role === self::ROLE_PLATFORM_ADMIN;
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === self::ROLE_COMPANY_ADMIN;
    }

    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isFinancial(): bool
    {
        return $this->role === self::ROLE_FINANCIAL;
    }

    public function isTechnicalSupport(): bool
    {
        return $this->role === self::ROLE_TECHNICAL_SUPPORT;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Grouping Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Can manage company settings, integrations, and bots.
     */
    public function canManageIntegrations(): bool
    {
        return $this->hasAnyPermission(['manage_integrações', 'manage_canais', 'view_integrações']);
    }

    /**
     * Can manage company users and their roles.
     */
    public function canManageUsers(): bool
    {
        return $this->hasAnyPermission(['create_usuários', 'edit_usuários', 'delete_usuários', 'view_usuários']);
    }

    /**
     * Can view and participate in chats/conversations.
     */
    public function canChat(): bool
    {
        return $this->hasAnyPermission(['view_conversas', 'manage_conversas']);
    }

    /**
     * Can view internal logs and technical data.
     */
    public function canViewLogs(): bool
    {
        return $this->hasPermission('view_logs');
    }

    /**
     * Can access reports and dashboard analytics.
     */
    public function canViewReports(): bool
    {
        return $this->hasAnyPermission(['view_relatórios', 'export_relatórios']);
    }

    /**
     * Can access billing and subscription details.
     */
    public function canAccessBilling(): bool
    {
        return $this->hasAnyPermission(['view_financeiro', 'manage_financeiro']);
    }

    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function sessionLogs(): HasMany
    {
        return $this->hasMany(UserSessionLog::class);
    }

    public function scopeTenantVisible($query, ?User $user)
    {
        if ($user === null || $user->isPlatformAdmin()) {
            return $query;
        }

        return $query->where('company_id', $user->company_id);
    }
}
