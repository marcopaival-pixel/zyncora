<?php

namespace App\Providers;

use App\Events\MessageCreated;
use App\Listeners\QueueWhatsAppOutbound;
use App\Models\Message;
use App\Models\User;
use App\Observers\MessageObserver;
use App\Observers\UserObserver;
use Database\Seeders\DemoUsersSeeder;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\TenantService::class);

        $this->app->bind(\App\Contracts\Fiscal\IFiscalProvider::class, function ($app) {
            $provider = config('fiscal.default_provider', 'enotas');
            return match($provider) {
                'focus' => new \App\Services\Fiscal\Providers\FocusNFeProvider(),
                'asaas' => new \App\Services\Fiscal\Providers\AsaasFiscalProvider(),
                default => new \App\Services\Fiscal\Providers\ENotasProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(! app()->isProduction());

        $this->maybeSeedDemoUsersInLocalDev();
        $this->registerFilamentNavigationGroups();

        Table::configureUsing(function (Table $table): void {
            $table
                ->striped()
                ->paginationPageOptions([10, 25, 50, 100])
                ->defaultPaginationPageOption(25)
                ->extremePaginationLinks();
        });

        \Filament\Tables\Filters\SelectFilter::configureUsing(function (\Filament\Tables\Filters\SelectFilter $filter): void {
            $filter->native(false);
        });

        \Filament\Forms\Components\Select::configureUsing(function (\Filament\Forms\Components\Select $component): void {
            $component->native(false);
        });

        Message::observe(MessageObserver::class);
        User::observe(UserObserver::class);

        Event::listen(MessageCreated::class, QueueWhatsAppOutbound::class);
        Event::listen(\Illuminate\Auth\Events\Login::class, \App\Listeners\LogSuccessfulLogin::class);
        Event::listen(\Illuminate\Auth\Events\Failed::class, \App\Listeners\LogFailedLoginAttempt::class);
        Event::listen(\Illuminate\Auth\Events\Logout::class, \App\Listeners\HandleLogoutSession::class);

        \Filament\Forms\Components\Field::macro('withHelp', function (string $title, string $description, ?array $examples = null) {
            /** @var \Filament\Forms\Components\Field $this */
            return $this->hintAction(
                \Filament\Forms\Components\Actions\Action::make('help')
                    ->icon('heroicon-o-question-mark-circle')
                    ->modalHeading($title)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn () => view('filament.components.field-help-content', [
                        'description' => $description,
                        'examples' => $examples
                    ]))
            );
        });
    }

    /**
     * Regista os grupos de navegação do Filament com ícones e ordenação.
     * A ordem dos grupos no array determina a posição no menu lateral.
     */
    protected function registerFilamentNavigationGroups(): void
    {
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make('Atendimento')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->collapsed(),
                NavigationGroup::make('CRM')
                    ->icon('heroicon-o-building-office')
                    ->collapsed(),
                NavigationGroup::make('Automação')
                    ->icon('heroicon-o-bolt')
                    ->collapsed(),
                NavigationGroup::make('Integrações')
                    ->icon('heroicon-o-link')
                    ->collapsed(),
                NavigationGroup::make('Operação')
                    ->icon('heroicon-o-wrench')
                    ->collapsed(),
                NavigationGroup::make('LGPD')
                    ->icon('heroicon-o-shield-exclamation')
                    ->collapsed(false),
                NavigationGroup::make('Configurações de Acesso')
                    ->icon('heroicon-o-lock-closed')
                    ->collapsed(false),
                NavigationGroup::make('Segurança')
                    ->icon('heroicon-o-eye')
                    ->collapsed(),
                NavigationGroup::make('Plataforma')
                    ->icon('heroicon-o-server')
                    ->collapsed(),
                NavigationGroup::make('Gestão do Sistema')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ]);

            \Filament\Support\Facades\FilamentView::registerRenderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@livewire(\'global-help-button\')')
            );
        });
    }

    /**
     * Se AUTO_SEED_DEMO_USERS=true no .env e a app corre em local, repõe utilizadores demo
     * quando a tabela users está vazia (ex.: após migrate:fresh sem seed).
     * Nunca corre em produção.
     */
    protected function maybeSeedDemoUsersInLocalDev(): void
    {
        if (! app()->isLocal()) {
            return;
        }

        if (! filter_var(env('AUTO_SEED_DEMO_USERS', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        try {
            if (! Schema::hasTable('users') || User::query()->count() > 0) {
                return;
            }

            Artisan::call('db:seed', [
                '--class' => DemoUsersSeeder::class,
                '--force' => true,
            ]);
        } catch (\Throwable) {
            // Migrações em curso ou BD indisponível — ignorar.
        }
    }
}
