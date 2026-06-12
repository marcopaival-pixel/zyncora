<div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col md:flex-row">
    <!-- Lado Esquerdo (Marketing) -->
    <div class="hidden md:flex flex-col justify-center w-1/2 bg-gray-900 text-white p-12 relative overflow-hidden" style="background-image: url('{{ asset('images/register-bg.jpg') }}'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-gray-900/90 backdrop-blur-sm z-0"></div>
        <div class="relative z-10 max-w-lg mx-auto">
            <h1 class="text-4xl font-bold mb-6 font-['Outfit']">Comece a automatizar<br>seu atendimento hoje.</h1>
            <p class="text-gray-300 text-lg mb-8 font-['Inter']">Mais de 1.000 empresas utilizam agentes inteligentes para automatizar atendimentos com a Zynkora.</p>
            
            <ul class="space-y-4 mb-12">
                <li class="flex items-center gap-3 text-gray-200">
                    <div class="bg-primary-500/20 p-2 rounded-full">
                        <x-heroicon-s-check class="w-5 h-5 text-primary-400" />
                    </div>
                    <span>14 dias grátis de teste completo</span>
                </li>
                <li class="flex items-center gap-3 text-gray-200">
                    <div class="bg-primary-500/20 p-2 rounded-full">
                        <x-heroicon-s-check class="w-5 h-5 text-primary-400" />
                    </div>
                    <span>Sem exigência de cartão de crédito</span>
                </li>
                <li class="flex items-center gap-3 text-gray-200">
                    <div class="bg-primary-500/20 p-2 rounded-full">
                        <x-heroicon-s-check class="w-5 h-5 text-primary-400" />
                    </div>
                    <span>Ambiente seguro e dados protegidos pela LGPD</span>
                </li>
                <li class="flex items-center gap-3 text-gray-200">
                    <div class="bg-primary-500/20 p-2 rounded-full">
                        <x-heroicon-s-check class="w-5 h-5 text-primary-400" />
                    </div>
                    <span>Cancelamento a qualquer momento</span>
                </li>
            </ul>

            <div class="flex items-center gap-4 border-t border-gray-700 pt-8">
                <a href="/" class="text-gray-400 hover:text-white transition flex items-center gap-2">
                    <x-heroicon-m-arrow-left class="w-4 h-4" /> Voltar para o site
                </a>
            </div>
        </div>
    </div>

    <!-- Lado Direito (Formulário) -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">
            <!-- Logo para Mobile -->
            <div class="md:hidden mb-8 text-center">
                <a href="/" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white flex items-center justify-center gap-2">
                    <x-heroicon-m-arrow-left class="w-4 h-4" /> Voltar
                </a>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $this->getHeading() }}
                </h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ $this->getSubheading() }}
                </p>
            </div>

            <x-filament-panels::form wire:submit="register">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>

            <div class="mt-6 text-center text-sm text-gray-500">
                {{ $this->loginAction }}
            </div>
        </div>
    </div>
</div>
