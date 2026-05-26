<x-filament-widgets::widget>
    @php
        $snapshot = $this->getSnapshot();
        $toneClasses = [
            'primary' => 'bg-primary-500/10 text-primary-700 border-primary-500/20 dark:text-primary-200 dark:bg-primary-500/20',
            'success' => 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20 dark:text-emerald-200 dark:bg-emerald-500/20',
            'warning' => 'bg-amber-500/10 text-amber-800 border-amber-500/20 dark:text-amber-200 dark:bg-amber-500/20',
            'info' => 'bg-sky-500/10 text-sky-700 border-sky-500/20 dark:text-sky-200 dark:bg-sky-500/20',
            'gray' => 'bg-gray-500/10 text-gray-700 border-gray-500/20 dark:text-gray-200 dark:bg-white/10',
        ];
    @endphp

    @php
        $snapshot = $this->getSnapshot();
        $toneClasses = [
            'primary' => 'bg-primary-500/10 text-primary-400 border-primary-500/20',
            'success' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'warning' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'info' => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
            'gray' => 'bg-white/5 text-slate-400 border-white/10',
        ];
    @endphp

    <div class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-white/[0.015] backdrop-blur-2xl p-8 shadow-2xl transition-all duration-700 hover:border-primary-500/30 group">
        <div class="absolute right-0 top-0 h-64 w-64 translate-x-12 -translate-y-12 rounded-full bg-primary-500/10 blur-[100px] transition-all duration-700 group-hover:bg-primary-500/20"></div>
        <div class="absolute bottom-0 left-0 h-64 w-64 -translate-x-12 translate-y-12 rounded-full bg-emerald-500/5 blur-[100px] transition-all duration-700 group-hover:bg-emerald-500/10"></div>

        <div class="relative z-10 flex flex-col gap-8">
            <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-primary-500/20 bg-primary-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-primary-400">
                        Indicadores de Performance
                    </div>
                    <h2 class="mt-4 text-3xl font-black tracking-tighter text-white uppercase italic md:text-4xl leading-tight">
                        {{ $snapshot['title'] }}
                    </h2>
                    <p class="mt-2 max-w-3xl text-sm font-medium text-slate-400">
                        {{ $snapshot['subtitle'] }}
                    </p>
                </div>

                <div class="hidden lg:block rounded-2xl border border-white/5 bg-white/[0.03] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 italic">
                    Dados processados em tempo real
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach($snapshot['metrics'] as $metric)
                    <div class="rounded-3xl border p-6 transition-all duration-500 hover:scale-[1.02] hover:bg-white/[0.03] {{ $toneClasses[$metric['tone']] ?? $toneClasses['gray'] }}">
                        <div class="text-[9px] font-black uppercase tracking-[0.2em] opacity-60">
                            {{ $metric['label'] }}
                        </div>
                        <div class="mt-3 text-3xl font-black tracking-tighter italic">
                            {{ $metric['value'] }}
                        </div>
                        <div class="mt-2 text-[10px] font-bold opacity-60 italic">
                            {{ $metric['description'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-3xl border border-white/5 bg-white/[0.02] p-6 transition-all hover:bg-white/[0.04]">
                    <div class="flex items-start gap-4">
                        <div class="rounded-2xl bg-primary-600 p-3 text-white shadow-xl shadow-primary-600/20">
                            <x-heroicon-o-presentation-chart-line class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white uppercase italic tracking-tight">
                                Insights Estratégicos
                            </h3>
                            <p class="mt-2 text-sm font-medium text-slate-400 leading-relaxed">
                                Utilize estes indicadores para validar o ROI da sua operação, identificar gargalos no atendimento e otimizar a conversão do pipeline CRM.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/5 bg-white/[0.02] p-6 transition-all hover:bg-white/[0.04]">
                    <h3 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">
                        Ações Recomendadas
                    </h3>

                    <div class="space-y-3">
                        @forelse($snapshot['actions'] as $action)
                            <a
                                href="{{ $action['url'] }}"
                                class="group flex items-center justify-between gap-4 rounded-2xl border border-white/5 bg-white/[0.03] p-4 transition-all hover:bg-white/[0.08] hover:translate-x-1 {{ $toneClasses[$action['tone']] ?? $toneClasses['gray'] }}"
                            >
                                <span>
                                    <span class="block text-xs font-black uppercase italic tracking-tight">{{ $action['label'] }}</span>
                                    <span class="mt-0.5 block text-[10px] font-medium opacity-60">{{ $action['description'] }}</span>
                                </span>
                                <x-heroicon-o-arrow-right class="h-4 w-4 shrink-0 transition group-hover:translate-x-1" />
                            </a>
                        @empty
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">
                                Operação configurada. Monitorando fluxos ativos.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
