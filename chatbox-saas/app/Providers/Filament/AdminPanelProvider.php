<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\Auth\CustomRegister;
use App\Filament\Pages\Auth\CustomRequestPasswordReset;
use App\Filament\Pages\Auth\CustomResetPassword;
use App\Filament\Widgets\AdvancedMetrics;
use App\Filament\Widgets\CompanyUsageStats;
use App\Filament\Widgets\ConversationChart;
use App\Filament\Widgets\ConversationOverview;
use App\Filament\Widgets\CrmMetrics;
use App\Filament\Widgets\ExecutiveGrowthSnapshot;
use App\Filament\Widgets\LatestLogs;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\RecentConversations;
use App\Filament\Widgets\RecentDeals;
use App\Filament\Widgets\SystemHealthWidget;
use App\Filament\Widgets\TopLeadsWidget;
use App\Filament\Widgets\WelcomeHero;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class);

        if (config('chatbox.filament_registration_enabled', false)) {
            $panel = $panel->registration(CustomRegister::class);
        }

        return $panel
            ->passwordReset(CustomRequestPasswordReset::class)
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('5rem')
            ->favicon(asset('images/logo.png'))
            ->colors([
                'primary' => '#10b981', // Neon Emerald
                'gray' => Color::Zinc,  // Darker, sleeker than Slate
                'danger' => Color::Rose,
                'success' => '#10b981',
                'warning' => Color::Amber,
                'info' => Color::Cyan, // Cyan instead of Blue
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('260px')
            ->collapsedSidebarWidth('80px')
            ->brandLogo(function (): ?string {
                $company = Auth::user()?->company;

                return $company?->panel_logo_path ? asset('storage/'.$company->panel_logo_path) : null;
            })
            ->brandName(fn () => Auth::user()?->company?->name ?? 'Zynkora')
            ->defaultThemeMode(ThemeMode::Dark)
            ->viteTheme('resources/css/app.css')
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->userMenuItems([
                // Removido o override do botão de logout para permitir o comportamento nativo do Filament
                // O Javascript customizado já intercepta o form de logout e exibe o modal de confirmação.
            ])
            ->navigationGroups([
                NavigationGroup::make('Atendimento')
                    ->label('Atendimento Profissional')
                    ->collapsed(false),
                NavigationGroup::make('Automação')
                    ->label('Automação & Bot')
                    ->collapsed(true),
                NavigationGroup::make('Integrações')
                    ->label('Conexões & Canais')
                    ->collapsed(true),
                NavigationGroup::make('Plataforma')
                    ->label('Gestão & Cobrança')
                    ->collapsed(true),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                SystemHealthWidget::class,
                WelcomeHero::class,
                ExecutiveGrowthSnapshot::class,
                TopLeadsWidget::class,
                QuickActions::class,
                CompanyUsageStats::class,
                AdvancedMetrics::class,
                CrmMetrics::class,
                ConversationOverview::class,
                ConversationChart::class,
                RecentConversations::class,
                RecentDeals::class,
                LatestLogs::class,
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
                PanelsRenderHook::HEAD_START,
                function (): string {
                    $user = Auth::user();
                    if (!$user || !$user->company_id) return '';

                    $data = cache()->remember("company_head_assets_{$user->company_id}", 3600, function() use ($user) {
                        $company = $user->company;
                        return [
                            'color' => $company->brand_color ?? '#10b981',
                            'favicon' => $company->favicon_path ? asset('storage/'.$company->favicon_path) : null,
                        ];
                    });

                    $brandColor = $data['color'];
                    $favicon = $data['favicon'];

                    // Premium Background (Linear/Vercel inspired: deep zinc/black with very subtle neon mesh)
                    $html = "
                    <div class='fixed inset-0 -z-10 bg-[#09090b] overflow-hidden pointer-events-none'>
                        <div class='absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-emerald-500/5 blur-[120px] animate-pulse'></div>
                        <div class='absolute bottom-[-10%] right-[-10%] w-[60%] h-[60%] rounded-full bg-cyan-500/5 blur-[120px] animate-pulse' style='animation-delay: 2s;'></div>
                        <div class='absolute top-[20%] right-[10%] w-[30%] h-[30%] rounded-full bg-purple-500/5 blur-[100px] animate-pulse' style='animation-delay: 1s;'></div>
                    </div>
                    <style>
                        :root {
                            --primary-brand-color: {$brandColor};
                        }
                        
                        /* Scrollbar Premium */
                        ::-webkit-scrollbar { width: 4px !important; }
                        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1) !important; border-radius: 10px !important; }
                        ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2) !important; }
                        
                        /* Sidebar Refinement */
                        .fi-sidebar-group-label {
                            font-weight: 600 !important;
                            font-style: normal !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.1em !important;
                            font-size: 0.65rem !important;
                            color: #52525b !important; /* Zinc 500 */
                        }

                        /* Typography: Strong & Modern */
                        .fi-section-header-heading {
                            font-weight: 700 !important;
                            font-style: normal !important;
                            letter-spacing: -0.02em !important;
                            font-size: 0.95rem !important;
                            color: #e4e4e7 !important; /* Zinc 200 */
                        }

                        /* Tables Premium */
                        .fi-ta-header-cell-label {
                            font-weight: 600 !important;
                            font-style: normal !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.05em !important;
                            color: #71717a !important; /* Zinc 400 */
                        }

                        /* Hide Theme Switcher as we are locked in Dark Premium */
                        .fi-theme-switcher, 
                        [active-theme-mode-switcher],
                        .fi-topbar .fi-theme-switcher-container {
                            display: none !important;
                        }
                        
                        /* Fix Native Select Repeating Icons (ex: Pagination) */
                        select {
                            background-repeat: no-repeat !important;
                            background-position: right 0.5rem center !important;
                        }

                        /* Global Focus Style */
                        *:focus { outline: none !important; }
                        *:focus-visible { 
                            outline: 2px solid rgba(16, 185, 129, 0.5) !important; 
                            outline-offset: 2px !important; 
                        }
                    </style>";

                    $html .= "<script>
                        document.documentElement.classList.add('dark');
                    </script>";

                    if ($favicon) {
                        $html .= "<link rel='icon' type='image/x-icon' href='{$favicon}'>";
                    }

                    return $html;
                }
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
                    <script>
                        document.addEventListener('livewire:init', () => {
                            Livewire.hook('request', ({ fail }) => {
                                fail(({ status, preventDefault }) => {
                                    if (status === 419) {
                                        preventDefault();
                                        document.getElementById('session-expired-modal').classList.replace('hidden', 'flex');
                                    }
                                });
                            });
                        });
                    </script>
                "
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => Blade::render('
                    <form action="{{ filament()->getLogoutUrl() }}" method="post" class="mt-auto p-4 w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 py-2 px-4 rounded-lg bg-zinc-800/50 hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 border border-zinc-700/50 hover:border-zinc-700 transition-all text-sm font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Sair do Sistema
                        </button>
                    </form>
                ')
            )
            ->renderHook(
                PanelsRenderHook::SCRIPTS_BEFORE,
                fn (): string => Blade::render('@vite([\'resources/js/filament-echo.js\'])')
            );
    }
}
