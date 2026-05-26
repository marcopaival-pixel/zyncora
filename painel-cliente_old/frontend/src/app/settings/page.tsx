import React from 'react';

const SettingsPage = () => {
  return (
    <div className="p-8 space-y-8">
      <header>
        <h2 className="text-3xl font-bold text-white">Configurações</h2>
        <p className="text-gray-400">Personalize sua plataforma e gerencie integrações.</p>
      </header>

      <div className="flex gap-8">
        {/* Navegação de Tabs */}
        <aside className="w-64 space-y-2">
          <TabItem label="Perfil da Empresa" active />
          <TabItem label="Canais & API" />
          <TabItem label="Horários de Atendimento" />
          <TabItem label="Aparência do Widget" />
          <TabItem label="Faturamento" />
        </aside>

        {/* Conteúdo da Tab */}
        <main className="flex-1 space-y-8">
          {/* Sessão: Perfil */}
          <section className="glass-card p-8 space-y-6">
            <h3 className="text-xl font-bold text-white border-b border-white/5 pb-4">Perfil da Empresa</h3>
            
            <div className="grid grid-cols-2 gap-8">
              <div className="space-y-4">
                <Field label="Nome da Organização" value="TechSolution SaaS" />
                <Field label="E-mail Administrativo" value="admin@techsolution.local" />
              </div>
              <div className="flex flex-col items-center justify-center p-6 border-2 border-dashed border-white/10 rounded-2xl group hover:border-indigo-500/50 transition-all cursor-pointer">
                <div className="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center text-2xl mb-2">📸</div>
                <p className="text-xs font-bold text-gray-500 uppercase">Alterar Logotipo</p>
              </div>
            </div>
            
            <div className="pt-4">
              <button className="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold transition-all">Salvar Alterações</button>
            </div>
          </section>

          {/* Sessão: WhatsApp Integration */}
          <section className="glass-card p-8 space-y-6">
            <div className="flex justify-between items-center border-b border-white/5 pb-4">
              <h3 className="text-xl font-bold text-white">Integração WhatsApp</h3>
              <span className="bg-green-500/10 text-green-500 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">Conectado</span>
            </div>

            <div className="bg-black/20 p-6 rounded-2xl space-y-4">
              <div className="flex justify-between items-center">
                <div>
                  <p className="text-sm font-bold text-white">WhatsApp Cloud API (Meta)</p>
                  <p className="text-xs text-gray-500">ID do Telefone: 109283746551209</p>
                </div>
                <button className="text-red-400 hover:text-red-300 text-xs font-bold uppercase transition-colors">Desconectar</button>
              </div>
              <div className="grid grid-cols-1 gap-4">
                <div className="p-4 bg-white/5 rounded-xl border border-white/5">
                  <p className="text-[10px] font-bold text-gray-500 uppercase mb-2">Webhook URL (Callback)</p>
                  <code className="text-indigo-400 text-xs break-all">https://api.seusistema.com/v1/webhook/whatsapp/techsolution</code>
                </div>
              </div>
            </div>
          </section>
        </main>
      </div>
    </div>
  );
};

const TabItem = ({ label, active }) => (
  <button className={`w-full text-left px-6 py-3 rounded-xl text-sm font-semibold transition-all ${active ? 'bg-indigo-600/10 text-white border border-indigo-500/20' : 'text-gray-500 hover:text-gray-300 hover:bg-white/5'}`}>
    {label}
  </button>
);

const Field = ({ label, value }) => (
  <div className="flex flex-col space-y-2">
    <label className="text-[11px] font-bold text-gray-500 uppercase tracking-widest">{label}</label>
    <input 
      type="text" 
      defaultValue={value} 
      className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 transition-all text-white"
    />
  </div>
);

export default SettingsPage;
