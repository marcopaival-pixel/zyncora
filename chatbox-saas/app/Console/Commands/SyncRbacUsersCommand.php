<?php

namespace App\Console\Commands;

use App\Services\RoleSyncService;
use Illuminate\Console\Command;

class SyncRbacUsersCommand extends Command
{
    protected $signature = 'rbac:sync-users';

    protected $description = 'Sincroniza users.role com a tabela pivot role_user (RBAC unificado)';

    public function handle(RoleSyncService $roleSyncService): int
    {
        $count = $roleSyncService->syncAllUsers();

        $this->info("RBAC sincronizado para {$count} utilizador(es).");

        return self::SUCCESS;
    }
}
