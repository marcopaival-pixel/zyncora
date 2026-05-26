<x-filament-panels::page.simple>
    <div class="fixed inset-0 -z-10 bg-[#020617] overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-primary-500/10 blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[60%] h-[60%] rounded-full bg-indigo-500/10 blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute top-[20%] right-[10%] w-[30%] h-[30%] rounded-full bg-emerald-500/5 blur-[100px] animate-pulse" style="animation-delay: 1s;"></div>
        
        <!-- Mesh Gradient Overlay -->
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 30%, rgba(139, 92, 246, 0.1) 0%, transparent 50%), radial-gradient(circle at 80% 70%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);"></div>
    </div>

    <div class="flex h-screen w-full items-center justify-center p-4 lg:p-8 overflow-hidden">
        <div class="grid w-full max-w-6xl max-h-[90vh] grid-cols-1 overflow-hidden rounded-[2.5rem] border border-white/10 bg-white/[0.02] shadow-2xl backdrop-blur-2xl lg:grid-cols-2">
            
            <!-- Left Side: Hero / Brand Experience -->
            <div class="relative hidden flex-col justify-between p-12 lg:flex bg-gradient-to-br from-white/[0.02] to-transparent">
                <div class="relative z-10">
                    <img src="{{ asset('images/logo.png') }}" alt="Zynkora" class="h-10 w-auto select-none brightness-110" />
                    
                    <div class="mt-8 space-y-4">
                        <h2 class="text-3xl font-black leading-tight tracking-tighter text-white uppercase italic xl:text-4xl">
                            A Inteligência que <br/>
                            <span class="text-primary-400">Escala o seu</span> <br/>
                            Atendimento.
                        </h2>
                        <p class="max-w-sm text-lg font-medium text-slate-400">
                            Plataforma omnichannel completa com IA generativa para transformar conversas em resultados.
                        </p>
                    </div>

                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-white/5 bg-white/[0.03] p-4 backdrop-blur-sm">
                            <div class="text-2xl font-black text-emerald-400">99.9%</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Uptime Garantido</div>
                        </div>
                        <div class="rounded-2xl border border-white/5 bg-white/[0.03] p-4 backdrop-blur-sm">
                            <div class="text-2xl font-black text-primary-400">24/7</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Monitoramento Ativo</div>
                        </div>
                    </div>
                </div>

                <!-- Orbital Visualization -->
                <div class="absolute inset-0 flex items-center justify-center opacity-20 pointer-events-none overflow-hidden">
                    <div class="relative w-[400px] h-[400px]">
                        <img src="{{ asset('images/zyncora-globo-orbital.svg') }}" alt="" class="w-full h-full animate-[spin_60s_linear_infinite]" />
                        <div class="absolute inset-0 bg-radial-gradient from-transparent to-[#020617] scale-110"></div>
                    </div>
                </div>

                <div class="relative z-10 flex items-center gap-4">
                    <div class="flex -space-x-2">
                        @for($i=1; $i<=3; $i++)
                            <div class="h-8 w-8 rounded-full border-2 border-[#020617] bg-slate-800 flex items-center justify-center text-[10px] font-bold text-white">
                                {{ chr(64 + $i) }}
                            </div>
                        @endfor
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                        Confiado por +500 empresas
                    </p>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="flex flex-col justify-center p-8 lg:p-12 xl:p-16 bg-white/[0.01] overflow-y-auto lg:overflow-hidden">
                <div class="mx-auto w-full max-w-sm space-y-6 xl:space-y-8">
                    <!-- Mobile Logo -->
                    <div class="flex justify-center lg:hidden mb-8">
                        <img src="{{ asset('images/logo.png') }}" alt="Zynkora" class="h-10 w-auto select-none brightness-110" />
                    </div>

                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-400 lg:mx-0">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            Sistema Operacional
                        </div>
                        <h1 class="mt-4 text-3xl font-black tracking-tighter text-white uppercase italic">
                            Bem-vindo de volta
                        </h1>
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            Insira suas credenciais para acessar o painel de controle.
                        </p>
                    </div>

                    <div class="login-form-wrapper">
                        <x-filament-panels::form wire:submit="authenticate">
                            {{ $this->form }}

                            <div class="space-y-4">
                                <x-filament-panels::form.actions 
                                    :actions="$this->getCachedFormActions()" 
                                    :full-width="true"
                                    class="!mt-4 transform transition-all active:scale-[0.98]" 
                                />

                                @if (filament()->hasPasswordReset())
                                    <div class="text-center">
                                        <a href="{{ filament()->getRequestPasswordResetUrl() }}"
                                            class="text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-primary-400 transition-colors">
                                            Esqueceu sua senha?
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </x-filament-panels::form>
                    </div>

                    <div class="pt-6 border-t border-white/5 text-center lg:text-left">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-600">
                            &copy; {{ date('Y') }} Zynkora AI. Segurança de nível empresarial.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom UI Overrides - NUCLEAR SCROLL RESET */
        :root {
            --primary-rgb: 139, 92, 246; /* Violet 500 */
        }

        /* Forçar remoção de qualquer scroll em nível de página */
        html, body {
            background: #020617 !important;
            height: 100vh !important;
            width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            position: fixed !important; /* Trava a rolagem de vez */
        }

        .fi-simple-layout {
            background: transparent !important;
            height: 100vh !important;
            width: 100vw !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
        }

        /* Ocultar o container padrão do Filament para usarmos o nosso grid customizado */
        .fi-simple-main {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            max-width: none !important;
            width: 100% !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
        }

        .fi-simple-header { display: none !important; }

        /* Custom Input Styling */
        .fi-input-wrp {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            border-radius: 1rem !important;
            transition: all 0.3s ease !important;
        }

        .fi-input-wrp:focus-within {
            background: rgba(15, 23, 42, 0.8) !important;
            border-color: rgba(139, 92, 246, 0.5) !important;
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.15) !important;
            transform: translateY(-1px);
        }

        .fi-input {
            color: white !important;
            font-weight: 500 !important;
        }

        /* Action Button */
        .fi-btn {
            height: 3.5rem !important;
            border-radius: 1rem !important;
            font-size: 0.875rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
            box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.4) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .fi-btn:hover {
            transform: translateY(-2px) scale(1.01) !important;
            box-shadow: 0 20px 30px -10px rgba(124, 58, 237, 0.6) !important;
        }

        /* Typography */
        h1, h2 {
            text-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Mobile specific adjustments */
        @media (max-width: 1024px) {
            .fi-simple-main {
                max-width: 28rem !important;
                margin: auto !important;
            }
            .grid {
                max-height: 100vh !important;
                border-radius: 0 !important;
                border: none !important;
            }
        }

        /* Short screens adjustments */
        @media (max-height: 750px) {
            .grid {
                max-height: 98vh !important;
            }
            .space-y-8, .space-y-6 {
                gap: 0.5rem !important;
            }
            .mt-16, .mt-8, .mt-4 {
                margin-top: 0.25rem !important;
            }
            .p-12, .p-16, .p-8 {
                padding: 1rem !important;
            }
            h1 { font-size: 1.25rem !important; }
            h2 { font-size: 1.5rem !important; }
            p { font-size: 0.7rem !important; }
            .h-10 { height: 1.5rem !important; }
            .login-form-wrapper { margin-top: 0.5rem !important; }
        }
    </style>
</x-filament-panels::page.simple>