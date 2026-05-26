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
            Nova Senha
        </h1>

        <div class="mt-2 flex items-center justify-center gap-2">
            <span class="flex h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[9px] font-bold text-emerald-500/80 uppercase tracking-[0.3em]">Defina sua nova credencial de acesso</span>
        </div>
    </div>

    <div class="login-form-section mx-auto max-w-md border-t border-white/5 pt-8">
        <x-filament-panels::form wire:submit="resetPassword">
            {{ $this->form }}

            <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="true"
                class="!mt-2" />
        </x-filament-panels::form>
    </div>

    <footer
        class="login-footer mx-auto mt-12 max-w-md text-center text-[10px] font-bold uppercase tracking-widest text-gray-500 opacity-40">
        &copy; {{ date('Y') }} Zynkora AI. Todos os direitos reservados.
    </footer>

    <style>
        /* Estilos idênticos ao login para consistência */
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

        .fi-simple-main {
            background: rgba(255, 255, 255, 0.02) !important;
            backdrop-filter: blur(16px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 2.5rem !important;
            padding: 3rem !important;
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
        }
    </style>
</x-filament-panels::page.simple>
