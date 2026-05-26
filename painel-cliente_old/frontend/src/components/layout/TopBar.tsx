import React, { useState, useEffect } from 'react';

const TopBar = () => {
  const [notifications, setNotifications] = useState([
    { id: 1, text: "Nova mensagem de João Silva", time: "Agora", type: "msg" },
    { id: 2, text: "Transferência recebida de Alice", time: "5 min", type: "transfer" }
  ]);
  const [showNotif, setShowNotif] = useState(false);

  return (
    <header className="h-20 glass border-b border-white/10 px-8 flex items-center justify-between sticky top-0 z-50">
      {/* Busca Global */}
      <div className="flex-1 max-w-xl">
        <div className="relative group">
          <span className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-indigo-500 transition-colors">🔍</span>
          <input 
            type="text" 
            placeholder="Pesquisar contatos, mensagens ou protocolos..." 
            className="w-full bg-white/5 border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-white focus:outline-none focus:border-indigo-500/50 transition-all"
          />
        </div>
      </div>

      {/* Ações Direitas */}
      <div className="flex items-center gap-6">
        {/* Notificações */}
        <div className="relative">
          <button 
            onClick={() => setShowNotif(!showNotif)}
            className="w-12 h-12 glass border border-white/10 rounded-xl flex items-center justify-center relative hover:bg-white/5 transition-all"
          >
            <span className="text-xl">🔔</span>
            <span className="absolute top-2 right-2 w-3 h-3 bg-red-500 border-2 border-[#0a0a0c] rounded-full"></span>
          </button>

          {showNotif && (
            <div className="absolute right-0 mt-4 w-80 glass-card p-4 shadow-2xl animate-in fade-in zoom-in duration-200">
              <div className="flex justify-between items-center mb-4">
                <h4 className="text-sm font-bold text-white uppercase tracking-widest">Notificações</h4>
                <button className="text-[10px] text-gray-500 hover:text-white uppercase font-bold">Limpar</button>
              </div>
              <div className="space-y-3">
                {notifications.map(n => (
                  <div key={n.id} className="p-3 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 cursor-pointer transition-all">
                    <p className="text-xs text-gray-200 leading-snug">{n.text}</p>
                    <span className="text-[10px] text-gray-600 font-bold uppercase mt-1 inline-block">{n.time}</span>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Perfil do Usuário */}
        <div className="flex items-center gap-4 pl-6 border-l border-white/10">
          <div className="text-right hidden sm:block">
            <p className="text-sm font-bold text-white">Admin Tech</p>
            <p className="text-[10px] text-indigo-500 uppercase font-black tracking-widest">Plano Premium</p>
          </div>
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 p-0.5 shadow-lg">
             <div className="w-full h-full rounded-[14px] bg-[#0a0a0c] flex items-center justify-center font-bold text-white">AT</div>
          </div>
        </div>
      </div>
    </header>
  );
};

export default TopBar;
