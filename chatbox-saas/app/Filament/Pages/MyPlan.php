<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Services\PlanUsageService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MyPlan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $title = 'Meu Plano e Consumo';

    protected static ?string $navigationLabel = 'Meu Plano';

    protected static string $view = 'filament.pages.my-plan';

    public $company;
    public $currentPlan;
    public $usageData;
    public $resultsMetrics;
    public $isTrial = false;
    public $trialDaysRemaining = 0;
    public $trialDaysTotal = 7; // Assumindo padrão
    public $trialPercentage = 0;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canAccessBilling() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->canAccessBilling() ?? false;
    }

    public function mount(PlanUsageService $planUsageService): void
    {
        $this->company = Auth::user()->company;
        $this->currentPlan = $this->company?->plan;
        
        if (!$this->company) {
            return;
        }

        $this->usageData = $planUsageService->getUsageData($this->company);
        $this->resultsMetrics = $planUsageService->getResultsMetrics($this->company);

        // Verificações de trial
        if ($this->company->subscription_status === 'trial') {
            $this->isTrial = true;
            $this->trialDaysRemaining = $this->company->calcularDiasRestantes();
            
            if ($this->company->trial_start_at && $this->company->trial_end_at) {
                $this->trialDaysTotal = $this->company->trial_start_at->diffInDays($this->company->trial_end_at, false) ?: 7;
            }
            
            $this->trialPercentage = $this->trialDaysTotal > 0 
                ? round((($this->trialDaysTotal - $this->trialDaysRemaining) / $this->trialDaysTotal) * 100)
                : 100;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('buy_credits')
                ->label('Comprar Créditos Extras')
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('package')
                        ->label('Pacote de Mensagens IA')
                        ->options([
                            'bronze' => 'Bronze (+500 Conversas) - R$ 29,00',
                            'silver' => 'Prata (+2.000 Conversas) - R$ 89,00',
                            'gold' => 'Ouro (+5.000 Conversas) - R$ 199,00',
                            'platinum' => 'Platinum (+15.000 Conversas) - R$ 499,00',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $company = auth()->user()->company;
                    $added = 0;
                    $price = 0;
                    
                    switch ($data['package']) {
                        case 'bronze': $added = 500; $price = 29; break;
                        case 'silver': $added = 2000; $price = 89; break;
                        case 'gold': $added = 5000; $price = 199; break;
                        case 'platinum': $added = 15000; $price = 499; break;
                    }

                    $stripeService = app(\App\Services\StripePaymentService::class);
                    
                    try {
                        $checkoutUrl = $stripeService->createOneOffCheckoutSession(
                            $company, 
                            auth()->user(),
                            $data['package'],
                            $price,
                            $added
                        );
                        
                        return redirect()->away($checkoutUrl);
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erro ao processar pagamento: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            \Filament\Actions\Action::make('configure_ai')
                ->label('Configurar Limites de IA')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\Select::make('ai_limit_action')
                        ->label('Ação ao Atingir o Limite')
                        ->options([
                            'block' => 'Bloquear IA (Padrão)',
                            'human_only' => 'Permitir apenas atendimento humano',
                            'auto_buy' => 'Comprar créditos automaticamente',
                        ])
                        ->default(fn () => auth()->user()->company?->ai_limit_action ?? 'block')
                        ->required()
                        ->live(),

                    \Filament\Forms\Components\Select::make('auto_buy_package')
                        ->label('Pacote para Compra Automática')
                        ->options([
                            'bronze' => 'Bronze (+500 Conversas)',
                            'silver' => 'Prata (+2.000 Conversas)',
                            'gold' => 'Ouro (+5.000 Conversas)',
                            'platinum' => 'Platinum (+15.000 Conversas)',
                        ])
                        ->default(fn () => auth()->user()->company?->auto_buy_package)
                        ->visible(fn (\Filament\Forms\Get $get) => $get('ai_limit_action') === 'auto_buy')
                        ->required(fn (\Filament\Forms\Get $get) => $get('ai_limit_action') === 'auto_buy'),
                ])
                ->action(function (array $data) {
                    $company = auth()->user()->company;
                    $company->update([
                        'ai_limit_action' => $data['ai_limit_action'],
                        'auto_buy_package' => $data['ai_limit_action'] === 'auto_buy' ? $data['auto_buy_package'] : null,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Configurações salvas')
                        ->success()
                        ->send();
                }),

            \App\Filament\Actions\HelpAction::make()->module('Meu Plano'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\AiConsumptionTimelineChart::class,
            \App\Filament\Widgets\AiIntentPieChart::class,
        ];
    }
}

