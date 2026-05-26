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
                    $checkoutUrl = app(BillingCheckoutService::class)->checkoutUrl($this->company, $plan, $user);
                    $this->redirect($checkoutUrl, navigate: false);
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
            'basic' => ['users' => 1, 'channels' => 1, 'bots' => 1],
            'pro' => ['users' => 5, 'channels' => 3, 'bots' => 5],
            'enterprise' => ['users' => 20, 'channels' => 10, 'bots' => 20],
        ];

        $this->company->update([
            'plan' => $newPlan,
            'max_users' => $limits[$newPlan]['users'],
            'max_channels' => $limits[$newPlan]['channels'],
            'max_chatbots' => $limits[$newPlan]['bots'],
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
