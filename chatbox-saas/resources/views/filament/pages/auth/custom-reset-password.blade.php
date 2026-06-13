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

        /* Medidor de força de senha */
        .password-strength-container {
            margin-top: 0.5rem;
        }
        .password-strength-bar {
            height: 4px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
            display: flex;
        }
        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        .password-strength-text {
            font-size: 0.65rem;
            margin-top: 0.25rem;
            text-align: right;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const passwordInput = document.querySelector('input[type="password"][wire\\:model="data.password"]');
                if (!passwordInput) return;

                // Criar elementos do medidor
                const container = document.createElement('div');
                container.className = 'password-strength-container';
                
                const barContainer = document.createElement('div');
                barContainer.className = 'password-strength-bar';
                
                const barFill = document.createElement('div');
                barFill.className = 'password-strength-fill';
                barContainer.appendChild(barFill);
                
                const textInfo = document.createElement('div');
                textInfo.className = 'password-strength-text text-gray-500';
                textInfo.innerText = 'Força da senha';

                container.appendChild(barContainer);
                container.appendChild(textInfo);

                // Inserir após o input wrapper (precisamos encontrar o contêiner do campo no Filament)
                const wrapper = passwordInput.closest('.fi-input-wrp').parentElement;
                wrapper.appendChild(container);

                passwordInput.addEventListener('input', function(e) {
                    const val = e.target.value;
                    let strength = 0;
                    
                    if (val.length >= 8) strength += 20;
                    if (/[A-Z]/.test(val)) strength += 20;
                    if (/[a-z]/.test(val)) strength += 20;
                    if (/[0-9]/.test(val)) strength += 20;
                    if (/[^A-Za-z0-9]/.test(val)) strength += 20;

                    barFill.style.width = strength + '%';

                    if (strength <= 20) {
                        barFill.style.backgroundColor = '#EF4444'; // Red
                        textInfo.innerText = 'MUITO FRACA';
                        textInfo.style.color = '#EF4444';
                    } else if (strength <= 40) {
                        barFill.style.backgroundColor = '#F97316'; // Orange
                        textInfo.innerText = 'FRACA';
                        textInfo.style.color = '#F97316';
                    } else if (strength <= 60) {
                        barFill.style.backgroundColor = '#F59E0B'; // Amber
                        textInfo.innerText = 'RAZOÁVEL';
                        textInfo.style.color = '#F59E0B';
                    } else if (strength <= 80) {
                        barFill.style.backgroundColor = '#3B82F6'; // Blue
                        textInfo.innerText = 'BOA';
                        textInfo.style.color = '#3B82F6';
                    } else {
                        barFill.style.backgroundColor = '#10B981'; // Green
                        textInfo.innerText = 'FORTE';
                        textInfo.style.color = '#10B981';
                    }
                });
            }, 500); // Aguardar renderização do Livewire
        });
    </script>
</x-filament-panels::page.simple>
