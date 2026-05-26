import React from 'react';

const LoginPage = () => {
  return (
    <div className="min-h-screen flex items-center justify-center p-6 bg-[#0a0a0c]">
      {/* Background Decorativo */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/10 blur-[120px] rounded-full"></div>
        <div className="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-violet-600/10 blur-[120px] rounded-full"></div>
      </div>

      <div className="w-full max-w-md space-y-8 relative z-10">
        <div className="text-center space-y-2">
          <div className="w-16 h-16 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-3xl mx-auto flex items-center justify-center shadow-2xl shadow-indigo-600/30 mb-6">
            <span className="text-2xl font-black text-white">CB</span>
          </div>
          <h1 className="text-3xl font-extrabold text-white tracking-tight">Bem-vindo de volta!</h1>
          <p className="text-gray-500 text-sm font-medium">Acesse seu painel multiempresa ChatBox.</p>
        </div>

        <form className="glass-card p-10 space-y-6">
          <div className="space-y-4">
            <InputField label="Endereço de E-mail" type="email" placeholder="nome@empresa.com" />
            <InputField label="Sua Senha" type="password" placeholder="••••••••" />
          </div>

          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
               <input type="checkbox" className="w-4 h-4 rounded bg-white/5 border-white/10 accent-indigo-500" />
               <label className="text-xs text-gray-400 font-medium">Lembrar acesso</label>
            </div>
            <a href="#" className="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition-colors">Esqueceu a senha?</a>
          </div>

          <button className="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-600/20 transition-all active:scale-[0.98]">
            Entrar no Painel
          </button>
        </form>

        <p className="text-center text-xs text-gray-600 font-medium">
          Ainda não tem uma conta? <a href="#" className="text-white font-bold hover:underline">Fale com o comercial</a>
        </p>
      </div>
    </div>
  );
};

const InputField = ({ label, type, placeholder }) => (
  <div className="space-y-2">
    <label className="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">{label}</label>
    <input 
      type={type} 
      placeholder={placeholder}
      className="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-indigo-500 transition-all text-white placeholder:text-gray-600"
    />
  </div>
);

export default LoginPage;
