import React from 'react';

const ReportsPage = () => {
  return (
    <div className="p-8 space-y-8">
      <header className="flex justify-between items-end">
        <div>
          <h2 className="text-3xl font-bold text-white">Relatórios & Insights</h2>
          <p className="text-gray-400">Análise detalhada de performance e engajamento.</p>
        </div>
        <div className="flex gap-3">
          <select className="glass border border-white/10 rounded-xl px-4 py-2 text-sm text-gray-300 focus:outline-none">
            <option>Últimos 7 dias</option>
            <option>Últimos 30 dias</option>
            <option>Este mês</option>
          </select>
          <button className="glass border border-white/10 rounded-xl px-4 py-2 text-sm font-bold text-indigo-400 hover:text-white transition-all">Exportar PDF</button>
        </div>
      </header>

      {/* Gráficos Principais */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Atendimentos por Dia */}
        <div className="glass-card p-8">
          <h3 className="text-lg font-bold text-white mb-8">Volume de Conversas</h3>
          <div className="h-64 flex items-end justify-between gap-4">
            <Bar height="40%" label="Seg" />
            <Bar height="65%" label="Ter" />
            <Bar height="45%" label="Qua" />
            <Bar height="90%" label="Qui" active />
            <Bar height="70%" label="Sex" />
            <Bar height="20%" label="Sab" />
            <Bar height="15%" label="Dom" />
          </div>
        </div>

        {/* Distribuição por Canal */}
        <div className="glass-card p-8">
          <h3 className="text-lg font-bold text-white mb-8">Performance por Canal</h3>
          <div className="space-y-6">
            <ChannelBar label="WhatsApp" value={82} color="bg-green-500" />
            <ChannelBar label="Web Chat" value={45} color="bg-blue-500" />
            <ChannelBar label="API / Outros" value={12} color="bg-purple-500" />
          </div>
          <div className="mt-8 pt-8 border-t border-white/5 grid grid-cols-3 gap-4 text-center">
            <StatSmall label="Tempo Médio" value="2.4m" />
            <StatSmall label="Resolução" value="94%" />
            <StatSmall label="Satisfação" value="4.9" />
          </div>
        </div>
      </div>

      {/* Tabela de Ranking de Atendentes */}
      <div className="glass-card overflow-hidden">
        <div className="p-6 border-b border-white/10">
          <h3 className="text-lg font-bold text-white">Top Atendentes da Semana</h3>
        </div>
        <table className="w-full text-left">
          <tbody className="divide-y divide-white/5">
            <AgentRow name="Alice Oliveira" chats={142} rating={4.9} />
            <AgentRow name="Carla Souza" chats={128} rating={4.8} />
            <AgentRow name="Bruno Mendes" chats={94} rating={4.7} />
          </tbody>
        </table>
      </div>
    </div>
  );
};

const Bar = ({ height, label, active }) => (
  <div className="flex-1 flex flex-col items-center gap-4 group">
    <div className="w-full relative bg-white/5 rounded-t-xl overflow-hidden h-full flex items-end">
      <div 
        style={{ height }} 
        className={`w-full transition-all duration-1000 ease-out ${active ? 'bg-gradient-to-t from-indigo-600 to-indigo-400 shadow-[0_0_20px_rgba(99,102,241,0.3)]' : 'bg-white/10 group-hover:bg-white/20'}`}
      ></div>
    </div>
    <span className={`text-[10px] font-bold uppercase tracking-widest ${active ? 'text-indigo-400' : 'text-gray-500'}`}>{label}</span>
  </div>
);

const ChannelBar = ({ label, value, color }) => (
  <div className="space-y-2">
    <div className="flex justify-between text-xs font-bold uppercase tracking-widest">
      <span className="text-gray-400">{label}</span>
      <span className="text-white">{value}%</span>
    </div>
    <div className="h-2 w-full bg-white/5 rounded-full overflow-hidden">
      <div style={{ width: `${value}%` }} className={`h-full ${color} rounded-full`}></div>
    </div>
  </div>
);

const StatSmall = ({ label, value }) => (
  <div>
    <p className="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">{label}</p>
    <p className="text-lg font-bold text-white">{value}</p>
  </div>
);

const AgentRow = ({ name, chats, rating }) => (
  <tr className="hover:bg-white/5 transition-colors">
    <td className="px-6 py-4 font-bold text-white text-sm">{name}</td>
    <td className="px-6 py-4 text-xs font-bold text-gray-500 uppercase">{chats} Conversas</td>
    <td className="px-6 py-4">
      <div className="flex items-center gap-1">
        <span className="text-yellow-500">★</span>
        <span className="text-sm font-bold text-white">{rating}</span>
      </div>
    </td>
  </tr>
);

export default ReportsPage;
