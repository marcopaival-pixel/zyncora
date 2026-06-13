<?php

namespace App\Filament\Resources\ChatbotResource\Pages;

use App\Filament\Resources\ChatbotResource;
use App\Helpers\SegmentHelper;
use App\Models\Chatbot;
use App\Services\AgentPersonalizationService;
use Filament\Forms;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\HtmlString;

class CreateChatbot extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ChatbotResource::class;

    protected static ?string $title = 'Onboarding Inteligente';

    protected ?string $subheading = 'Configure seu assistente em menos de 5 minutos.';

    protected bool $generateWithAi = true;

    protected string $chatbotObjective = 'suporte';

    protected array $chatbotChannels = ['site'];

    protected ?string $chatbotSegment = null;

    protected function getSteps(): array
    {
        return [
            Step::make('Identificação')
                ->description('Nome e origem principal')
                ->icon('heroicon-o-identification')
                ->schema([
                    Forms\Components\Select::make('company_id')
                        ->relationship('company', 'name')
                        ->label('Empresa')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),
                    Forms\Components\TextInput::make('name')
                        ->label('Nome do Chatbot')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ex.: Assistente Virtual, Maria, Bot Comercial'),
                    Forms\Components\Select::make('default_channel')
                        ->label('Canal Principal de Entrada')
                        ->options([
                            'whatsapp' => 'WhatsApp',
                            'site' => 'Site / WebChat',
                            'instagram' => 'Instagram',
                            'messenger' => 'Facebook Messenger',
                        ])
                        ->default('whatsapp')
                        ->required()
                        ->native(true),
                    Forms\Components\TextInput::make('whatsapp_phone')
                        ->label('Telefone WhatsApp (E.164)')
                        ->maxLength(64)
                        ->placeholder('+5511999999999')
                        ->visible(fn (Forms\Get $get) => $get('default_channel') === 'whatsapp'),
                ]),

            Step::make('Perfil e Segmento')
                ->description('Ajude a IA a entender seu negócio')
                ->icon('heroicon-o-building-storefront')
                ->schema([
                    Forms\Components\Select::make('chatbot_segment')
                        ->label('Qual o segmento da sua empresa?')
                        ->options(SegmentHelper::getSecondarySegments())
                        ->searchable()
                        ->required()
                        ->helperText('A IA usará este segmento para criar fluxos e respostas automaticamente.'),
                    Forms\Components\Select::make('chatbot_objective')
                        ->label('Objetivo Principal do Chatbot')
                        ->options([
                            'vendas' => 'Vendas e Conversão',
                            'suporte' => 'Suporte ao Cliente / SAC',
                            'agendamento' => 'Agendamentos / Reservas',
                            'captacao' => 'Captação de Leads',
                            'informacao' => 'Tirar Dúvidas / Informações',
                        ])
                        ->required()
                        ->default('vendas')
                        ->native(true),
                    Forms\Components\CheckboxList::make('chatbot_channels')
                        ->label('Em quais canais o assistente vai atuar?')
                        ->options([
                            'site' => 'Site / WebChat',
                            'whatsapp' => 'WhatsApp',
                            'instagram' => 'Instagram',
                            'facebook' => 'Facebook Messenger',
                        ])
                        ->required()
                        ->default(['whatsapp']),
                ]),

            Step::make('Atendimento')
                ->description('Janela de operação')
                ->icon('heroicon-o-clock')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TimePicker::make('hours_start')
                                ->label('Início do Atendimento')
                                ->seconds(false)
                                ->default('08:00'),
                            Forms\Components\TimePicker::make('hours_end')
                                ->label('Fim do Atendimento')
                                ->seconds(false)
                                ->default('18:00'),
                        ]),
                    Forms\Components\Placeholder::make('ai_magic')
                        ->label('')
                        ->content(new HtmlString('<div class="p-4 bg-primary-50 rounded-lg text-primary-600 border border-primary-200">✨ <strong>Atenção:</strong> Ao finalizar, a Inteligência Artificial irá gerar automaticamente: Saudação, Fluxo de conversas inicial, FAQ da sua área, Mensagens automáticas e Categorias.</div>')),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        $this->generateWithAi = true;
        $this->chatbotObjective = $data['chatbot_objective'] ?? 'suporte';
        $this->chatbotChannels = $data['chatbot_channels'] ?? ['site'];
        $this->chatbotSegment = $data['chatbot_segment'] ?? null;

        unset($data['chatbot_objective'], $data['chatbot_channels'], $data['chatbot_segment']);

        $data['status'] = Chatbot::STATUS_CONFIGURING;
        $data['use_ai'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->generateWithAi) {
            $chatbot = $this->record;
            $company = $chatbot->company;
            $segment = $this->chatbotSegment ?? $company->segment ?? 'Outro Segmento';
            $channels = $this->chatbotChannels;

            app(AgentPersonalizationService::class)->generateForSegment(
                $company,
                $chatbot,
                $segment,
                $this->chatbotObjective,
                $channels
            );

            // Set to READY after generating
            $chatbot->update(['status' => Chatbot::STATUS_READY]);

            Notification::make()
                ->title('IA configurou seu assistente com sucesso!')
                ->body('O assistente foi treinado com o segmento: '.$segment.'. Revise e publique!')
                ->success()
                ->send();
        }
    }
}
