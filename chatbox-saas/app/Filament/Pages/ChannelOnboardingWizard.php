<?php

namespace App\Filament\Pages;

use App\Models\Channel;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class ChannelOnboardingWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Atendimento';

    protected static ?string $navigationLabel = 'Adicionar Canal';

    protected static ?string $slug = 'channels/onboarding';

    protected static ?string $title = 'Assistente de Canais';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.channel-onboarding-wizard';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Seleção')
                        ->description('Escolha o tipo de canal que deseja conectar')
                        ->schema([
                            Select::make('type')
                                ->label('Qual canal você quer adicionar?')
                                ->options([
                                    'whatsapp' => 'WhatsApp Cloud API',
                                    'widget' => 'Widget para Site',
                                    'instagram' => 'Instagram Direct',
                                    'telegram' => 'Telegram (Em breve)',
                                ])
                                ->required()
                                ->live(),
                        ]),
                    Step::make('Conexão')
                        ->description('Forneça as credenciais de acesso')
                        ->schema([
                            Placeholder::make('instrucoes')
                                ->label('Instruções')
                                ->content(new HtmlString('Para o WhatsApp, acesse o painel da Meta for Developers e copie o seu <strong>Phone Number ID</strong> e o <strong>Access Token</strong> permanente.'))
                                ->visible(fn ($get) => $get('type') === 'whatsapp'),

                            TextInput::make('external_ref')
                                ->label(fn ($get) => $get('type') === 'whatsapp' ? 'Phone Number ID' : 'ID Externo')
                                ->required()
                                ->visible(fn ($get) => in_array($get('type'), ['whatsapp', 'widget'])),

                            TextInput::make('token_api')
                                ->label('Token de Acesso / API Key')
                                ->password()
                                ->required()
                                ->visible(fn ($get) => $get('type') === 'whatsapp'),
                        ]),
                    Step::make('Finalização')
                        ->description('Teste a conexão e salve')
                        ->schema([
                            Placeholder::make('resumo')
                                ->label('Quase pronto!')
                                ->content('Após clicar em "Finalizar", o sistema testará as credenciais. Se tudo estiver correto, o seu canal estará ativo e pronto para uso.'),
                        ]),
                ])
                    ->submitAction(new HtmlString('<button type="submit" class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">Finalizar e Conectar</button>')),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $state = $this->form->getState();
        $company = filament()->auth()->user()->company;

        if (! Channel::canAddMoreChannels($company)) {
            Notification::make()
                ->title('Limite Atingido')
                ->body('Seu plano não permite adicionar mais canais. Faça o upgrade.')
                ->danger()
                ->send();

            return;
        }

        // Testar a conexão aqui (mock para WhatsApp)
        if ($state['type'] === 'whatsapp') {
            // Logica de testar com Http
        }

        $channel = Channel::create([
            'company_id' => $company->id,
            'type' => $state['type'],
            'external_ref' => $state['external_ref'],
            'token_api' => $state['token_api'] ?? null,
            'status' => 'connected',
        ]);

        Notification::make()
            ->title('Canal conectado com sucesso!')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.channels.index');
    }
}
