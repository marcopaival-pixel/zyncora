<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Register as BaseRegister;

class CustomRegister extends BaseRegister
{
    protected static string $view = 'filament.pages.auth.custom-register';

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Wizard::make([
                    \Filament\Forms\Components\Wizard\Step::make('Dados de Acesso')
                        ->schema([
                            $this->getNameFormComponent()
                                ->label('Nome do responsável'),
                            $this->getEmailFormComponent(),
                            \Filament\Forms\Components\TextInput::make('phone')
                                ->label('Telefone / WhatsApp')
                                ->tel()
                                ->required()
                                ->maxLength(255),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),

                    \Filament\Forms\Components\Wizard\Step::make('Sua Empresa')
                        ->schema([
                            \Filament\Forms\Components\Radio::make('account_type')
                                ->label('Tipo de conta')
                                ->options([
                                    'empresa' => 'Empresa (CNPJ)',
                                    'autonomo' => 'Profissional Autônomo',
                                    'pf' => 'Pessoa Física (Teste)',
                                ])
                                ->default('empresa')
                                ->required()
                                ->live(),

                            \Filament\Forms\Components\TextInput::make('cnpj')
                                ->label('CNPJ')
                                ->mask('99.999.999/9999-99')
                                ->visible(fn (\Filament\Forms\Get $get) => $get('account_type') === 'empresa')
                                ->helperText('Consulta automática na Receita Federal.')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $state, \Filament\Forms\Set $set) {
                                    $cnpj = preg_replace('/[^0-9]/', '', $state);
                                    if (strlen($cnpj) === 14) {
                                        try {
                                            $response = \Illuminate\Support\Facades\Http::get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
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

                            \Filament\Forms\Components\TextInput::make('cpf')
                                ->label('CPF (Opcional)')
                                ->mask('999.999.999-99')
                                ->visible(fn (\Filament\Forms\Get $get) => $get('account_type') === 'autonomo'),

                            \Filament\Forms\Components\TextInput::make('company_name')
                                ->label('Nome da Empresa / Razão Social')
                                ->required()
                                ->maxLength(255),

                            \Filament\Forms\Components\TextInput::make('trade_name')
                                ->label('Nome Fantasia')
                                ->maxLength(255)
                                ->visible(fn (\Filament\Forms\Get $get) => $get('account_type') === 'empresa'),
                        ]),

                    \Filament\Forms\Components\Wizard\Step::make('Termos Legais')
                        ->schema([
                            \Filament\Forms\Components\Checkbox::make('terms_of_use')
                                ->label('Li e concordo com os Termos de Uso')
                                ->required()
                                ->accepted(),
                            \Filament\Forms\Components\Checkbox::make('privacy_policy')
                                ->label('Li e concordo com a Política de Privacidade')
                                ->required()
                                ->accepted(),
                        ]),
                ])
                ->submitAction(new \Illuminate\Support\HtmlString('<button type="submit" class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">Criar Conta</button>'))
            ])
            ->statePath('data');
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $trialPlan = \App\Models\Plan::where('slug', 'trial')->first();

            $company = \App\Models\Company::create([
                'name' => $data['company_name'],
                'phone' => $data['phone'],
                'slug' => \Illuminate\Support\Str::slug($data['company_name']) . '-' . rand(100, 999),
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

            \App\Models\SubscriptionAuditLog::create([
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
                'role' => \App\Models\User::ROLE_COMPANY_ADMIN,
                'status' => 'active',
            ]);

            // Save Legal Consents
            $termsDoc = \App\Models\PlatformLegalDocument::where('type', 'terms')->where('is_active', true)->latest('published_at')->first();
            if ($termsDoc) {
                \App\Models\PlatformLegalConsent::create([
                    'user_id' => $user->id,
                    'platform_legal_document_id' => $termsDoc->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            $privacyDoc = \App\Models\PlatformLegalDocument::where('type', 'privacy')->where('is_active', true)->latest('published_at')->first();
            if ($privacyDoc) {
                \App\Models\PlatformLegalConsent::create([
                    'user_id' => $user->id,
                    'platform_legal_document_id' => $privacyDoc->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $user;
        });
    }

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Crie a sua conta gratuita';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Automatize o seu atendimento agora mesmo.';
    }

    public function loginAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('login')
            ->link()
            ->label('Já tem uma conta? Faça login aqui')
            ->url(filament()->getLoginUrl());
    }
}
