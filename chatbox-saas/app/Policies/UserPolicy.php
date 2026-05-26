<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndPermission;

class UserPolicy
{
    use ChecksTenantAndPermission;

    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, User $model): bool
    {
        return $this->sameCompany($user, $model) && $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_usuários');
    }

    public function update(User $user, User $model): bool
    {
        return $this->sameCompany($user, $model) && $user->hasPermission('edit_usuários');
    }

    public function delete(User $user, User $model): bool
    {
        return $this->sameCompany($user, $model) && $user->hasPermission('delete_usuários');
    }
}
