<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Bot Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/[0.015] backdrop-blur-2xl p-6 shadow-2xl transition-all duration-500 hover:border-primary-500/30 group">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 rounded-xl bg-primary-500/10 border border-primary-500/20">
                        <x-heroicon-o-cpu-chip class="w-5 h-5 text-primary-400" />
                    </div>
                    <h3 class="text-sm font-black uppercase italic tracking-widest text-white">Status do Bot</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Identificação</span>
                        <span class="text-xs font-bold text-white italic">{{ $record->name }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Inteligência</span>
                        <x-filament::badge :color="$record->use_ai ? 'success' : 'gray'" class="font-black italic uppercase text-[9px] tracking-widest">
                            {{ $record->use_ai ? 'Generativa ON' : 'Fluxo Fixo' }}
                        </x-filament::badge>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Canal Ativo</span>
                        <x-filament::badge color="info" class="font-black italic uppercase text-[9px] tracking-widest">
                            {{ strtoupper($record->default_channel) }}
                        </x-filament::badge>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/[0.015] backdrop-blur-2xl p-6 shadow-2xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                        <x-heroicon-o-command-line class="w-5 h-5 text-emerald-400" />
                    </div>
                    <h3 class="text-sm font-black uppercase italic tracking-widest text-white">System Prompt</h3>
                </div>
                <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                    <p class="text-xs text-slate-400 font-medium italic leading-relaxed">
                        "{{ $record->ai_instruction ?? 'Nenhuma instrução específica configurada.' }}"
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="lg:col-span-2">
            <div class="flex flex-col h-[700px] bg-[#020617] rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/10 relative">
                <!-- Immersive Background Elements -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-30">
                    <div class="absolute top-[-20%] left-[-20%] w-full h-full rounded-full bg-primary-500/5 blur-[120px]"></div>
                    <div class="absolute bottom-[-20%] right-[-20%] w-full h-full rounded-full bg-emerald-500/5 blur-[120px]"></div>
                </div>

                <!-- Chat Header -->
                <div class="relative z-10 p-6 border-b border-white/10 bg-white/[0.02] backdrop-blur-3xl flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary-600 shadow-xl shadow-primary-600/20 flex items-center justify-center text-white relative">
                            <x-heroicon-o-sparkles class="w-7 h-7" />
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-[#020617] shadow-[0_0_10px_#10b981]"></span>
                        </div>
                        <div>
                            <h3 class="font-black text-white uppercase italic tracking-tight">{{ $record->name }}</h3>
                            <p class="text-[10px] text-emerald-400 font-black uppercase tracking-widest flex items-center gap-1.5 mt-0.5">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                </span>
                                Simulador Ativo
                            </p>
                        </div>
                    </div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-500 italic border border-white/5 rounded-full px-3 py-1 bg-white/[0.03]">
                        Ambiente Seguro
                    </div>
                </div>

                <!-- Chat Messages -->
                <div class="relative z-10 flex-1 overflow-y-auto p-8 space-y-6">
                    @foreach($chatHistory as $chat)
                        <div class="flex {{ $chat['role'] === 'user' ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <div class="max-w-[85%] relative">
                                <div class="rounded-3xl px-6 py-3 shadow-xl backdrop-blur-md
                                    {{ $chat['role'] === 'user' 
                                        ? 'bg-primary-600/90 text-white rounded-tr-none border border-white/10' 
                                        : 'bg-white/[0.03] text-white border border-white/5 rounded-tl-none' }}">
                                    <p class="text-sm font-medium leading-relaxed">{{ $chat['content'] }}</p>
                                </div>
                                <p class="text-[8px] mt-2 font-black uppercase tracking-widest opacity-40 px-2 {{ $chat['role'] === 'user' ? 'text-right' : 'text-left' }}">
                                    {{ $chat['time'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Chat Input -->
                <div class="relative z-10 p-6 bg-white/[0.02] backdrop-blur-3xl border-t border-white/10">
                    <form wire:submit.prevent="sendMessage" class="chat-form-modern">
                        {{ $this->form }}
                        
                        <div class="flex justify-between items-center mt-4">
                            <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest italic">
                                &copy; Zynkora AI · Modo Debug
                            </p>
                            <span class="text-[9px] text-slate-500 font-medium">Pressione Enter para enviar</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
