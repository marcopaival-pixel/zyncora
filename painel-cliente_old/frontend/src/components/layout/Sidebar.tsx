import React from 'react';

const Sidebar = () => {
  return (
    <aside className="w-72 h-screen glass border-r border-white/10 flex flex-col p-6 sticky top-0 overflow-y-auto">
      <div className="flex items-center gap-4 mb-10 px-2">
        <div className="w-12 h-12 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
          <span className="text-xl font-black text-white">CB</span>
        </div>
        <div>
          <h1 className="text-xl font-extrabold text-white tracking-tight">ChatBox</h1>
          <p className="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">SaaS Edition</p>
        </div>
      </div>

      <nav className="flex-1 space-y-8">
        <div>
          <p className="text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4 px-2">Principais</p>
          <div className="space-y-1">
            <NavItem label="Dashboard" icon="D" active />
            <NavItem label="Chat ao Vivo" icon="C" />
            <NavItem label="Contatos" icon="U" />
          </div>
        </div>

        <div>
          <p className="text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4 px-2">Automação</p>
          <div className="space-y-1">
            <NavItem label="Fluxos Chatbot" icon="Z" />
            <NavItem label="Respostas Rápidas" icon="R" />
            <NavItem label="Etiquetas (Tags)" icon="T" />
          </div>
        </div>

        <div>
          <p className="text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4 px-2">Gestão</p>
          <div className="space-y-1">
            <NavItem label="Equipe" icon="E" />
            <NavItem label="Setores" icon="S" />
            <NavItem label="Relatórios" icon="G" />
          </div>
        </div>
      </nav>

      <div className="mt-auto pt-6 border-t border-white/5 space-y-1">
        <NavItem label="Ajustes" icon="A" />
        <NavItem label="Integrações" icon="I" />
      </div>
    </aside>
  );
};

const NavItem = ({ label, icon, active }) => (
  <button className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group ${active ? 'bg-indigo-600/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white'}`}>
    <span className={`w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all ${active ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-500 group-hover:bg-white/10 group-hover:text-indigo-400'}`}>
      {icon}
    </span>
    <span className="text-sm font-semibold">{label}</span>
    {active && <div className="ml-auto w-1.5 h-1.5 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.8)]"></div>}
  </button>
);

export default Sidebar;
