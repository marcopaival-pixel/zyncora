<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

use App\Services\PlanService;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->data;
        $user = auth()->user();
        
        // Se for administrador de empresa tentando criar um Agente
        // No UserResource, o Hidden field 'role' está como agente por padrão para não-admins
        // Mas vamos validar baseado no que está sendo enviado
        $targetRole = $data['role'] ?? User::ROLE_AGENT;

        if ($user && ! $user->isPlatformAdmin() && $targetRole === User::ROLE_AGENT) {
            $company = $user->company;
            $planService = app(PlanService::class);

            if (! $planService->canAddAttendant($company)) {
                Notification::make()
                    ->title('Limite de Atendentes Atingido')
                    ->body("A sua subscrição permite apenas {$company->max_attendants} atendentes ativos. Por favor, faça upgrade do seu plano ou desative um atendente existente.")
                    ->danger()
                    ->send();

                $this->halt();
            }
        }
    }

    public function getTitle(): string
    {
        return 'Registar novo membro';
    }

    public function getSubheading(): ?string
    {
        return 'Preencha nome, e-mail e função. Defina uma senha inicial ou o utilizador poderá usar recuperação de password.';
    }

    public function getBreadcrumb(): string
    {
        return 'Criar';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Confirmar Registo');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $data['company_id'] = $user->company_id;
            $data['role'] = $data['role'] ?? User::ROLE_AGENT;
            if (($data['role'] ?? '') === User::ROLE_PLATFORM_ADMIN) {
                $data['role'] = User::ROLE_AGENT;
            }
        }

        return $data;
    }
}
