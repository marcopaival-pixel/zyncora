<?php

namespace App\Providers;

use App\Contracts\Fiscal\IFiscalProvider;
use App\Events\MessageCreated;
use App\Listeners\HandleLogoutSession;
use App\Listeners\LogFailedLoginAttempt;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\QueueWhatsAppOutbound;
use App\Models\Message;
use App\Models\Plan;
use App\Models\User;
use App\Observers\MessageObserver;
use App\Observers\PlanObserver;
use App\Observers\UserObserver;
use App\Services\Fiscal\Providers\AsaasFiscalProvider;
use App\Services\Fiscal\Providers\ENotasProvider;
use App\Services\Fiscal\Providers\FocusNFeProvider;
use App\Services\TenantService;
use Database\Seeders\DemoUsersSeeder;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
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
        $this->app->singleton(TenantService::class);

        $this->app->bind(IFiscalProvider::class, function ($app) {
            $provider = config('fiscal.default_provider', 'enotas');

            return match ($provider) {
                'focus' => new FocusNFeProvider,
                'asaas' => new AsaasFiscalProvider,
                default => new ENotasProvider,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        $this->maybeSeedDemoUsersInLocalDev();
        $this->registerFilamentNavigationGroups();

        Table::configureUsing(function (Table $table): void {
            $table
                ->striped()
                ->paginationPageOptions([10, 25, 50, 100])
                ->defaultPaginationPageOption(25)
                ->extremePaginationLinks();
        });

        SelectFilter::configureUsing(function (SelectFilter $filter): void {
            $filter->native(false);
        });

        Select::configureUsing(function (Select $component): void {
            $component->native(false);
        });

        Message::observe(MessageObserver::class);
        User::observe(UserObserver::class);
        Plan::observe(PlanObserver::class);

        Event::listen(MessageCreated::class, QueueWhatsAppOutbound::class);
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Failed::class, LogFailedLoginAttempt::class);
        Event::listen(Logout::class, HandleLogoutSession::class);

        Field::macro('withHelp', function (string $title, string $description, ?array $examples = null) {
            /** @var Field $this */
            return $this->hintAction(
                Action::make('help')
                    ->icon('heroicon-o-question-mark-circle')
                    ->modalHeading($title)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn () => view('filament.components.field-help-content', [
                        'description' => $description,
                        'examples' => $examples,
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

            FilamentView::registerRenderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => auth()->check() ? Blade::render('@livewire(\'global-help-button\')') : ''
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
