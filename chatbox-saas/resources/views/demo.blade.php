<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demonstração Interativa | Zynkora - Sua Central de Atendimento Inteligente</title>
    <meta name="description" content="Experimente o poder da inteligência artificial aplicada ao atendimento ao cliente. Teste nossa demo e veja como o Zynkora pode automatizar seu negócio.">
    
    <!-- SEO & Social Media -->
    <meta property="og:title" content="Demonstração Interativa | Zynkora">
    <meta property="og:description" content="Converse com nossa IA e descubra como escalar seu atendimento 24/7 com o Zynkora.">
    <meta property="og:type" content="website">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        secondary: {
                            500: '#0ea5e9',
                        },
                        dark: {
                            900: '#0b0f1a',
                            800: '#0f172a',
                            700: '#1e293b',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'shimmer': 'shimmer 3s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        shimmer: {
                            '0%': { left: '-60%' },
                            '20%': { left: '120%' },
                            '100%': { left: '120%' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        body {
            background: #0b0f1a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,30%,10%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(215,40%,15%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(280,30%,15%,1) 0, transparent 50%);
            background-attachment: fixed;
            color: rgba(255, 255, 255, 0.9);
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .gradient-text {
            background: linear-gradient(135deg, #818cf8, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #0ea5e9);
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .chat-container {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="font-sans selection:bg-primary-500 selection:text-white" x-data="demoApp()">

    <!-- Header -->
    <header class="fixed top-0 w-full z-50 transition-all duration-300" 
            :class="scrolled ? 'glass py-3 border-b border-white/10' : 'bg-transparent py-5'"
            @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div class="container mx-auto px-6 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 group">
                <img src="{{ asset('images/logo.png') }}" alt="Zynkora" style="height: 60px;">
            </a>
            
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
                <a href="#demo" class="text-white/70 hover:text-white transition-colors">Testar Demo</a>
                <a href="#features" class="text-white/70 hover:text-white transition-colors">Funcionalidades</a>
                <a href="#faq" class="text-white/70 hover:text-white transition-colors">FAQ</a>
            </nav>
            
            <div class="flex items-center gap-4">
                <a href="/admin/register" class="hidden sm:block px-6 py-2.5 rounded-full border border-white/20 bg-white/5 text-white text-sm font-semibold hover:bg-white/10 transition-all">
                    Login
                </a>
                <button @click="openLeadModal = true" class="btn-primary px-6 py-2.5 rounded-full text-white text-sm font-bold">
                    Teste Grátis
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 overflow-hidden">
        <div class="container mx-auto px-6 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-500 text-xs font-bold mb-6 tracking-wide uppercase">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                </span>
                Demo Interativa Zynkora
            </div>
            
            <h1 class="text-5xl md:text-7xl font-bold font-display leading-[1.1] mb-6 max-w-4xl mx-auto text-white">
                O chat que conecta você ao <span class="gradient-text">mundo.</span>
            </h1>
            
            <p class="text-lg md:text-xl text-white/60 max-w-2xl mx-auto mb-10 leading-relaxed">
                Teste nossa inteligência artificial e descubra como o Zynkora pode automatizar seu atendimento 24/7 com máxima eficiência.
            </p>
            
            <div class="flex flex-wrap justify-center gap-4 mb-16">
                <a href="#demo" class="btn-primary px-8 py-4 rounded-full text-white font-bold flex items-center gap-2">
                    Iniciar Demo <i data-lucide="zap" class="w-5 h-5"></i>
                </a>
                <button @click="openLeadModal = true" class="px-8 py-4 rounded-full bg-white/5 text-white font-bold border border-white/10 hover:bg-white/10 transition-all flex items-center gap-2">
                    Solicitar Demo <i data-lucide="presentation" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Chat Demo Section -->
    <section id="demo" class="py-20 relative">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                
                <!-- Left Content -->
                <div class="lg:col-span-5">
                    <span class="text-primary-500 font-bold uppercase tracking-widest text-xs mb-4 block">Inteligência Artificial</span>
                    <h2 class="text-4xl font-bold font-display mb-6 text-white leading-tight">Como o Zynkora <br>transforma seu negócio?</h2>
                    <p class="text-white/60 mb-10 leading-relaxed text-lg">
                        Nossa IA proprietária aprende sobre seu negócio e responde como um especialista, liberando sua equipe para o que realmente importa.
                    </p>
                    
                    <div class="space-y-8">
                        <template x-for="item in benefits" :key="item.title">
                            <div class="flex gap-5 group">
                                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-white/5 border border-white/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-white transition-all duration-300">
                                    <i :data-lucide="item.icon" class="w-7 h-7"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white mb-1 text-lg" x-text="item.title"></h4>
                                    <p class="text-sm text-white/40 leading-relaxed" x-text="item.desc"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Chat Interface -->
                <div class="lg:col-span-7">
                    <div class="chat-container rounded-[40px] shadow-2xl border border-white/10 overflow-hidden flex flex-col h-[650px]">
                        <!-- Chat Header -->
                        <div class="p-6 bg-white/5 border-b border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <div class="w-12 h-12 bg-gradient-to-tr from-primary-600 to-secondary-500 rounded-2xl flex items-center justify-center text-white shadow-lg">
                                        <i data-lucide="bot" class="w-7 h-7"></i>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-[#0b0f1a] rounded-full"></div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-white leading-tight">Zynkora AI Assistant</h3>
                                    <span class="text-[10px] text-green-500 uppercase font-extrabold tracking-widest">Pronto para Atender</span>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div class="flex-1 overflow-y-auto p-8 space-y-6 scrollbar-hide" id="chat-messages">
                            <template x-for="msg in messages" :key="msg.id">
                                <div :class="msg.type === 'user' ? 'flex justify-end' : 'flex justify-start'">
                                    <div :class="msg.type === 'user' 
                                        ? 'bg-primary-600 text-white rounded-[24px] rounded-tr-none shadow-xl shadow-primary-500/10' 
                                        : 'bg-white/5 text-white/90 rounded-[24px] rounded-tl-none border border-white/10 backdrop-blur-md'"
                                        class="max-w-[85%] p-5 text-sm leading-relaxed animate-in fade-in slide-in-from-bottom-4 duration-500">
                                        <p x-text="msg.text"></p>
                                        <div class="mt-3 text-[10px] opacity-40 flex justify-end font-mono" x-text="msg.time"></div>
                                    </div>
                                </div>
                            </template>

                            <!-- Typing Indicator -->
                            <div x-show="isTyping" class="flex justify-start animate-in fade-in duration-300">
                                <div class="bg-white/5 border border-white/10 p-5 rounded-[24px] rounded-tl-none">
                                    <div class="flex gap-1.5">
                                        <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce"></div>
                                        <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                                        <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Suggestions -->
                        <div class="px-6 pb-4 overflow-x-auto whitespace-nowrap scrollbar-hide flex gap-3">
                            <template x-for="suggestion in suggestions" :key="suggestion">
                                <button @click="sendMessage(suggestion)" 
                                        class="px-5 py-2.5 bg-white/5 border border-white/10 rounded-full text-xs font-semibold text-white/70 hover:bg-primary-500 hover:text-white hover:border-primary-500 transition-all">
                                    <span x-text="suggestion"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Chat Input -->
                        <div class="p-6 bg-white/5 border-t border-white/10">
                            <form @submit.prevent="sendMessage()" class="flex gap-3">
                                <input type="text" 
                                       x-model="input" 
                                       placeholder="Pergunte qualquer coisa sobre o Zynkora..." 
                                       class="flex-1 bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm text-white placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                                <button type="submit" 
                                        :disabled="!input.trim() || isTyping"
                                        class="w-14 h-14 btn-primary text-white rounded-2xl flex items-center justify-center disabled:opacity-50 transition-all">
                                    <i data-lucide="send" class="w-6 h-6"></i>
                                </button>
                            </form>
                            <p class="text-[10px] text-white/20 mt-4 text-center tracking-widest uppercase font-bold">
                                Sistema de Demonstração Zynkora AI
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Use Cases -->
    <section class="py-24 bg-white/[0.02]">
        <div class="container mx-auto px-6 text-center">
            <span class="text-primary-500 font-bold uppercase tracking-widest text-xs mb-4 block">Versatilidade</span>
            <h2 class="text-4xl font-bold font-display mb-16 text-white">Feito para o seu negócio</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <template x-for="useCase in useCases" :key="useCase.title">
                    <div class="bg-white/5 p-10 rounded-[32px] border border-white/5 hover:bg-white/10 hover:-translate-y-2 transition-all duration-500 group text-left">
                        <div class="w-16 h-16 bg-gradient-to-tr from-primary-500/20 to-secondary-500/20 rounded-2xl flex items-center justify-center text-primary-500 mb-8 group-hover:scale-110 transition-transform">
                            <i :data-lucide="useCase.icon" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-white" x-text="useCase.title"></h3>
                        <p class="text-white/40 leading-relaxed" x-text="useCase.desc"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center mb-16">
                <span class="text-primary-500 font-bold uppercase tracking-widest text-xs mb-4 block">Suporte</span>
                <h2 class="text-4xl font-bold font-display text-white mb-6">Dúvidas Frequentes</h2>
            </div>
            
            <div class="space-y-4">
                <template x-for="(faq, index) in faqs" :key="index">
                    <div class="bg-white/5 rounded-3xl border border-white/5 overflow-hidden transition-all"
                         :class="activeFaq === index ? 'border-primary-500/30 bg-white/10' : ''">
                        <button @click="activeFaq === index ? activeFaq = null : activeFaq = index" 
                                class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-white/5 transition-colors">
                            <span class="font-bold text-white text-lg" x-text="faq.q"></span>
                            <i data-lucide="chevron-down" class="w-6 h-6 text-primary-500 transition-transform duration-500" :class="activeFaq === index ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="activeFaq === index" 
                             x-collapse 
                             class="px-8 pb-8 text-white/50 leading-relaxed border-t border-white/5 pt-6">
                            <p x-text="faq.a"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-32 relative overflow-hidden">
        <div class="absolute inset-0 bg-primary-600/10"></div>
        <div class="container mx-auto px-6 relative z-10 text-center">
            <h2 class="text-4xl md:text-6xl font-bold font-display text-white mb-8 leading-tight">Pronto para evoluir <br>seu atendimento?</h2>
            <p class="text-white/50 text-xl mb-12 max-w-2xl mx-auto">
                Comece hoje mesmo com o Zynkora e experimente o futuro da automação comercial.
            </p>
            
            <div class="flex flex-wrap justify-center gap-6">
                <button @click="openLeadModal = true" class="btn-primary px-12 py-5 rounded-full text-white font-bold text-xl">
                    Começar Agora
                </button>
                <a href="https://wa.me/5511999999999" target="_blank" class="px-12 py-5 rounded-full bg-white/5 text-white font-bold text-xl backdrop-blur-md border border-white/10 hover:bg-white/10 transition-all flex items-center gap-3">
                    <i data-lucide="message-circle" class="w-7 h-7"></i> WhatsApp
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#050811] py-16 border-t border-white/5">
        <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-10">
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="Zynkora" style="height: 50px;">
            </a>
            
            <p class="text-white/20 text-sm font-medium">
                &copy; 2026 Zynkora Chatbot. Uma solução <span class="text-white/40">PaivaTech Solutions</span>.
            </p>
            
            <div class="flex gap-8 text-white/30">
                <a href="#" class="hover:text-primary-500 transition-colors"><i data-lucide="instagram" class="w-6 h-6"></i></a>
                <a href="#" class="hover:text-primary-500 transition-colors"><i data-lucide="linkedin" class="w-6 h-6"></i></a>
                <a href="#" class="hover:text-primary-500 transition-colors"><i data-lucide="twitter" class="w-6 h-6"></i></a>
            </div>
        </div>
    </footer>

    <!-- Lead Modal -->
    <div x-show="openLeadModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-6"
         x-cloak>
        <div @click="openLeadModal = false" class="absolute inset-0 bg-dark-900/90 backdrop-blur-xl animate-in fade-in duration-500"></div>
        
        <div class="bg-dark-800 w-full max-w-xl rounded-[48px] overflow-hidden relative z-10 border border-white/10 shadow-2xl animate-in zoom-in-95 duration-500">
            <div class="p-10 md:p-16">
                <div class="text-center mb-10">
                    <div class="w-20 h-20 bg-primary-500/10 text-primary-500 rounded-[32px] flex items-center justify-center mx-auto mb-8 border border-primary-500/20">
                        <i data-lucide="rocket" class="w-10 h-10"></i>
                    </div>
                    <h2 class="text-4xl font-bold font-display mb-4 text-white">Comece sua Jornada</h2>
                    <p class="text-white/40">Transforme seu atendimento com a potência do Zynkora.</p>
                </div>
                
                <form @submit.prevent="submitLead" class="space-y-6">
                    <div>
                        <input type="text" x-model="leadForm.name" required placeholder="Nome Completo"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <input type="email" x-model="leadForm.email" required placeholder="E-mail"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        <input type="tel" x-model="leadForm.whatsapp" required placeholder="WhatsApp"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-5 text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                    </div>
                    
                    <button type="submit" 
                            :disabled="submittingLead"
                            class="w-full py-6 btn-primary text-white font-bold rounded-2xl text-lg flex items-center justify-center gap-4 mt-4 disabled:opacity-50">
                        <span x-show="!submittingLead">Ativar Teste Grátis</span>
                        <span x-show="submittingLead" class="flex gap-2">
                            <span class="w-2 h-2 bg-white rounded-full animate-bounce"></span>
                            <span class="w-2 h-2 bg-white rounded-full animate-bounce [animation-delay:0.2s]"></span>
                            <span class="w-2 h-2 bg-white rounded-full animate-bounce [animation-delay:0.4s]"></span>
                        </span>
                    </button>
                </form>
            </div>
            
            <button @click="openLeadModal = false" class="absolute top-10 right-10 text-white/30 hover:text-white transition-colors">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
        </div>
    </div>

    <!-- Success Toast -->
    <div x-show="showToast" 
         class="fixed bottom-10 right-10 z-[200] px-8 py-5 bg-white text-dark-900 rounded-[24px] shadow-2xl flex items-center gap-4 animate-in slide-in-from-right-10 duration-500"
         x-cloak>
        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
            <i data-lucide="check" class="w-5 h-5 text-white"></i>
        </div>
        <span class="text-lg font-bold" x-text="toastMessage"></span>
    </div>

    <script>
        function demoApp() {
            return {
                scrolled: false,
                openLeadModal: false,
                submittingLead: false,
                showToast: false,
                toastMessage: '',
                activeFaq: null,
                input: '',
                isTyping: false,
                messageCount: 0,
                messages: [
                    { id: 1, type: 'bot', text: 'Olá! 👋 Eu sou o assistente inteligente da Zynkora. Como posso ajudar você hoje a descobrir o poder da automação?', time: '09:00' }
                ],
                suggestions: [
                    'Quais são os planos?',
                    'Integração com WhatsApp',
                    'Transfere para humano?',
                    'Treinar com documentos'
                ],
                benefits: [
                    { title: 'Atendimento 24/7', desc: 'Nunca mais perca um lead por falta de resposta imediata.', icon: 'clock' },
                    { title: 'Redução de Custos', desc: 'Automatize até 80% das demandas repetitivas da sua equipe.', icon: 'trending-down' },
                    { title: 'IA Generativa', desc: 'Respostas fluídas e naturais com GPT-4.', icon: 'sparkles' }
                ],
                useCases: [
                    { title: 'Comercial & Vendas', desc: 'Qualifique leads e feche vendas automaticamente.', icon: 'shopping-cart' },
                    { title: 'Suporte Técnico', desc: 'Resolva dúvidas com base na sua documentação.', icon: 'help-circle' },
                    { title: 'Agendamentos', desc: 'Sincronize reuniões sem idas e vindas de mensagens.', icon: 'calendar' }
                ],
                faqs: [
                    { q: 'O Zynkora integra com WhatsApp?', a: 'Sim, temos integração oficial com a API do WhatsApp Business.' },
                    { q: 'Como é feito o treinamento?', a: 'Basta anexar PDFs ou URLs e a IA aprende em segundos.' },
                    { q: 'Posso usar minha própria marca?', a: 'Sim, oferecemos solução White Label no plano Enterprise.' },
                    { q: 'Qual o tempo de implementação?', a: 'Você coloca seu primeiro bot no ar em menos de 15 minutos.' }
                ],
                leadForm: { name: '', email: '', whatsapp: '' },

                async sendMessage(text = null) {
                    const messageText = text || this.input;
                    if (!messageText.trim() || this.isTyping || this.messageCount >= 20) return;

                    const userMsg = {
                        id: Date.now(),
                        type: 'user',
                        text: messageText,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    };
                    this.messages.push(userMsg);
                    this.input = '';
                    this.messageCount++;
                    this.scrollToBottom();
                    this.isTyping = true;
                    
                    try {
                        const response = await fetch('/api/demo-chat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ message: messageText })
                        });
                        const data = await response.json();
                        setTimeout(() => {
                            this.isTyping = false;
                            this.messages.push({
                                id: Date.now() + 1,
                                type: 'bot',
                                text: data.text,
                                time: data.timestamp
                            });
                            this.scrollToBottom();
                            if (this.messageCount === 5) {
                                setTimeout(() => { this.openLeadModal = true; }, 2000);
                            }
                        }, 1000);
                    } catch (error) {
                        this.isTyping = false;
                    }
                },

                async submitLead() {
                    this.submittingLead = true;
                    try {
                        const response = await fetch('/api/demo-lead', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.leadForm)
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.toast('Sucesso! Verifique seu e-mail.', 'success');
                            setTimeout(() => {
                                this.openLeadModal = false;
                                this.submittingLead = false;
                                this.leadForm = { name: '', email: '', whatsapp: '' };
                            }, 1500);
                        }
                    } catch (error) {
                        this.submittingLead = false;
                    }
                },

                scrollToBottom() {
                    setTimeout(() => {
                        const chatContainer = document.getElementById('chat-messages');
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }, 50);
                },

                toast(msg, type) {
                    this.toastMessage = msg;
                    this.showToast = true;
                    setTimeout(() => this.showToast = false, 3000);
                },

                init() {
                    lucide.createIcons();
                }
            }
        }
    </script>
</body>
</html>
