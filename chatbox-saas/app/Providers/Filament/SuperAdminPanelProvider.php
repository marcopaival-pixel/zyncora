<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('super-admin')
            ->path('super-admin')
            ->login(CustomLogin::class)
            ->defaultThemeMode(ThemeMode::Dark)
            ->viteTheme('resources/css/app.css')
            ->spa(false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/SuperAdmin/Resources'), for: 'App\\Filament\\SuperAdmin\\Resources')
            ->discoverPages(in: app_path('Filament/SuperAdmin/Pages'), for: 'App\\Filament\\SuperAdmin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/SuperAdmin/Widgets'), for: 'App\\Filament\\SuperAdmin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => Blade::render('
                    <div x-data="{ showLogoutModal: false }" class="mt-auto p-4 w-full">
                        <button @click="showLogoutModal = true" type="button" class="w-full flex items-center gap-3 py-2 px-4 rounded-lg bg-zinc-800/50 hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 border border-zinc-700/50 hover:border-zinc-700 transition-all text-sm font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Sair do Sistema
                        </button>
                        
                        <template x-teleport="body">
                            <div x-show="showLogoutModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" x-transition.opacity>
                                <div @click.away="showLogoutModal = false" class="bg-white dark:bg-gray-900 rounded-[2rem] shadow-2xl border border-gray-200 dark:border-gray-800 p-8 max-w-sm w-full text-center space-y-6" x-transition>
                                    <div class="w-20 h-20 bg-rose-500/10 rounded-full flex items-center justify-center mx-auto">
                                        <svg class="w-10 h-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">Sair do Sistema?</h3>
                                        <p class="text-sm text-gray-500 mt-2 font-medium">Tem certeza que deseja encerrar a sua sessão? Você precisará fazer login novamente para voltar.</p>
                                    </div>
                                    <div class="flex gap-3">
                                        <button @click="showLogoutModal = false" class="w-1/2 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-xl font-bold transition-all">Cancelar</button>
                                        <form action="{{ filament()->getLogoutUrl() }}" method="post" class="w-1/2" onsubmit="try { localStorage.clear(); sessionStorage.clear(); } catch(e) {}">
                                            @csrf
                                            <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold shadow-lg shadow-rose-500/20 active:scale-95 transition-all">Sim, Sair</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                ')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => "
                    <div id='session-expired-modal' class='fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-md animate-in fade-in duration-300'>
                        <div class='bg-white dark:bg-gray-900 rounded-[2rem] shadow-2xl border border-gray-200 dark:border-gray-800 p-8 max-w-sm w-full text-center space-y-6 transform animate-in zoom-in-95 duration-300'>
                            <div class='w-20 h-20 bg-amber-500/10 rounded-full flex items-center justify-center mx-auto animate-pulse'>
                                <svg class='w-10 h-10 text-amber-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                            </div>
                            <div>
                                <h3 class='text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter'>Sessão Expirada</h3>
                                <p class='text-sm text-gray-500 mt-2 font-medium'>A sua conexão com o servidor foi interrompida ou o tempo limite foi atingido. Deseja recarregar a página para restaurar o acesso?</p>
                            </div>
                            <div class='flex flex-col gap-3'>
                                <button onclick='window.location.reload()' class='w-full py-4 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-bold shadow-lg shadow-primary-500/20 active:scale-95 transition-all'>
                                    Recarregar Agora
                                </button>
                                <button onclick='document.getElementById(\"session-expired-modal\").classList.replace(\"flex\", \"hidden\")' class='w-full py-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-xs font-bold transition-all'>
                                    Permanecer nesta página
                                </button>
                            </div>
                        </div>
                    </div>
                "
            );
    }
}
