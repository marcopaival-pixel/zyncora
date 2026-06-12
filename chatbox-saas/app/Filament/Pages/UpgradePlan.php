<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\Plan;
use App\Services\BillingCheckoutService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class UpgradePlan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $title = 'Planos e Assinatura';

    protected static ?string $navigationLabel = 'Upgrade de Plano';

    protected static string $view = 'filament.pages.upgrade-plan';

    protected static ?int $navigationSort = 10;

    public Company $company;

    public function mount(): void
    {
        $company = auth()->user()?->company;

        if ($company === null) {
            abort(403, 'Apenas usuários vinculados a uma empresa podem gerir planos.');
        }

        $this->company = $company;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canAccessBilling() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->canAccessBilling() ?? false;
    }

    public function changePlan(string $newPlan): void
    {
        if (app(BillingCheckoutService::class)->supportsCheckout()) {
            $plan = Plan::query()->where('slug', $newPlan)->first();
            $user = auth()->user();

            if ($plan && $user) {
                try {
                    // Redireciona para o CheckoutWizard passando o plano
                    $this->redirect(CheckoutWizard::getUrl(['plan' => $plan->id]), navigate: false);
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Erro no checkout')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            }

            return;
        }

        if (! config('chatbox.billing_simulation_enabled', false)) {
            Notification::make()
                ->title('Pagamento necessário')
                ->warning()
                ->body('A alteração de plano em produção requer checkout via gateway de pagamento.')
                ->send();

            return;
        }

        // Mocking payment success. In a real world, redirect to Stripe/MercadoPago.
        $limits = [
            'start' => ['users' => 1, 'attendants' => 1, 'channels' => 1, 'bots' => 1, 'ai' => 500],
            'professional' => ['users' => 5, 'attendants' => 5, 'channels' => 3, 'bots' => 3, 'ai' => 3000],
            'enterprise' => ['users' => 20, 'attendants' => 20, 'channels' => 10, 'bots' => 10, 'ai' => 10000],
        ];

        $this->company->update([
            'plan' => $newPlan,
            'max_users' => $limits[$newPlan]['users'],
            'max_attendants' => $limits[$newPlan]['attendants'],
            'max_channels' => $limits[$newPlan]['channels'],
            'max_chatbots' => $limits[$newPlan]['bots'],
            'ai_credits_balance' => $limits[$newPlan]['ai'], // Atribuir limite básico como saldo renovado no mock
            'expires_at' => now()->addMonth(),
        ]);

        Notification::make()
            ->title('Plano Atualizado!')
            ->success()
            ->body('Sua conta foi atualizada para o plano '.strtoupper($newPlan).' com sucesso.')
            ->send();

        $this->redirect(static::getUrl());
    }
}

