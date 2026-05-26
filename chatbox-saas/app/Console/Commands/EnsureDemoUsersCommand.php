<?php

namespace App\Console\Commands;

use Database\Seeders\DemoUsersSeeder;
use Illuminate\Console\Command;

class EnsureDemoUsersCommand extends Command
{
    protected $signature = 'demo:ensure-users
                            {--force : Executar sem confirmação em produção}';

    protected $description = 'Recria ou atualiza os utilizadores de demonstração (admin@example.com e agente@demo.local, senha: password)';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Em produção use --force apenas se tiver a certeza.');

            return self::FAILURE;
        }

        $this->call('db:seed', [
            '--class' => DemoUsersSeeder::class,
            '--force' => true,
        ]);

        $this->info('Utilizadores demo garantidos.');
        $this->line('  Admin plataforma: admin@example.com / password');
        $this->line('  Atendente demo:  agente@demo.local / password');

        return self::SUCCESS;
    }
}
