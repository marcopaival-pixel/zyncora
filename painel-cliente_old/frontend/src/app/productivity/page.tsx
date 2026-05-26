import React from 'react';

const QuickRepliesPage = () => {
  return (
    <div className="p-8 space-y-8">
      <header className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-white">Produtividade</h2>
          <p className="text-gray-400">Atalhos e classificações para agilizar o atendimento.</p>
        </div>
        <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold transition-all shadow-xl shadow-indigo-600/20">
          + Criar Atalho
        </button>
      </header>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Respostas Rápidas */}
        <section className="glass-card flex flex-col">
          <div className="p-6 border-b border-white/10 flex justify-between items-center">
             <h3 className="text-lg font-bold text-white">Respostas Rápidas</h3>
             <span className="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Ative com "/" no chat</span>
          </div>
          <div className="flex-1 p-6 space-y-4">
             <ReplyItem trigger="/boasvindas" content="Olá! Seja bem-vindo à nossa empresa. Como posso te ajudar hoje?" />
             <ReplyItem trigger="/pix" content="Chave CNPJ: 12.345.678/0001-90. Nome: Empresa Exemplo Ltda." />
             <ReplyItem trigger="/endereco" content="Estamos na Rua das Flores, 123 - Centro, São Paulo/SP." />
          </div>
        </section>

        {/* Gestão de Etiquetas (Tags) */}
        <section className="glass-card">
          <div className="p-6 border-b border-white/10 flex justify-between items-center">
             <h3 className="text-lg font-bold text-white">Etiquetas (Tags)</h3>
             <button className="text-xs font-bold text-indigo-400 uppercase tracking-widest hover:text-indigo-300">Nova Tag</button>
          </div>
          <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <TagItem label="Suporte" color="#ef4444" count={42} />
            <TagItem label="Comercial" color="#10b981" count={128} />
            <TagItem label="Financeiro" color="#f59e0b" count={15} />
            <TagItem label="Feedback" color="#6366f1" count={8} />
          </div>
        </section>
      </div>
    </div>
  );
};

const ReplyItem = ({ trigger, content }) => (
  <div className="p-4 bg-white/5 border border-white/5 rounded-2xl group hover:border-indigo-500/30 transition-all">
    <div className="flex justify-between items-center mb-2">
      <span className="text-xs font-bold text-indigo-400">{trigger}</span>
      <button className="text-[10px] text-gray-600 font-bold uppercase hover:text-white">Editar</button>
    </div>
    <p className="text-sm text-gray-300 line-clamp-2">{content}</p>
  </div>
);

const TagItem = ({ label, color, count }) => (
  <div className="p-4 bg-white/5 border border-white/5 rounded-xl flex items-center justify-between group hover:border-white/10 transition-all">
    <div className="flex items-center gap-3">
      <div style={{ backgroundColor: color }} className="w-3 h-3 rounded-full shadow-[0_0_8px_rgba(0,0,0,0.5)]"></div>
      <span className="text-sm font-bold text-white">{label}</span>
    </div>
    <span className="text-[10px] font-bold text-gray-500 uppercase">{count} chats</span>
  </div>
);

export default QuickRepliesPage;
