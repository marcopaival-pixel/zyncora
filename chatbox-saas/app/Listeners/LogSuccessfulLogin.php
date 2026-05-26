<?php

namespace App\Listeners;

use App\Services\SecurityService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function handle(Login $event): void
    {
        $this->securityService->log('login', 'Usuário realizou login com sucesso.');
    }
}
