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
    public \Illuminate\Database\Eloquent\Collection $plans;

    public function mount(): void
    {
        $company = auth()->user()?->company;

        if ($company === null) {
            if (auth()->user()?->isPlatformAdmin()) {
                $company = new Company([
                    'name' => 'Visualização de Admin',
                    'plan' => null,
                ]);
            } else {
                abort(403, 'Apenas usuários vinculados a uma empresa podem gerir planos.');
            }
        }

        $this->company = $company;
        $this->plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
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
        $user = auth()->user();

        if ($user && $user->isPlatformAdmin() && $user->company_id === null) {
            Notification::make()
                ->title('Ação Restrita')
                ->warning()
                ->body('Administradores de plataforma não podem assinar planos. Utilize a gestão de Empresas no painel Super Admin.')
                ->send();
            return;
        }

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
        $plan = Plan::where('slug', $newPlan)->first();
        if ($plan) {
            $this->company->update([
                'plan' => $newPlan,
                'max_users' => $plan->max_users,
                'max_attendants' => $plan->max_attendants,
                'max_channels' => $plan->max_channels,
                'max_chatbots' => $plan->max_chatbots,
                'ai_credits_balance' => $plan->max_ai_conversations,
                'expires_at' => now()->addMonth(),
            ]);
        }

        Notification::make()
            ->title('Plano Atualizado!')
            ->success()
            ->body('Sua conta foi atualizada para o plano '.strtoupper($newPlan).' com sucesso.')
            ->send();

        $this->redirect(static::getUrl());
    }
}

