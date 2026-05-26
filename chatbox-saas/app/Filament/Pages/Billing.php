<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Services\BillingCheckoutService;
use App\Services\PlanSubscriptionService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Billing extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $title = 'Assinatura e Planos';

    protected static ?string $navigationLabel = 'Assinatura';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canAccessBilling() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->canAccessBilling() ?? false;
    }

    protected static string $view = 'filament.pages.billing';

    public $plans;

    public $currentPlanId;

    public function mount(): void
    {
        $this->plans = Plan::where('is_active', true)->get();

        $user = Auth::user();
        $this->currentPlanId = $user->company?->plan_id;

        if (request()->query('checkout') === 'success') {
            Notification::make()
                ->title('Pagamento recebido')
                ->body(app(BillingCheckoutService::class)->checkoutSuccessMessage())
                ->success()
                ->send();
        }
    }

    public function selectPlan($planId): void
    {
        $plan = Plan::find($planId);
        $user = Auth::user();
        $company = $user?->company;

        if (! $plan || ! $company) {
            Notification::make()
                ->title('Erro')
                ->body('Plano ou empresa não encontrados.')
                ->danger()
                ->send();

            return;
        }

        if (app(BillingCheckoutService::class)->supportsCheckout()) {
            try {
                $checkoutUrl = app(BillingCheckoutService::class)->checkoutUrl($company, $plan, $user);
                $this->redirect($checkoutUrl, navigate: false);
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Erro no checkout')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }

            return;
        }

        if (! config('chatbox.billing_simulation_enabled', false)) {
            Notification::make()
                ->title('Pagamento necessário')
                ->body('Configure PAYMENT_DRIVER=stripe|mercadopago ou active BILLING_SIMULATION_ENABLED apenas em desenvolvimento.')
                ->warning()
                ->send();

            return;
        }

        app(PlanSubscriptionService::class)->applyPlanToCompany($company, $plan);

        $this->currentPlanId = $plan->id;

        Notification::make()
            ->title('Plano Atualizado!')
            ->body("Sua empresa agora está no plano {$plan->name}. Os novos limites já estão ativos.")
            ->success()
            ->send();
    }
}
