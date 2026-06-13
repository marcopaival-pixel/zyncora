<?php

namespace App\Filament\Resources\ChatbotResource\Pages;

use App\Filament\Resources\ChatbotResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions;
use Filament\Notifications\Notification;

class EditChatbot extends BaseEditRecord
{
    protected static string $resource = ChatbotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_suggestions')
                ->label('Gerar Sugestões (IA)')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription('A inteligência artificial irá analisar o segmento da sua empresa e gerar sugestões rápidas adequadas. Confirma?')
                ->action(function () {
                    $record = $this->getRecord();
                    $segment = $record->company->segment ?? 'geral';

                    $suggestions = match (strtolower($segment)) {
                        'academia', 'fitness' => [
                            ['title' => 'Conhecer Planos', 'icon' => '💪'],
                            ['title' => 'Agendar Avaliação', 'icon' => '📅'],
                            ['title' => 'Financeiro', 'icon' => '💰'],
                        ],
                        'clinica', 'saúde', 'medicina' => [
                            ['title' => 'Agendar Consulta', 'icon' => '📅'],
                            ['title' => 'Especialidades', 'icon' => '⚕️'],
                            ['title' => 'Resultados de Exames', 'icon' => '📄'],
                        ],
                        'varejo', 'loja' => [
                            ['title' => 'Ver Catálogo', 'icon' => '🛍️'],
                            ['title' => 'Status do Pedido', 'icon' => '📦'],
                            ['title' => 'Trocas e Devoluções', 'icon' => '🔄'],
                        ],
                        default => [
                            ['title' => 'Falar com Atendente', 'icon' => '👨‍💻'],
                            ['title' => 'Dúvidas Frequentes', 'icon' => '❓'],
                            ['title' => 'Solicitar Orçamento', 'icon' => '📄'],
                        ]
                    };

                    // Remove existing to replace
                    $record->actionCards()->delete();

                    foreach ($suggestions as $index => $s) {
                        $record->actionCards()->create([
                            'company_id' => $record->company_id,
                            'title' => $s['title'],
                            'icon' => $s['icon'],
                            'action_type' => 'text_reply',
                            'action_payload' => $s['title'],
                            'order_column' => $index,
                            'is_active' => true,
                        ]);
                    }

                    Notification::make()
                        ->title('Sugestões geradas com sucesso!')
                        ->success()
                        ->send();

                    $this->refreshFormData(['actionCards']);
                }),
            Actions\Action::make('builder')
                ->label('Abrir Construtor de Fluxo')
                ->icon('heroicon-o-rectangle-group')
                ->color('warning')
                ->url(fn (): string => static::getResource()::getUrl('builder', ['record' => $this->getRecord()])),
            Actions\DeleteAction::make(),
        ];
    }
}
