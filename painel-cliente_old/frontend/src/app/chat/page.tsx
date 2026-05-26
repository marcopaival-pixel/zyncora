import React, { useState } from 'react';

const ChatPage = () => {
  const [selectedChat, setSelectedChat] = useState(null);

  return (
    <div className="flex h-[calc(100vh-2rem)] m-4 rounded-3xl overflow-hidden glass shadow-2xl">
      {/* Lista de Conversas */}
      <aside className="w-80 border-r border-white/10 flex flex-col">
        <div className="p-6 border-b border-white/10">
          <input 
            type="text" 
            placeholder="Buscar conversa..." 
            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 transition-all"
          />
        </div>
        <div className="flex-1 overflow-y-auto">
          {[1, 2, 3, 4, 5].map((i) => (
            <ChatListItem 
              key={i} 
              active={selectedChat === i} 
              onClick={() => setSelectedChat(i)} 
            />
          ))}
        </div>
      </aside>

      {/* Janela de Chat Principal */}
      <main className="flex-1 flex flex-col bg-black/40">
        {selectedChat ? (
          <>
            {/* Header do Chat */}
            <header className="p-6 border-b border-white/10 flex items-center justify-between">
              <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center font-bold text-indigo-400">J</div>
                <div>
                  <h3 className="font-bold text-white">João Silva</h3>
                  <p className="text-xs text-green-400 font-medium tracking-wide">● Aguardando Resposta</p>
                </div>
              </div>
              <div className="flex gap-2">
                <ActionButton label="Transferir" />
                <ActionButton label="Encerrar" primary />
              </div>
            </header>

            {/* Balões de Mensagem */}
            <div className="flex-1 overflow-y-auto p-6 space-y-4">
              <MessageBubble text="Olá, gostaria de saber mais sobre os planos." sender="visitor" />
              <MessageBubble text="Boa tarde! Com certeza, temos 3 modalidades..." sender="agent" />
              <MessageBubble text="O plano premium inclui o chatbot ilimitado?" sender="visitor" />
            </div>

            {/* Input de Mensagem */}
            <footer className="p-6 bg-black/50 border-t border-white/10">
              <div className="relative">
                <textarea 
                  placeholder="Escreva sua mensagem..."
                  className="w-full bg-white/5 border border-white/20 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-indigo-500 transition-all resize-none pr-32"
                  rows={2}
                />
                <div className="absolute right-4 bottom-4 flex gap-2">
                  <button className="p-2 text-gray-400 hover:text-white transition-colors">📄</button>
                  <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all">
                    Enviar
                  </button>
                </div>
              </div>
            </footer>
          </>
        ) : (
          <div className="flex-1 flex flex-col items-center justify-center text-center p-12">
            <div className="w-20 h-20 bg-white/5 rounded-3xl flex items-center justify-center text-3xl mb-4">💬</div>
            <h3 className="text-xl font-bold text-white mb-2">Selecione uma conversa</h3>
            <p className="text-gray-500 max-w-xs uppercase text-[10px] font-bold tracking-[0.2em]">Escolha um cliente na lateral para iniciar o atendimento</p>
          </div>
        )}
      </main>

      {/* Painel Lateral Direito (Info do Cliente) */}
      <aside className="w-80 border-l border-white/10 p-6 space-y-8 bg-black/20">
        <section>
          <h4 className="text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">Informações do Contato</h4>
          <div className="space-y-4 text-sm">
            <InfoRow label="Telefone" value="+55 11 99999-9999" />
            <InfoRow label="Protocolo" value="#2024-88A2" />
            <InfoRow label="Setor" value="Vendas" />
          </div>
        </section>

        <section>
          <h4 className="text-[11px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">Etiquetas</h4>
          <div className="flex flex-wrap gap-2">
            <TagBadge label="Urgente" color="bg-red-500/20 text-red-400" />
            <TagBadge label="Lead Quente" color="bg-orange-500/20 text-orange-400" />
            <TagBadge label="Premium" color="bg-indigo-500/20 text-indigo-400" />
          </div>
        </section>
      </aside>
    </div>
  );
};

const ChatListItem = ({ active, onClick }) => (
  <div 
    onClick={onClick}
    className={`p-4 flex gap-4 cursor-pointer transition-all border-l-4 ${active ? 'bg-indigo-600/10 border-indigo-500' : 'hover:bg-white/5 border-transparent'}`}
  >
    <div className="w-12 h-12 rounded-full bg-white/5 flex-shrink-0 flex items-center justify-center font-bold">JS</div>
    <div className="flex-1 min-w-0">
      <div className="flex justify-between items-start mb-1">
        <h4 className="font-bold text-sm text-white truncate">João Silva</h4>
        <span className="text-[10px] text-gray-500">14:02</span>
      </div>
      <p className="text-xs text-gray-500 truncate">Olá, gostaria de saber mais sobre...</p>
    </div>
  </div>
);

const MessageBubble = ({ text, sender }) => (
  <div className={`flex ${sender === 'agent' ? 'justify-end' : 'justify-start'}`}>
    <div className={`max-w-[70%] p-4 rounded-2xl text-sm ${sender === 'agent' ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white/5 text-gray-200 rounded-tl-none border border-white/10'}`}>
      {text}
    </div>
  </div>
);

const ActionButton = ({ label, primary }) => (
  <button className={`px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-lg ${primary ? 'bg-white/10 text-white hover:bg-white/20' : 'text-gray-400 hover:text-white'}`}>
    {label}
  </button>
);

const InfoRow = ({ label, value }) => (
  <div className="flex flex-col">
    <span className="text-[10px] text-gray-500 font-bold mb-1">{label}</span>
    <span className="text-white font-medium">{value}</span>
  </div>
);

const TagBadge = ({ label, color }) => (
  <span className={`px-2 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider ${color}`}>
    {label}
  </span>
);

export default ChatPage;
