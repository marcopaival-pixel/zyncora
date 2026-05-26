<x-filament-widgets::widget>
    <div class="rounded-[2.5rem] border border-white/10 bg-white/[0.015] backdrop-blur-2xl p-8 shadow-2xl relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/[0.03] to-transparent pointer-events-none"></div>
        
        <div class="relative z-10 mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-black uppercase italic tracking-[0.2em] text-white">Central de Comando</h2>
                <p class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Acesso rápido aos módulos operacionais</p>
            </div>
            <div class="p-2 rounded-xl bg-primary-500/10 border border-primary-500/20">
                <x-heroicon-o-command-line class="w-5 h-5 text-primary-400" />
            </div>
        </div>

        <div class="relative z-10 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($this->getActions() as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="group flex flex-col items-start gap-4 rounded-[1.5rem] border border-white/5 bg-white/[0.03] p-6 transition-all duration-300 hover:bg-white/[0.06] hover:border-primary-500/30 hover:scale-[1.02] active:scale-95 shadow-xl"
                >
                    <div class="flex items-center justify-between w-full">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-600 shadow-xl shadow-primary-600/10 text-white transition-all group-hover:scale-110">
                            <x-dynamic-component :component="$action['icon']" class="h-6 w-6" />
                        </span>
                        <x-heroicon-m-chevron-right class="h-4 w-4 shrink-0 text-slate-600 transition-all group-hover:text-primary-400 group-hover:translate-x-1" />
                    </div>
                    
                    <div class="min-w-0">
                        <p class="text-xs font-black text-white uppercase italic tracking-tight">
                            {{ $action['label'] }}
                        </p>
                        <p class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed opacity-60">
                            {{ $action['description'] }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
