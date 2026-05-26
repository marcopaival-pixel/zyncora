<?php

namespace App\Observers;

use App\Models\User;
use App\Services\RoleSyncService;

class UserObserver
{
    public function saved(User $user): void
    {
        if ($user->wasRecentlyCreated || $user->wasChanged('role')) {
            app(RoleSyncService::class)->syncUserRole($user);
        }
    }
}
