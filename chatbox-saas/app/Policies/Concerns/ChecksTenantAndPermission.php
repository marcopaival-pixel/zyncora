<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ChecksTenantAndPermission
{
    protected function sameCompany(User $user, Model $model): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if (! isset($model->company_id)) {
            return false;
        }

        return (int) $user->company_id === (int) $model->company_id;
    }
}
