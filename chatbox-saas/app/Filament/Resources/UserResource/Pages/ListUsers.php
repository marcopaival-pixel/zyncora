<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string 
    {
        return 'Membros e Utilizadores';
    }

    public function getBreadcrumb(): string
    {
        return 'Lista de Membros';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->label('Adicionar Novo Membro')
                ->icon('heroicon-m-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    $user = auth()->user();
                    if ($user && ! $user->isPlatformAdmin()) {
                        $data['company_id'] = $user->company_id;
                        $data['role'] = $data['role'] ?? \App\Models\User::ROLE_AGENT;
                        if (($data['role'] ?? '') === \App\Models\User::ROLE_PLATFORM_ADMIN) {
                            $data['role'] = \App\Models\User::ROLE_AGENT;
                        }
                    }
                    return $data;
                })
                ->before(function (Actions\CreateAction $action, array $data) {
                    $user = auth()->user();
                    $targetRole = $data['role'] ?? \App\Models\User::ROLE_AGENT;

                    if ($user && ! $user->isPlatformAdmin() && $targetRole === \App\Models\User::ROLE_AGENT) {
                        $company = $user->company;
                        $planService = app(\App\Services\PlanService::class);

                        if (! $planService->canAddAttendant($company)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Limite de Atendentes Atingido')
                                ->body("A sua subscrição permite apenas {$company->max_attendants} atendentes ativos. Por favor, faça upgrade do seu plano ou desative um atendente existente.")
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }
                }),
        ];
    }
}
