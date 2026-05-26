import React, { useState } from 'react';

const AgentChat = () => {
  const [activeTab, setActiveTab] = useState('mine'); // 'mine' ou 'sector'

  return (
    <div className="flex h-screen bg-[#0a0a0c]">
      {/* Lista de Chats (Filtro por Atendente/Setor) */}
      <aside className="w-80 border-r border-white/10 flex flex-col glass">
        <div className="p-6 border-b border-white/10 space-y-4">
          <div className="flex bg-white/5 rounded-xl p-1">
            <button 
              onClick={() => setActiveTab('mine')}
              className={`flex-1 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all ${activeTab === 'mine' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:text-gray-300'}`}
            >
              Meus Atendimentos
            </button>
            <button 
              onClick={() => setActiveTab('sector')}
              className={`flex-1 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all ${activeTab === 'sector' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:text-gray-300'}`}
            >
              Fila do Setor
            </button>
          </div>
          <input type="text" placeholder="Filtrar por nome..." className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" />
        </div>

        <div className="flex-1 overflow-y-auto divide-y divide-white/5">
          <ChatCard name="Ricardo Lima" message="Preciso de ajuda com o boleto" time="2m" status="urgente" />
          <ChatCard name="Fernanda Rocha" message="O bot não entendeu minha pergunta" time="5m" />
          <ChatCard name="Marcos Paulo" message="Obrigado pelo suporte!" time="1h" closed />
        </div>
      </aside>

      {/* Janela de Mensageira Principal */}
      <main className="flex-1 flex flex-col relative">
        {/* Top Bar da Conversa */}
        <header className="p-6 glass border-b border-white/10 flex justify-between items-center z-10">
           <div className="flex items-center gap-4">
              <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center font-bold text-white shadow-lg">RL</div>
              <div>
                 <h2 className="text-white font-bold">Ricardo Lima</h2>
                 <p className="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Protocolo: #2024-XPTO</p>
              </div>
           </div>
           <div className="flex gap-3">
              <button className="px-4 py-2 glass hover:bg-white/5 border border-white/10 text-white text-xs font-bold rounded-xl transition-all">Transferir</button>
              <button className="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-green-600/20 transition-all">Finalizar Chat</button>
           </div>
        </header>

        {/* Histórico de Mensagens */}
        <div className="flex-1 overflow-y-auto p-8 space-y-6 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-fixed opacity-90">
           <div className="text-center">
              <span className="text-[10px] bg-white/5 text-gray-500 px-3 py-1 rounded-full uppercase font-bold tracking-widest">Início da Conversa - 14:00</span>
           </div>
           <Msg bubble="Oi, meu boleto veio com valor errado." sender="visitor" />
           <Msg bubble="Olá Ricardo, sou o atendente humano. Vou verificar agora mesmo." sender="agent" />
        </div>

        {/* Input e Respostas Rápidas */}
        <footer className="p-6 glass border-t border-white/10">
           <div className="flex gap-4 mb-4">
              <span className="text-[10px] font-black text-indigo-500 uppercase tracking-widest cursor-pointer hover:underline">/boasvindas</span>
              <span className="text-[10px] font-black text-indigo-500 uppercase tracking-widest cursor-pointer hover:underline">/pix</span>
              <span className="text-[10px] font-black text-indigo-500 uppercase tracking-widest cursor-pointer hover:underline">/finalizar</span>
           </div>
           <div className="flex gap-4">
              <textarea 
                className="flex-1 bg-white/5 border border-white/10 rounded-2xl p-4 text-sm text-white focus:outline-none focus:border-indigo-500 transition-all resize-none"
                placeholder="Digite sua resposta..."
                rows={2}
              ></textarea>
              <button className="w-14 h-14 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl flex items-center justify-center text-xl shadow-xl shadow-indigo-600/30 transition-all">
                🚀
              </button>
           </div>
        </footer>
      </main>
    </div>
  );
};

const ChatCard = ({ name, message, time, status, closed }) => (
  <div className={`p-5 cursor-pointer border-l-4 transition-all ${closed ? 'opacity-50 grayscale' : 'hover:bg-white/5'} ${status === 'urgente' ? 'border-red-500' : 'border-transparent'}`}>
     <div className="flex justify-between items-start mb-1">
        <h4 className="text-sm font-bold text-white">{name}</h4>
        <span className="text-[10px] text-gray-500">{time}</span>
     </div>
     <p className="text-xs text-gray-400 line-clamp-1">{message}</p>
     {status && <span className="inline-block mt-2 text-[8px] bg-red-500/10 text-red-500 px-2 py-0.5 rounded font-black uppercase tracking-widest">{status}</span>}
  </div>
);

const Msg = ({ bubble, sender }) => (
  <div className={`flex ${sender === 'agent' ? 'justify-end' : 'justify-start'}`}>
     <div className={`max-w-[80%] p-4 rounded-3xl text-sm ${sender === 'agent' ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white/10 text-gray-200 rounded-tl-none border border-white/10'}`}>
        {bubble}
     </div>
  </div>
);

export default AgentChat;
