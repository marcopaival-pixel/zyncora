import React from 'react';

const ActivityFeed = () => {
  const activities = [
    { user: 'Alice', action: 'Transferiu atendimento', target: '#2044', time: '2m ago' },
    { user: 'Sistema', action: 'Bot atualizado', target: 'Boas Vindas', time: '15m ago' },
    { user: 'Admin', action: 'Novo setor criado', target: 'Comercial', time: '1h ago' },
    { user: 'Bruno', action: 'Encerrou chat', target: '#1992', time: '2h ago' }
  ];

  return (
    <div className="glass-card p-6 h-full flex flex-col">
      <div className="flex justify-between items-center mb-6">
        <h3 className="text-sm font-bold text-white uppercase tracking-widest">Atividade Recente</h3>
        <span className="w-2 h-2 bg-indigo-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(99,102,241,1)]"></span>
      </div>

      <div className="flex-1 space-y-6 relative before:absolute before:left-2 before:top-2 before:bottom-2 before:w-[1px] before:bg-white/10">
        {activities.map((item, idx) => (
          <div key={idx} className="relative pl-8 group">
            <div className="absolute left-0 top-1.5 w-4 h-4 rounded-full bg-[#0a0a0c] border-2 border-indigo-500 group-hover:scale-125 transition-transform"></div>
            <div>
              <p className="text-xs text-gray-300 font-medium">
                <span className="text-white font-bold">{item.user}</span> {item.action} 
                <span className="text-indigo-400 font-bold ml-1">{item.target}</span>
              </p>
              <p className="text-[10px] text-gray-600 font-bold uppercase mt-1">{item.time}</p>
            </div>
          </div>
        ))}
      </div>

      <button className="mt-6 w-full py-2 bg-white/5 border border-white/5 rounded-xl text-[10px] font-bold text-gray-400 hover:text-white uppercase transition-all">
        Ver Log Completo
      </button>
    </div>
  );
};

export default ActivityFeed;
