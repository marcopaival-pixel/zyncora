<?php

namespace App\Filament\Pages;

use App\Helpers\SegmentHelper;
use App\Models\Chatbot;
use App\Services\AgentPersonalizationService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class OnboardingWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $title = 'Vamos criar seu primeiro agente de IA';

    protected static ?string $navigationLabel = 'Assistente Inicial';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.onboarding-wizard';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        if ($user->company && $user->company->is_onboarding_completed) {
            redirect()->to('/admin');
        }

        $this->form->fill([
            'company_name' => $user->company->name ?? '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Segmento')
                        ->schema([
                            ViewField::make('segment_primary')
                                ->label('Qual o segmento da sua empresa?')
                                ->view('filament.forms.components.segment-cards')
                                ->required(),

                            Select::make('segment_secondary')
                                ->label('Selecione o segmento específico')
                                ->options(SegmentHelper::getSecondarySegments())
                                ->visible(fn (Get $get) => in_array($get('segment_primary'), ['Outro', 'Saúde', 'Fitness', 'Educação', 'Jurídico', 'Contabilidade', 'Imobiliário', 'Automotivo', 'Comércio', 'E-commerce', 'Alimentação', 'Beleza', 'Hotelaria', 'Serviços']))
                                ->required(),
                        ]),
                    Wizard\Step::make('Informações da Empresa')
                        ->schema([
                            TextInput::make('company_name')
                                ->label('Qual o nome da sua empresa?')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label('Telefone Fixo')
                                ->tel(),
                            TextInput::make('whatsapp')
                                ->label('WhatsApp')
                                ->tel(),
                            TextInput::make('email')
                                ->label('E-mail de Contato')
                                ->email(),
                            Textarea::make('address')
                                ->label('Endereço Completo')
                                ->rows(2),
                            TextInput::make('website')
                                ->label('Site (opcional)')
                                ->url()
                                ->placeholder('https://'),
                            KeyValue::make('social_networks')
                                ->label('Redes Sociais')
                                ->keyLabel('Plataforma (ex: Instagram)')
                                ->valueLabel('Link ou @'),
                            KeyValue::make('business_hours')
                                ->label('Horário de Funcionamento')
                                ->keyLabel('Dia da semana')
                                ->valueLabel('Horário (ex: 08:00 - 18:00)'),
                        ]),
                    Wizard\Step::make('Personalização')
                        ->schema([
                            Select::make('chatbot_objective')
                                ->label('Qual o principal objetivo do assistente?')
                                ->options([
                                    'vendas' => 'Vendas e Conversão',
                                    'suporte' => 'Suporte ao Cliente / SAC',
                                    'agendamento' => 'Agendamentos / Reservas',
                                    'captacao' => 'Captação de Leads',
                                    'informacao' => 'Tirar Dúvidas / Informações',
                                ])
                                ->required()
                                ->default('suporte')
                                ->hint('A IA configurará os fluxos iniciais focados neste objetivo.'),
                            CheckboxList::make('chatbot_channels')
                                ->label('Em quais canais ele irá atuar?')
                                ->options([
                                    'site' => 'Site / WebChat',
                                    'whatsapp' => 'WhatsApp',
                                    'instagram' => 'Instagram',
                                    'facebook' => 'Facebook Messenger',
                                    'telegram' => 'Telegram',
                                ])
                                ->columns(2)
                                ->required()
                                ->default(['site'])
                                ->hint('A linguagem será adaptada para os canais selecionados.'),
                        ]),
                ])->submitAction(new HtmlString('<button type="submit" class="filament-button filament-button-size-md inline-flex items-center justify-center py-2 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-6 text-sm text-white shadow-md focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">Criar Meu Agente &rarr;</button>')),
            ])
            ->statePath('data');
    }

    public function submit(AgentPersonalizationService $personalizationService): void
    {
        $data = $this->form->getState();
        $user = Auth::user();
        $company = $user->company;

        $finalSegment = ($data['segment_primary'] === 'Outro') ? $data['segment_secondary'] : $data['segment_primary'];

        $company->update([
            'is_onboarding_completed' => true,
            'name' => $data['company_name'],
            'segment' => $finalSegment,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'website' => $data['website'] ?? null,
            'social_networks' => $data['social_networks'] ?? null,
            'business_hours' => $data['business_hours'] ?? null,
            'ai_credits_balance' => 50, // Teste grátis inicial de 50 créditos
        ]);

        $chatbot = Chatbot::create([
            'company_id' => $company->id,
            'name' => 'Assistente Virtual',
            'status' => Chatbot::STATUS_CONFIGURING, // Set initial status to configuring
            'default_channel' => 'site',
            'use_ai' => true,
            'ai_instruction' => '', // Will be updated by service
            'initial_message' => '', // Will be updated by service
        ]);

        // Personalize the agent (Prompt, Knowledge, Flows)
        $objective = $data['chatbot_objective'] ?? 'suporte';
        $channels = $data['chatbot_channels'] ?? ['site'];
        $personalizationService->generateForSegment($company, $chatbot, $finalSegment, $objective, $channels);

        Notification::make()
            ->title('Agente criado com sucesso!')
            ->success()
            ->send();

        $this->redirect('/admin');
    }
}
