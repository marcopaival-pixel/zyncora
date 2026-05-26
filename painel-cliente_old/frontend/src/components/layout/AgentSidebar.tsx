import React from 'react';

const AgentSidebar = () => {
  return (
    <aside className="w-20 lg:w-64 h-screen glass border-r border-white/10 flex flex-col p-4 sticky top-0">
      <div className="flex items-center gap-3 mb-10 px-2 lg:px-4">
        <div className="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-600/20">A</div>
        <div className="hidden lg:block">
           <h1 className="text-sm font-bold text-white">Atendimento</h1>
           <p className="text-[10px] text-gray-500 uppercase font-bold">Painel do Agente</p>
        </div>
      </div>

      <nav className="flex-1 space-y-2">
        <AgentNavItem label="Conversas" icon="💬" active />
        <AgentNavItem label="Meus Dados" icon="👤" />
        <AgentNavItem label="Respostas" icon="⚡" />
      </nav>

      <div className="mt-auto space-y-2 border-t border-white/5 pt-4">
        <div className="hidden lg:block px-4 mb-4">
           <div className="bg-green-500/10 border border-green-500/20 rounded-xl p-3 flex items-center gap-2">
              <span className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
              <span className="text-[10px] font-bold text-green-500 uppercase">Disponível</span>
           </div>
        </div>
        <AgentNavItem label="Sair" icon="🚪" />
      </div>
    </aside>
  );
};

const AgentNavItem = ({ label, icon, active }) => (
  <button className={`w-full flex items-center gap-4 px-4 py-3 rounded-xl transition-all group ${active ? 'bg-white/5 text-white' : 'text-gray-500 hover:text-white hover:bg-white/5'}`}>
    <span className="text-xl">{icon}</span>
    <span className="hidden lg:block text-sm font-bold">{label}</span>
  </button>
);

export default AgentSidebar;
