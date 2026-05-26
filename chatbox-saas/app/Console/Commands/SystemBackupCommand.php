<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class SystemBackupCommand extends Command
{
    protected $signature = 'system:backup';

    protected $description = 'Realiza o backup do banco de dados e arquivos locais';

    public function handle(): int
    {
        $this->info('Iniciando backup do sistema...');

        $filename = 'backup-'.now()->format('Y-m-d-H-i-s').'.sql';
        $path = storage_path('app/backups/'.$filename);

        if (! file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $connection = config('database.default');
        $dbHost = config("database.connections.{$connection}.host");
        $dbName = config("database.connections.{$connection}.database");
        $dbUser = config("database.connections.{$connection}.username");
        $dbPass = config("database.connections.{$connection}.password");

        $process = new Process(
            ['mysqldump', '--user='.$dbUser, '--host='.$dbHost, $dbName],
            null,
            ['MYSQL_PWD' => (string) $dbPass]
        );
        $process->setTimeout(300);

        try {
            $process->run();

            if (! $process->isSuccessful()) {
                $this->error('Falha ao realizar backup.');
                Log::error('Falha no backup do sistema.', [
                    'stderr' => $process->getErrorOutput(),
                ]);

                return self::FAILURE;
            }

            file_put_contents($path, $process->getOutput());

            $this->info("Backup concluído: {$filename}");
            Log::info("Backup do sistema realizado com sucesso: {$filename}");

            $this->clearOldBackups();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Erro crítico no backup: '.$e->getMessage());
            Log::error('Erro crítico no backup do sistema.', ['message' => $e->getMessage()]);

            return self::FAILURE;
        }
    }

    protected function clearOldBackups(): void
    {
        $files = Storage::disk('local')->files('backups');
        foreach ($files as $file) {
            $lastModified = Storage::disk('local')->lastModified($file);
            if (time() - $lastModified > (7 * 24 * 60 * 60)) {
                Storage::disk('local')->delete($file);
            }
        }
    }
}
