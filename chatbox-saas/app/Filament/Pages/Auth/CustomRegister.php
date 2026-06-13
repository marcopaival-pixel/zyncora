<?php

namespace App\Filament\Pages\Auth;

use App\Models\Company;
use App\Models\Plan;
use App\Models\PlatformLegalConsent;
use App\Models\PlatformLegalDocument;
use App\Models\SubscriptionAuditLog;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CustomRegister extends BaseRegister
{
    protected static string $view = 'filament.pages.auth.custom-register';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Dados de Acesso')
                        ->schema([
                            $this->getNameFormComponent()
                                ->label('Nome do responsável'),
                            $this->getEmailFormComponent(),
                            TextInput::make('phone')
                                ->label('Telefone / WhatsApp')
                                ->tel()
                                ->required()
                                ->maxLength(255),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),

                    Step::make('Sua Empresa')
                        ->schema([
                            Radio::make('account_type')
                                ->label('Tipo de conta')
                                ->options([
                                    'empresa' => 'Empresa (CNPJ)',
                                    'autonomo' => 'Profissional Autônomo',
                                    'pf' => 'Pessoa Física (Teste)',
                                ])
                                ->default('empresa')
                                ->required()
                                ->live(),

                            TextInput::make('cnpj')
                                ->label('CNPJ')
                                ->mask('99.999.999/9999-99')
                                ->visible(fn (Get $get) => $get('account_type') === 'empresa')
                                ->helperText('Consulta automática na Receita Federal.')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $state, Set $set) {
                                    $cnpj = preg_replace('/[^0-9]/', '', $state);
                                    if (strlen($cnpj) === 14) {
                                        try {
                                            $response = Http::get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
                                            if ($response->successful()) {
                                                $data = $response->json();
                                                $set('company_name', $data['razao_social'] ?? '');
                                                $set('trade_name', $data['nome_fantasia'] ?? '');
                                            }
                                        } catch (\Exception $e) {
                                            // Silent fail
                                        }
                                    }
                                }),

                            TextInput::make('cpf')
                                ->label('CPF (Opcional)')
                                ->mask('999.999.999-99')
                                ->visible(fn (Get $get) => $get('account_type') === 'autonomo'),

                            TextInput::make('company_name')
                                ->label('Nome da Empresa / Razão Social')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('trade_name')
                                ->label('Nome Fantasia')
                                ->maxLength(255)
                                ->visible(fn (Get $get) => $get('account_type') === 'empresa'),
                        ]),

                    Step::make('Termos Legais')
                        ->schema([
                            Checkbox::make('terms_of_use')
                                ->label('Li e concordo com os Termos de Uso')
                                ->required()
                                ->accepted(),
                            Checkbox::make('privacy_policy')
                                ->label('Li e concordo com a Política de Privacidade')
                                ->required()
                                ->accepted(),
                        ]),
                ])
                    ->submitAction(new HtmlString('<button type="submit" class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">Criar Conta</button>')),
            ])
            ->statePath('data');
    }

    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $trialPlan = Plan::where('slug', 'trial')->first();

            $company = Company::create([
                'name' => $data['company_name'],
                'phone' => $data['phone'],
                'slug' => Str::slug($data['company_name']).'-'.rand(100, 999),
                'status' => 'active',
                'plan_id' => $trialPlan?->id,
                'plan' => 'trial',
                'max_users' => $trialPlan?->max_users ?? 1,
                'max_channels' => $trialPlan?->max_channels ?? 1,
                'max_chatbots' => $trialPlan?->max_chatbots ?? 3,
                'trial_start_at' => now(),
                'trial_end_at' => now()->addDays(14),
                'subscription_status' => 'trial',
                'is_onboarding_completed' => false,
            ]);

            SubscriptionAuditLog::create([
                'company_id' => $company->id,
                'action' => 'trial_started',
                'new_status' => 'trial',
                'trial_start_at' => $company->trial_start_at,
                'trial_end_at' => $company->trial_end_at,
                'notes' => 'Empresa criada com Trial Gratuito de 14 dias.',
            ]);

            $user = $this->getUserModel()::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'],
                'company_id' => $company->id,
                'role' => User::ROLE_COMPANY_ADMIN,
                'status' => 'active',
            ]);

            // Save Legal Consents
            $termsDoc = PlatformLegalDocument::where('type', 'terms')->where('is_active', true)->latest('published_at')->first();
            if ($termsDoc) {
                PlatformLegalConsent::create([
                    'user_id' => $user->id,
                    'platform_legal_document_id' => $termsDoc->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            $privacyDoc = PlatformLegalDocument::where('type', 'privacy')->where('is_active', true)->latest('published_at')->first();
            if ($privacyDoc) {
                PlatformLegalConsent::create([
                    'user_id' => $user->id,
                    'platform_legal_document_id' => $privacyDoc->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $user;
        });
    }

    public function getHeading(): string|Htmlable
    {
        return 'Crie a sua conta gratuita';
    }

    public function getSubheading(): string|Htmlable
    {
        return 'Automatize o seu atendimento agora mesmo.';
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Já tem uma conta? Faça login aqui')
            ->url(filament()->getLoginUrl());
    }
}
