import React from 'react';
import ActivityFeed from '@/components/dashboard/ActivityFeed';

const Dashboard = () => {
  return (
    <div className="p-8 space-y-8 animate-in fade-in duration-700">
      <header className="flex justify-between items-end">
        <div>
          <h2 className="text-3xl font-bold text-white">Olá, Admin</h2>
          <p className="text-gray-400">Bem-vindo de volta ao seu painel de atendimento.</p>
        </div>
        <div className="glass px-4 py-2 rounded-lg flex items-center gap-2">
          <span className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
          <span className="text-sm font-medium">Sistema Online</span>
        </div>
      </header>

      {/* Métricas Principais */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <MetricCard label="Conversas Ativas" value="24" trend="+12%" />
        <MetricCard label="Tempo Médio" value="1m 30s" trend="-5%" />
        <MetricCard label="Mensagens Hoje" value="1.2k" trend="+18%" />
        <MetricCard label="Satisfação" value="4.8/5" trend="+2%" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Atendimentos Recentes */}
        <div className="lg:col-span-2 glass-card p-6">
          <h3 className="text-xl font-semibold mb-6">Atendimentos Recentes</h3>
          <div className="space-y-4">
            {[1, 2, 3].map((i) => (
              <div key={i} className="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                <div className="flex items-center gap-4">
                  <div className="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 uppercase font-bold">C{i}</div>
                  <div>
                    <h4 className="font-medium text-white">Cliente #{i}239</h4>
                    <p className="text-xs text-gray-500">Iniciado há 5 minutos</p>
                  </div>
                </div>
                <button className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">Visualizar</button>
              </div>
            ))}
          </div>
        </div>

        {/* Audit / Activity Feed */}
        <div className="h-full">
           <ActivityFeed />
        </div>
      </div>
    </div>
  );
};

const MetricCard = ({ label, value, trend }) => (
  <div className="glass-card p-6">
    <p className="text-sm text-gray-400 font-medium mb-1">{label}</p>
    <div className="flex items-end justify-between">
      <h4 className="text-2xl font-bold text-white">{value}</h4>
      <span className={`text-xs font-bold ${trend.startsWith('+') ? 'text-green-400' : 'text-red-400'}`}>
        {trend}
      </span>
    </div>
  </div>
);

const ChannelStatus = ({ icon, label, status, warning }) => (
  <div className="flex items-center justify-between">
    <div className="flex items-center gap-3">
      <div className="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-sm">{icon[0]}</div>
      <span className="text-sm font-medium text-gray-300">{label}</span>
    </div>
    <span className={`text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded ${warning ? 'bg-red-500/10 text-red-500' : 'bg-green-500/10 text-green-500'}`}>
      {status}
    </span>
  </div>
);

export default Dashboard;
