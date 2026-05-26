import React from 'react';

const TeamPage = () => {
  return (
    <div className="p-8 space-y-8">
      <header className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-white">Equipe de Atendimento</h2>
          <p className="text-gray-400">Gerencie seus atendentes e permissões de acesso.</p>
        </div>
        <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold shadow-xl shadow-indigo-600/20 transition-all flex items-center gap-2">
          <span>+</span> Novo Atendente
        </button>
      </header>

      {/* Tabela de Equipe */}
      <div className="glass-card overflow-hidden">
        <table className="w-full text-left">
          <thead>
            <tr className="border-b border-white/10 bg-white/5">
              <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Nome</th>
              <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Cargo / Role</th>
              <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
              <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Entrada</th>
              <th className="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Ações</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-white/5">
            <TeamRow name="Alice Oliveira" email="alice@empresa.com" role="Administrador" status="Ativo" date="10 Out 2023" />
            <TeamRow name="Bruno Mendes" email="bruno@empresa.com" role="Agente" status="Em Pausa" date="15 Out 2023" />
            <TeamRow name="Carla Souza" email="carla@empresa.com" role="Supervisor" status="Ativo" date="02 Nov 2023" />
          </tbody>
        </table>
      </div>
    </div>
  );
};

const TeamRow = ({ name, email, role, status, date }) => (
  <tr className="hover:bg-white/5 transition-colors group">
    <td className="px-6 py-5">
      <div className="flex items-center gap-3">
        <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-violet-500/20 flex items-center justify-center font-bold text-indigo-400 border border-indigo-500/10">
          {name[0]}
        </div>
        <div>
          <p className="font-bold text-white text-sm">{name}</p>
          <p className="text-[11px] text-gray-500">{email}</p>
        </div>
      </div>
    </td>
    <td className="px-6 py-5">
      <span className="text-xs font-semibold text-gray-300 px-3 py-1 rounded-full bg-white/5 border border-white/10">
        {role}
      </span>
    </td>
    <td className="px-6 py-5">
      <div className="flex items-center gap-2">
        <span className={`w-2 h-2 rounded-full ${status === 'Ativo' ? 'bg-green-500 animate-pulse' : 'bg-orange-500'}`}></span>
        <span className="text-xs font-medium text-gray-300">{status}</span>
      </div>
    </td>
    <td className="px-6 py-5 text-sm text-gray-400 font-medium">{date}</td>
    <td className="px-6 py-5">
      <button className="text-gray-500 hover:text-white transition-colors text-sm font-bold">Editar</button>
    </td>
  </tr>
);

export default TeamPage;
