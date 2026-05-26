<x-filament-panels::page.simple>
    <!-- Background Decorativos (Premium WOW) -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div
            class="absolute -top-[10%] -left-[10%] w-[60%] h-[60%] rounded-full bg-primary-500/10 blur-[120px] animate-pulse">
        </div>
        <div
            class="absolute -bottom-[10%] -right-[10%] w-[50%] h-[50%] rounded-full bg-indigo-500/10 blur-[120px] animate-pulse">
        </div>
    </div>

    <div class="login-hero mx-auto mb-8 max-w-md text-center">
        <!-- Logo Principal (Zynkora) -->
        <div class="mb-4 flex justify-center transform hover:scale-105 transition-transform duration-500">
            <img src="{{ asset('images/logo.png') }}" width="320" height="80" alt="Zynkora"
                class="login-zynkora-wordmark h-12 w-auto object-contain object-center select-none drop-shadow-[0_0_15px_rgba(255,255,255,0.1)]"
                loading="eager" />
        </div>

        <h1 class="text-xl font-black tracking-tight text-white uppercase italic text-center mx-auto">
            Recuperar Senha
        </h1>

        <div class="mt-2 flex items-center justify-center gap-2">
            <span class="flex h-1.5 w-1.5 rounded-full bg-primary-500 animate-pulse"></span>
            <span class="text-[9px] font-bold text-primary-500/80 uppercase tracking-[0.3em]">Instruções serão enviadas por e-mail</span>
        </div>
    </div>

    <div class="login-form-section mx-auto max-w-md border-t border-white/5 pt-8">
        <x-filament-panels::form wire:submit="request">
            {{ $this->form }}

            <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="true"
                class="!mt-2" />
                
            <div class="text-center mt-6">
                <a href="{{ filament()->getLoginUrl() }}"
                    class="text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-white transition-colors">
                    &larr; Voltar para o login
                </a>
            </div>
        </x-filament-panels::form>
    </div>

    <footer
        class="login-footer mx-auto mt-12 max-w-md text-center text-[10px] font-bold uppercase tracking-widest text-gray-500 opacity-40">
        &copy; {{ date('Y') }} Zynkora AI. Todos os direitos reservados.
    </footer>

    <style>
        /* Desativar barra de rolagem e fixar container */
        html,
        body {
            height: 100vh !important;
            overflow: hidden !important;
            background: #0f172a !important;
        }

        .fi-simple-layout {
            height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: radial-gradient(circle at 50% 0%, #1e293b, #0f172a) !important;
        }

        /* Cartão Glassmorphism Premium */
        .fi-simple-main {
            background: rgba(255, 255, 255, 0.02) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 2.5rem !important;
            padding: 3rem !important;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset !important;
        }

        .fi-simple-main .fi-input-wrp {
            background: rgba(0, 0, 0, 0.2) !important;
            border-radius: 1rem !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .fi-simple-main .fi-btn {
            border-radius: 1rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            font-weight: 800 !important;
            box-shadow: 0 10px 15px -3px rgba(var(--primary-rgb), 0.3) !important;
        }
    </style>
</x-filament-panels::page.simple>
