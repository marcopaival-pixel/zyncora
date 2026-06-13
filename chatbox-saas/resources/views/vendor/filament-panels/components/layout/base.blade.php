@props([
    'livewire' => null,
])

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ __('filament-panels::layout.direction') ?? 'ltr' }}"
    @class([
        'fi min-h-screen dark bg-[#09090b]',
    ])
>
    <head>
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_START, scopes: $livewire?->getRenderHookScopes()) }}

        <meta charset="utf-8" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @if ($favicon = filament()->getFavicon())
            <link rel="icon" href="{{ $favicon }}" />
        @endif

        @php
            $title = trim(strip_tags(($livewire ?? null)?->getTitle() ?? ''));
            $brandName = trim(strip_tags(filament()->getBrandName()));
        @endphp

        <title>
            {{ filled($title) ? "{$title} - " : null }} {{ $brandName }}
        </title>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_BEFORE, scopes: $livewire?->getRenderHookScopes()) }}

        <style>
            [x-cloak=''],
            [x-cloak='x-cloak'],
            [x-cloak='1'] {
                display: none !important;
            }

            @media (max-width: 1023px) {
                [x-cloak='-lg'] {
                    display: none !important;
                }
            }

            @media (min-width: 1024px) {
                [x-cloak='lg'] {
                    display: none !important;
                }
            }
        </style>

        @filamentStyles

        {{ filament()->getTheme()->getHtml() }}
        {{ filament()->getFontHtml() }}

        <style>
            :root {
                --font-family: '{!! filament()->getFontFamily() !!}';
                --sidebar-width: {{ filament()->getSidebarWidth() }};
                --collapsed-sidebar-width: {{ filament()->getCollapsedSidebarWidth() }};
                --default-theme-mode: {{ filament()->getDefaultThemeMode()->value }};
            }
        </style>

        @stack('styles')

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_AFTER, scopes: $livewire?->getRenderHookScopes()) }}

        @if (! filament()->hasDarkMode())
            <script>
                localStorage.setItem('theme', 'light')
            </script>
        @elseif (filament()->hasDarkModeForced())
            <script>
                localStorage.setItem('theme', 'dark')
            </script>
        @else
            <script>
                const loadDarkMode = () => {
                    window.theme = localStorage.getItem('theme') ?? @js(filament()->getDefaultThemeMode()->value)

                    if (
                        window.theme === 'dark' ||
                        (window.theme === 'system' &&
                            window.matchMedia('(prefers-color-scheme: dark)')
                                .matches)
                    ) {
                        document.documentElement.classList.add('dark')
                    }
                }

                loadDarkMode()

                document.addEventListener('livewire:navigated', loadDarkMode)
            </script>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_END, scopes: $livewire?->getRenderHookScopes()) }}

        <!-- ZYNKORA DIAGNOSTIC SCRIPT (NavFix) -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const navLog = (msg, data) => {
                    const logData = {
                        timestamp: new Date().toISOString(),
                        ...data
                    };
                    console.log(`[NavDiag] ${msg}`, logData);
                };

                let lastClick = null;
                
                document.body.addEventListener('click', (e) => {
                    if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
                    
                    // Expansão: Aplicar o fallback agressivo a TODOS os links da plataforma
                    const link = e.target.closest('a[href]');
                    if (link && link.href && !link.href.includes('javascript:') && !link.getAttribute('href').startsWith('#') && link.getAttribute('target') !== '_blank') {
                        const now = performance.now();
                        lastClick = {
                            time: now,
                            url: link.href,
                            resolved: false
                        };
                        
                        navLog('Clique realizado em Link', { 
                            destino: link.href, 
                            pagina_atual: window.location.href,
                            texto: link.innerText.trim().substring(0, 30)
                        });

                        // Fallback agressivo: se após 400ms a navegação não for desencadeada.
                        // Resolve o bug do "clique duplo" globalmente (Tooltips / Touch / MorphDOM).
                        setTimeout(() => {
                            if (lastClick && lastClick.url === link.href && !lastClick.resolved) {
                                navLog('ALERTA: Clique não propagou navegação nativa. Forçando redirecionamento.', {
                                    destino: link.href,
                                    tempo_decorrido: Math.round(performance.now() - now) + 'ms'
                                });
                                window.location.href = link.href;
                            }
                        }, 400);
                    }
                }, true); // Capturing phase to avoid stopPropagation

                window.addEventListener('beforeunload', () => {
                    if (lastClick && !lastClick.resolved) {
                        lastClick.resolved = true;
                        navLog('Navegação nativa iniciada com sucesso.', {
                            destino: lastClick.url,
                            tempo_decorrido: Math.round(performance.now() - lastClick.time) + 'ms'
                        });
                    }
                });
                
                // Tratar requests pushState do livewire:navigate se existirem acidentalmente
                document.addEventListener('livewire:navigating', () => {
                    if (lastClick && !lastClick.resolved) {
                        lastClick.resolved = true;
                        navLog('Navegação via Livewire Navigate detectada.', { destino: lastClick.url });
                    }
                });
                
                navLog('Módulo de Diagnóstico Global Iniciado', { url: window.location.href });
            });
        </script>
    </head>

    <body
        {{ $attributes
                ->merge(($livewire ?? null)?->getExtraBodyAttributes() ?? [], escape: false)
                ->class([
                    'fi-body',
                    'fi-panel-' . filament()->getId(),
                    'min-h-screen bg-gray-50 font-normal text-gray-950 antialiased dark:bg-gray-950 dark:text-white',
                ]) }}
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_START, scopes: $livewire?->getRenderHookScopes()) }}

        {{ $slot }}

        @livewire(Filament\Livewire\Notifications::class)

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_BEFORE, scopes: $livewire?->getRenderHookScopes()) }}

        @filamentScripts(withCore: true)

        @if (filament()->hasBroadcasting() && config('filament.broadcasting.echo'))
            <script data-navigate-once>
                window.Echo = new window.EchoFactory(@js(config('filament.broadcasting.echo')))

                window.dispatchEvent(new CustomEvent('EchoLoaded'))
            </script>
        @endif

        @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
            <script>
                loadDarkMode()
            </script>
        @endif

        @stack('scripts')

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_AFTER, scopes: $livewire?->getRenderHookScopes()) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_END, scopes: $livewire?->getRenderHookScopes()) }}
    </body>
</html>
