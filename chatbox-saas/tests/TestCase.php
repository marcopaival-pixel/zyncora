<?php

namespace Tests;

use App\Models\Company;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setTenant(Company $company): self
    {
        app(TenantService::class)->setCompany($company);

        return $this;
    }
}
