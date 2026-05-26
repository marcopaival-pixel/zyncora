import React, { useState } from 'react';

const FlowEditor = () => {
  return (
    <div className="h-[calc(100vh-2rem)] m-4 rounded-3xl overflow-hidden glass flex flex-col">
      {/* Header do Editor */}
      <header className="p-6 border-b border-white/10 flex justify-between items-center bg-white/5">
        <div>
          <h2 className="text-xl font-bold text-white">Editor de Fluxo: Boas Vindas</h2>
          <p className="text-xs text-gray-500 uppercase font-bold tracking-widest mt-1">Status: Rascunho</p>
        </div>
        <div className="flex gap-3">
          <button className="px-5 py-2 rounded-xl text-sm font-bold text-gray-400 hover:text-white transition-all">Testar Bot</button>
          <button className="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/20 transition-all">Publicar Alterações</button>
        </div>
      </header>

      {/* Área do Canvas (Simulada) */}
      <div className="flex-1 bg-[radial-gradient(#ffffff0a_1px,transparent_1px)] [background-size:24px_24px] relative overflow-hidden">
        
        {/* Toolbox Lateral */}
        <aside className="absolute left-6 top-6 w-56 space-y-4 z-10">
          <div className="glass-card p-4">
            <h4 className="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-3">Arraste para o fluxo</h4>
            <div className="space-y-2">
              <NodeTool label="Enviar Mensagem" icon="💬" color="bg-blue-500" />
              <NodeTool label="Pergunta / Input" icon="❓" color="bg-purple-500" />
              <NodeTool label="Condição (IF)" icon="🔀" color="bg-orange-500" />
              <NodeTool label="Agendar Turno" icon="📅" color="bg-green-500" />
              <NodeTool label="Transferir" icon="👤" color="bg-red-500" />
            </div>
          </div>
        </aside>

        {/* Nós do Fluxo (Mockup Visual) */}
        <div className="absolute inset-0 flex items-center justify-center gap-20">
          <FlowNode 
            title="Início" 
            content="Quando o cliente envia a primeira mensagem" 
            type="start"
          />
          <div className="w-12 h-[2px] bg-indigo-500/50 relative">
            <div className="absolute -right-1 -top-1 w-2 h-2 rounded-full bg-indigo-500"></div>
          </div>
          <FlowNode 
            title="Mensagem" 
            content="Olá! Seja bem-vindo à nossa empresa. Como podemos ajudar?" 
            type="message"
            active
          />
          <div className="w-12 h-[2px] bg-indigo-500/50 relative">
             <div className="absolute -right-1 -top-1 w-2 h-2 rounded-full bg-indigo-500"></div>
          </div>
          <FlowNode 
            title="Menu Opções" 
            content="1. Suporte | 2. Vendas | 3. Outros" 
            type="choice"
          />
        </div>

        {/* Mini Map / Zoom Controls */}
        <div className="absolute right-6 bottom-6 flex flex-col gap-2">
          <ZoomBtn label="+" />
          <ZoomBtn label="-" />
          <ZoomBtn label="target" />
        </div>
      </div>
    </div>
  );
};

const NodeTool = ({ label, icon, color }) => (
  <div className="flex items-center gap-3 p-3 bg-white/5 border border-white/5 rounded-xl cursor-grab hover:bg-white/10 transition-all group">
    <div className={`w-8 h-8 ${color} rounded-lg flex items-center justify-center text-sm shadow-inner`}>{icon}</div>
    <span className="text-xs font-semibold text-gray-300 group-hover:text-white">{label}</span>
  </div>
);

const FlowNode = ({ title, content, type, active }) => (
  <div className={`w-64 glass-card p-5 border-2 transition-all ${active ? 'border-indigo-500 shadow-2xl shadow-indigo-500/20' : 'border-white/10 opacity-80 hover:opacity-100'}`}>
    <div className="flex items-center gap-2 mb-3">
      <span className={`w-2 h-2 rounded-full ${type === 'start' ? 'bg-green-500' : type === 'choice' ? 'bg-orange-500' : 'bg-blue-500'}`}></span>
      <h5 className="text-[10px] font-bold uppercase tracking-widest text-gray-400">{title}</h5>
    </div>
    <p className="text-sm text-gray-200 leading-relaxed">{content}</p>
    <div className="mt-4 pt-4 border-t border-white/5 flex justify-between items-center">
       <span className="text-[9px] font-bold text-gray-600 uppercase">Configurar</span>
       <div className="w-6 h-6 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-xs">⚙️</div>
    </div>
  </div>
);

const ZoomBtn = ({ label }) => (
  <button className="w-10 h-10 glass border border-white/10 rounded-xl font-bold text-gray-400 hover:text-white hover:bg-white/10 transition-all flex items-center justify-center">
    {label}
  </button>
);

export default FlowEditor;
