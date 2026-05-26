import React from 'react';

const IntegrationPage = () => {
  const scriptCode = `<script 
  src="https://cdn.seusistema.com/chat-widget.js" 
  data-company-id="7a8b9c..." 
  defer
></script>`;

  return (
    <div className="p-8 space-y-8 animate-in slide-in-from-bottom duration-500">
      <header>
        <h2 className="text-3xl font-bold text-white">Instalação & Integração</h2>
        <p className="text-gray-400">Conecte o ChatBox ao seu site em poucos segundos.</p>
      </header>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Guia de Instalação */}
        <div className="lg:col-span-2 space-y-8">
          <section className="glass-card p-8 space-y-4">
            <h3 className="text-xl font-bold text-white">1. Copie o Código</h3>
            <p className="text-sm text-gray-400">Insira este script antes do fechamento da tag <code>&lt;/body&gt;</code> do seu site.</p>
            
            <div className="relative group">
              <pre className="bg-black/40 border border-white/10 rounded-2xl p-6 text-indigo-400 text-sm overflow-x-auto">
                {scriptCode}
              </pre>
              <button className="absolute top-4 right-4 bg-white/5 hover:bg-white/10 border border-white/10 text-white px-3 py-1 rounded-lg text-xs font-bold transition-all">
                Copiar
              </button>
            </div>
          </section>

          <section className="glass-card p-8 space-y-6">
            <h3 className="text-xl font-bold text-white">2. Personalize o Widget</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div className="space-y-4">
                 <p className="text-xs font-bold text-gray-500 uppercase tracking-widest">Cor Principal</p>
                 <div className="flex gap-2">
                   <ColorCircle color="#6366f1" active />
                   <ColorCircle color="#10b981" />
                   <ColorCircle color="#ef4444" />
                   <ColorCircle color="#f59e0b" />
                 </div>
               </div>
               <div className="space-y-4">
                 <p className="text-xs font-bold text-gray-500 uppercase tracking-widest">Posição</p>
                 <div className="flex gap-2">
                   <button className="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-white">Direita</button>
                   <button className="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-gray-500">Esquerda</button>
                 </div>
               </div>
            </div>
          </section>
        </div>

        {/* Preview do Widget */}
        <aside className="space-y-6">
          <div className="glass-card p-8 h-full flex flex-col items-center justify-center text-center space-y-4">
            <div className="w-20 h-20 bg-indigo-600 rounded-full flex items-center justify-center text-3xl shadow-xl shadow-indigo-600/30">💬</div>
            <h4 className="text-white font-bold">Pré-visualização</h4>
            <p className="text-xs text-gray-500">Veja como o chat aparecerá para seus visitantes em tempo real.</p>
            <button className="w-full py-3 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white hover:bg-white/10 transition-all">Ver Site de Exemplo</button>
          </div>
        </aside>
      </div>
    </div>
  );
};

const ColorCircle = ({ color, active }) => (
  <div 
    style={{ backgroundColor: color }} 
    className={`w-8 h-8 rounded-full cursor-pointer transition-all border-4 ${active ? 'border-white shadow-xl' : 'border-transparent hover:scale-110'}`}
  ></div>
);

export default IntegrationPage;
