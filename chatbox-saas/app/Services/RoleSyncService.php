<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;

class RoleSyncService
{
    /**
     * Mapeamento único: users.role (slug) → roles.slug na base de dados.
     */
    public const USER_ROLE_TO_ROLE_SLUG = [
        User::ROLE_COMPANY_ADMIN => 'company_admin',
        User::ROLE_SUPERVISOR => 'supervisor',
        User::ROLE_AGENT => 'agent',
        User::ROLE_MANAGER => 'manager',
        User::ROLE_FINANCIAL => 'financial',
        User::ROLE_TECHNICAL_SUPPORT => 'technical_support',
        User::ROLE_CLIENT => 'client',
    ];

    public function syncUserRole(User $user): void
    {
        if ($user->isPlatformAdmin()) {
            $user->roles()->sync([]);

            return;
        }

        $slug = self::USER_ROLE_TO_ROLE_SLUG[$user->role] ?? null;

        if ($slug === null) {
            return;
        }

        $role = Role::query()->where('slug', $slug)->first();

        if ($role) {
            $user->roles()->sync([$role->id]);
        }
    }

    public function syncAllUsers(): int
    {
        $count = 0;

        User::query()->each(function (User $user) use (&$count) {
            $this->syncUserRole($user);
            $count++;
        });

        return $count;
    }

    public function resolvePermissionsForUserRole(?string $userRole): ?Role
    {
        if ($userRole === null || $userRole === User::ROLE_PLATFORM_ADMIN) {
            return null;
        }

        $slug = self::USER_ROLE_TO_ROLE_SLUG[$userRole] ?? null;

        if ($slug === null) {
            return null;
        }

        return Role::query()->where('slug', $slug)->first();
    }
}
