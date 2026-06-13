<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\SuperAdminPanelProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    AdminPanelProvider::class,
    SuperAdminPanelProvider::class,
    RepositoryServiceProvider::class,
];
