<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class CheckTrialStatusAndNotifyAdmins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:check-trials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica trials prestes a vencer e notifica os super admins.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companiesExpiringSoon = Company::where('status', 'trial')
            ->whereNotNull('trial_end_at')
            ->whereBetween('trial_end_at', [now(), now()->addDays(3)])
            ->get();

        if ($companiesExpiringSoon->isEmpty()) {
            $this->info('Nenhum trial vencendo nos próximos 3 dias.');

            return;
        }

        $superAdmins = User::where('role', User::ROLE_PLATFORM_ADMIN)->get();

        foreach ($companiesExpiringSoon as $company) {
            $daysLeft = $company->calcularDiasRestantes();

            foreach ($superAdmins as $admin) {
                Notification::make()
                    ->title('Trial Vencendo: '.$company->name)
                    ->body("A empresa {$company->name} tem apenas {$daysLeft} dias de trial restantes. Verifique a saúde e entre em contato.")
                    ->warning()
                    ->actions([
                        Action::make('Ver Empresa')
                            ->url(route('filament.admin.resources.companies.view', $company))
                            ->button(),
                    ])
                    ->sendToDatabase($admin);
            }
        }

        $this->info('Notificações de trial enviadas com sucesso: '.$companiesExpiringSoon->count());
    }
}
