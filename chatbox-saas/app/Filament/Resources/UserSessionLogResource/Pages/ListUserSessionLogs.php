<?php

namespace App\Filament\Resources\UserSessionLogResource\Pages;

use App\Filament\Resources\UserSessionLogResource;
use App\Models\User;
use App\Models\UserSessionLog;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUserSessionLogs extends ListRecords
{
    protected static string $resource = UserSessionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clear_logs')
                ->label('Limpar Histórico')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->form([
                    Select::make('user_id')
                        ->label('Selecionar Usuário (Opcional)')
                        ->options(function () {
                            $user = auth()->user();
                            $query = User::query();
                            if ($user && ! $user->isPlatformAdmin()) {
                                $query->where('company_id', $user->company_id);
                            }

                            return $query->pluck('name', 'id');
                        })
                        ->placeholder('Todos os usuários (Limpeza Total)')
                        ->helperText('Se não selecionar nenhum usuário, todos os logs da empresa serão apagados.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Limpar Histórico')
                ->modalDescription('Confirme a exclusão dos registros de sessão selecionados. Esta ação é irreversível.')
                ->action(function (array $data) {
                    $query = UserSessionLog::query();
                    $user = auth()->user();

                    // Isolamento de Tenant
                    if ($user && ! $user->isPlatformAdmin()) {
                        $query->whereHas('user', fn ($q) => $q->where('company_id', $user->company_id));
                    }

                    // Filtro por usuário específico se selecionado
                    if (! empty($data['user_id'])) {
                        $query->where('user_id', $data['user_id']);
                    }

                    $count = $query->count();
                    $query->delete();

                    Notification::make()
                        ->title('Histórico Limpo')
                        ->body("Sucesso! {$count} registos foram removidos.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
