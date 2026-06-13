<?php

namespace App\Filament\Resources\ChatbotResource\Pages;

use App\Filament\Resources\ChatbotResource;
use App\Models\Chatbot;
use App\Services\KnowledgeBaseService;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class TestChat extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ChatbotResource::class;

    protected static string $view = 'filament.resources.chatbot-resource.pages.test-chat';

    public Chatbot $record;

    public string $message = '';

    public array $chatHistory = [];

    public function mount(Chatbot $record): void
    {
        $this->record = $record;
        $this->chatHistory[] = [
            'role' => 'bot',
            'content' => $record->initial_message ?? 'Olá! Como posso ajudar você hoje?',
            'time' => now()->format('H:i'),
        ];
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMsg = $this->message;
        $this->chatHistory[] = [
            'role' => 'user',
            'content' => $userMsg,
            'time' => now()->format('H:i'),
        ];

        $this->message = '';

        // Generate Bot Response
        $service = app(KnowledgeBaseService::class);
        $response = $service->generateAiResponse($this->record, $userMsg);

        $this->chatHistory[] = [
            'role' => 'bot',
            'content' => $response,
            'time' => now()->format('H:i'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('message')
                    ->label('')
                    ->placeholder('Digite sua mensagem de teste...')
                    ->suffixAction(
                        Action::make('send')
                            ->icon('heroicon-m-paper-airplane')
                            ->action(fn () => $this->sendMessage())
                    )
                    ->extraAttributes([
                        'wire:keydown.enter' => 'sendMessage',
                    ]),
            ]);
    }
}
