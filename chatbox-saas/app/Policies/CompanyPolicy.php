<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndPermission;

class CompanyPolicy
{
    use ChecksTenantAndPermission;

    public function view(User $user, Company $company): bool
    {
        return $this->sameCompany($user, $company);
    }

    public function update(User $user, Company $company): bool
    {
        if (! $this->sameCompany($user, $company)) {
            return false;
        }

        return $user->isPlatformAdmin()
            || $user->isCompanyAdmin()
            || $user->canManageIntegrations();
    }
}
