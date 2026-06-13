<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use App\Models\ChatLog;
use App\Services\AiService;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Auditoria: Log de acesso (Spy Mode)
        ChatLog::create([
            'company_id' => $this->record->company_id,
            'log_type' => 'chat_view',
            'description' => 'O atendente '.auth()->user()->name." visualizou a conversa #{$this->record->id}.",
            'context' => [
                'user_id' => auth()->id(),
                'conversation_id' => $this->record->id,
                'ip' => request()->ip(),
            ],
            'logged_at' => now(),
        ]);
    }

    /**
     * Atualiza a página quando chega uma nova mensagem via Reverb/Pusher (canal privado da conversa).
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        $id = $this->record?->getKey();
        if (! $id || ! $this->shouldListenRealtime()) {
            return [];
        }

        return [
            "echo-private:conversation.{$id},.message.created" => '$refresh',
        ];
    }

    protected function shouldListenRealtime(): bool
    {
        $driver = config('broadcasting.default');

        if (! in_array($driver, ['reverb', 'pusher'], true)) {
            return false;
        }

        $key = config("broadcasting.connections.{$driver}.key");

        return ! empty($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ai_reply')
                ->label('Sugerir Resposta via IA')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->modalHeading('Resposta Sugerida pela IA')
                ->modalSubmitActionLabel('Revisar e Enviar')
                ->visible(fn (): bool => in_array((string) $this->record->status, ['open', 'waiting'], true))
                ->form([
                    Textarea::make('body')
                        ->label('Mensagem Sugerida')
                        ->required()
                        ->rows(5)
                        ->maxLength(8000)
                        ->columnSpanFull(),
                ])
                ->mountUsing(function (Filament\Forms\Form $form, AiService $aiService) {
                    $suggestion = $aiService->generateAgentSuggestion($this->record);
                    $form->fill(['body' => $suggestion]);
                })
                ->action(function (array $data): void {
                    $this->record->messages()->create([
                        'sender_type' => 'agent',
                        'sender_id' => auth()->id(),
                        'body' => $data['body'],
                        'message_type' => 'text',
                        'sent_at' => now(),
                    ]);

                    $this->record->touch();

                    Notification::make()
                        ->title('Mensagem enviada')
                        ->success()
                        ->send();

                    $this->redirect(ConversationResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('reply')
                ->label('Responder')
                ->icon('heroicon-o-paper-airplane')
                ->modalHeading('Enviar mensagem ao cliente')
                ->modalSubmitActionLabel('Enviar')
                ->visible(fn (): bool => in_array((string) $this->record->status, ['open', 'waiting'], true))
                ->form([
                    Textarea::make('body')
                        ->label('Mensagem')
                        ->required()
                        ->rows(5)
                        ->maxLength(8000)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $this->record->messages()->create([
                        'sender_type' => 'agent',
                        'sender_id' => auth()->id(),
                        'body' => $data['body'],
                        'message_type' => 'text',
                        'sent_at' => now(),
                    ]);

                    $this->record->touch();

                    Notification::make()
                        ->title('Mensagem enviada')
                        ->success()
                        ->send();

                    $this->redirect(ConversationResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions\EditAction::make(),
        ];
    }
}
