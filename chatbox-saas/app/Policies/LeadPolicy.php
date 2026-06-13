<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPlatformAdmin();
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->isPlatformAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isPlatformAdmin();
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->isPlatformAdmin();
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->isPlatformAdmin();
    }

    public function restore(User $user, Lead $lead): bool
    {
        return $user->isPlatformAdmin();
    }

    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->isPlatformAdmin();
    }
}
