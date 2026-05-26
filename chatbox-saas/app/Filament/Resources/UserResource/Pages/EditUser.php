<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord;

use App\Models\User;
use App\Services\PlanService;
use Filament\Notifications\Notification;

class EditUser extends BaseEditRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        $data = $this->data;
        $record = $this->record;
        $user = auth()->user();

        // Se o utilizador está a ser ativado ou mudado para perfil de Agente
        $targetStatus = $data['status'] ?? $record->status;
        $targetRole = $data['role'] ?? $record->role;

        // Se tentou ativar ou mudar para Agente e não era Agente Ativo antes
        $isBecameActiveAgent = ($targetStatus === 'active' && $targetRole === User::ROLE_AGENT) 
                               && (! ($record->status === 'active' && $record->role === User::ROLE_AGENT));

        if ($user && ! $user->isPlatformAdmin() && $isBecameActiveAgent) {
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
        return 'Editar Dados do Utilizador';
    }

    public function getBreadcrumb(): string
    {
        return 'Editar';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar Utilizador'),
        ];
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Atualizar Dados');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar');
    }
}
