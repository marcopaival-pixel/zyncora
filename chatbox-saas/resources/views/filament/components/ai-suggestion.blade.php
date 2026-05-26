<div class="p-6 space-y-6">
    <div class="relative p-8 bg-white/[0.02] border border-primary-500/30 rounded-[2rem] backdrop-blur-3xl shadow-2xl overflow-hidden group">
        <!-- AI Aura -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent pointer-events-none"></div>
        <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-primary-500/10 blur-[50px] transition-all group-hover:bg-primary-500/20"></div>

        <div class="absolute -top-3 left-6 px-4 py-1.5 bg-gradient-to-r from-primary-600 to-indigo-600 text-white text-[9px] font-black italic rounded-full uppercase tracking-[0.2em] shadow-xl shadow-primary-600/20 border border-white/20">
            Neuro-Engine Sugestão
        </div>
        
        <p id="ai-suggestion-text" class="text-slate-100 text-sm leading-relaxed italic font-medium mt-2">
            "{{ $suggestion }}"
        </p>

        <div class="mt-6 flex justify-end">
            <button 
                onclick="navigator.clipboard.writeText('{{ addslashes($suggestion) }}'); this.innerText = 'Sincronizado'; setTimeout(() => this.innerText = 'Copiar Resposta', 2000)"
                class="px-5 py-2.5 bg-white/5 hover:bg-white/10 text-primary-400 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all border border-white/10 italic shadow-lg"
            >
                Copiar Resposta
            </button>
        </div>
    </div>

    <div class="flex items-center justify-center gap-2 text-[9px] font-bold text-slate-600 uppercase tracking-widest italic">
        <x-heroicon-o-exclamation-triangle class="h-3 w-3 text-amber-500/50" />
        Auditoria Humana Obrigatória
    </div>
</div>
