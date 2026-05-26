<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zynkora - Sua Central de Atendimento Inteligente</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body class="reveal-ready">
    <!-- Floating Contact Button -->
    <a href="https://wa.me/5511999999999" class="floating-contact" target="_blank" aria-label="Fale conosco no WhatsApp">
        <i data-lucide="message-circle"></i>
        <span>Fale Conosco</span>
    </a>

    <header>
        <div class="container nav">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Zynkora" style="height: 80px;">
            </a>
            <button class="mobile-toggle" aria-label="Abrir menu">
                <i data-lucide="menu"></i>
            </button>
            <div class="nav-links">
                <a href="#features">Funcionalidades</a>
                <a href="#pricing">Planos</a>
                <a href="#faq">FAQ</a>
                <a href="#contact">Contato</a>
                @php
                    $loginUrl = Route::has('filament.admin.auth.login') ? route('filament.admin.auth.login') : '/admin/login';
                    $registerUrl = Route::has('filament.admin.auth.register') ? route('filament.admin.auth.register') : '/admin/register';
                @endphp
                <a href="{{ $loginUrl }}" class="btn-login">Login</a>
                <a href="{{ $registerUrl }}" class="btn-primary">Teste Grátis</a>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="container hero">
            <div class="hero-content">
                <span class="section-tag">SaaS de Chatbox Inteligente</span>
                <h1 class="hero-title">O chat que conecta você ao <span>mundo.</span></h1>
                <p class="hero-subtitle">
                    Atenda clientes, automatize respostas e aumente suas vendas com o chatbox mais completo do mercado.
                </p>
                <div class="hero-actions">
                    <a href="/admin/register" class="btn-primary btn-shimmer">Começar agora <i data-lucide="arrow-right"></i></a>
                    <a href="/demo" class="btn-secondary"><i data-lucide="play-circle"></i> Ver demonstração</a>
                </div>
                <div class="hero-check-group">
                    <span><i data-lucide="check"></i> Fácil de instalar</span>
                    <span><i data-lucide="check"></i> Sem cartão</span>
                    <span><i data-lucide="check"></i> 7 dias grátis</span>
                </div>
            </div>

            <div class="hero-visual">
                <div class="mockup-wrapper">
                    <img src="{{ asset('images/zynkora_dashboard_mockup.png') }}" alt="Zynkora Dashboard" class="mockup-img">
                    <div class="mockup-glow"></div>
                </div>
            </div>

            <div class="hero-quick-features">
                <div class="quick-feature-card">
                    <div class="q-icon"><i data-lucide="zap"></i></div>
                    <div class="q-content">
                        <h4>Atendimento em Tempo Real</h4>
                        <p>Converse com seus clientes instantaneamente.</p>
                    </div>
                </div>
                <div class="quick-feature-card">
                    <div class="q-icon"><i data-lucide="bot"></i></div>
                    <div class="q-content">
                        <h4>Chatbot com IA</h4>
                        <p>Respostas automáticas inteligentes 24/7.</p>
                    </div>
                </div>
                <div class="quick-feature-card">
                    <div class="q-icon"><i data-lucide="users"></i></div>
                    <div class="q-content">
                        <h4>Multiatendimento</h4>
                        <p>Vários atendentes sem perder conversas.</p>
                    </div>
                </div>
                <div class="quick-feature-card">
                    <div class="q-icon"><i data-lucide="bar-chart-3"></i></div>
                    <div class="q-content">
                        <h4>Relatórios Avançados</h4>
                        <p>Acompanhe métricas e melhore resultados.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trusted By Section -->
        <div class="container trusted-by reveal">
            <p class="trusted-title">EMPRESAS QUE CONFIAM NA ZYNKORA</p>
            <div class="partners-grid">
                <div class="partner-item"><i data-lucide="layers"></i> TECHWAVE</div>
                <div class="partner-item"><i data-lucide="box"></i> BRANDLY</div>
                <div class="partner-item"><i data-lucide="cpu"></i> CONNECTA</div>
                <div class="partner-item"><i data-lucide="cog"></i> INNOVAR</div>
                <div class="partner-item"><i data-lucide="activity"></i> GROWTH</div>
                <div class="partner-item"><i data-lucide="globe"></i> FUTURO DIGITAL</div>
            </div>
        </div>

        <!-- Why Choose Us Section -->
        <section class="container why-us reveal">
            <div class="why-us-grid">
                <div class="why-us-content">
                    <span class="section-tag">Por que Zynkora?</span>
                    <h2 class="section-title">Tecnologia proprietária para empresas escaláveis</h2>
                    <p class="section-subtitle">Diferente de soluções genéricas, o Zynkora foi construído para suportar alta demanda e isolamento total de dados.</p>
                    <div class="why-us-items">
                        <div class="why-item">
                            <div class="why-icon"><i data-lucide="building-2"></i></div>
                            <div>
                                <h4>Multi-empresa (SaaS)</h4>
                                <p>Gerencie múltiplas unidades ou clientes em um único painel centralizado.</p>
                            </div>
                        </div>
                        <div class="why-item">
                            <div class="why-icon"><i data-lucide="layout-template"></i></div>
                            <div>
                                <h4>White-Label Ready</h4>
                                <p>Sua marca, suas regras. Personalize a plataforma com sua identidade visual.</p>
                            </div>
                        </div>
                        <div class="why-item">
                            <div class="why-icon"><i data-lucide="lock"></i></div>
                            <div>
                                <h4>Segurança de Ponta</h4>
                                <p>Logs de auditoria, backups automáticos e criptografia de dados sensíveis.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="why-us-visual">
                    <div class="tech-stack-card">
                        <h4>Stack Tecnológico</h4>
                        <div class="tech-grid">
                            <div class="tech-pill">PHP / Laravel</div>
                            <div class="tech-pill">OpenAI GPT-4</div>
                            <div class="tech-pill">WhatsApp API</div>
                            <div class="tech-pill">Redis</div>
                            <div class="tech-pill">PostgreSQL</div>
                            <div class="tech-pill">Tailwind CSS</div>
                        </div>
                        <p class="tech-note">Desenvolvido com excelência pela PaivaTech Solutions.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="container reveal">
            <div class="section-header">
                <span class="section-tag">Recursos Elite</span>
                <h2 class="section-title">Tudo que você precisa para crescer</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i data-lucide="message-square"></i></div>
                    <h3 class="feature-name">Multicanal</h3>
                    <p class="feature-desc">Integração nativa com WhatsApp, Telegram e Chat Web em uma única plataforma.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i data-lucide="cpu"></i></div>
                    <h3 class="feature-name">IA Inteligente</h3>
                    <p class="feature-desc">Utilize GPT-4 e outras tecnologias para criar fluxos de conversa naturais e
                        eficientes.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i data-lucide="pie-chart"></i></div>
                    <h3 class="feature-name">Dashboard Geral</h3>
                    <p class="feature-desc">Acompanhe métricas em tempo real, volume de mensagens e taxa de conversão.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i data-lucide="users"></i></div>
                    <h3 class="feature-name">Multi-atendente</h3>
                    <p class="feature-desc">Sua equipe pode assumir a conversa a qualquer momento com o transbordo
                        humano.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i data-lucide="shield-check"></i></div>
                    <h3 class="feature-name">LGPD Compliance</h3>
                    <p class="feature-desc">Segurança de dados e conformidade total com a Lei Geral de Proteção de
                        Dados.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i data-lucide="workflow"></i></div>
                    <h3 class="feature-name">Flow Builder</h3>
                    <p class="feature-desc">Crie fluxos complexos visualmente sem precisar escrever uma única linha de
                        código.</p>
                </div>
            </div>
        </section>

        <!-- Target Audience Section -->
        <section class="container audience reveal">
            <div class="section-header">
                <span class="section-tag">Versatilidade</span>
                <h2 class="section-title">Feito para o seu negócio</h2>
            </div>
            <div class="audience-grid">
                <div class="audience-card">
                    <i data-lucide="stethoscope"></i>
                    <h4>Clínicas</h4>
                    <p>Agendamentos e triagem automatizada.</p>
                </div>
                <div class="audience-card">
                    <i data-lucide="dumbbell"></i>
                    <h4>Academias</h4>
                    <p>Suporte a alunos e venda de planos.</p>
                </div>
                <div class="audience-card">
                    <i data-lucide="shopping-cart"></i>
                    <h4>E-commerce</h4>
                    <p>Rastreio de pedidos e dúvidas frequentes.</p>
                </div>
                <div class="audience-card">
                    <i data-lucide="briefcase"></i>
                    <h4>Escritórios</h4>
                    <p>Qualificação de leads e primeira abordagem.</p>
                </div>
                <div class="audience-card">
                    <i data-lucide="wrench"></i>
                    <h4>Prestadores</h4>
                    <p>Orçamentos rápidos e FAQ de serviços.</p>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="container reveal">
            <div class="section-header">
                <span class="section-tag">Invista no seu Sucesso</span>
                <h2 class="section-title">Planos Transparentes</h2>
            </div>
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-name">Startup</div>
                    <div class="pricing-price">R$ 97<span>/mês</span></div>
                    <ul class="pricing-features">
                        <li><i data-lucide="check-circle"></i> 1 Chatbot</li>
                        <li><i data-lucide="check-circle"></i> 1.000 mensagens/mês</li>
                        <li><i data-lucide="check-circle"></i> Suporte por Email</li>
                        <li><i data-lucide="check-circle"></i> Integração Web</li>
                    </ul>
                    <a href="#" class="btn-login" style="width: 100%;">Assinar Agora</a>
                </div>
                <div class="pricing-card featured">
                    <div class="featured-label">MAIS POPULAR</div>
                    <div class="pricing-name">Business Pro</div>
                    <div class="pricing-price">R$ 297<span>/mês</span></div>
                    <ul class="pricing-features">
                        <li><i data-lucide="check-circle"></i> 5 Chatbots</li>
                        <li><i data-lucide="check-circle"></i> Mensagens Ilimitadas</li>
                        <li><i data-lucide="check-circle"></i> Suporte Prioritário</li>
                        <li><i data-lucide="check-circle"></i> WhatsApp & Telegram</li>
                    </ul>
                    <a href="#" class="btn-primary" style="width: 100%;">Assinar Agora</a>
                </div>
                <div class="pricing-card">
                    <div class="pricing-name">Enterprise</div>
                    <div class="pricing-price">Custom</div>
                    <ul class="pricing-features">
                        <li><i data-lucide="check-circle"></i> Chatbots Ilimitados</li>
                        <li><i data-lucide="check-circle"></i> API Dedicada</li>
                        <li><i data-lucide="check-circle"></i> White Label</li>
                        <li><i data-lucide="check-circle"></i> SLA Garantido</li>
                    </ul>
                    <a href="#contact" class="btn-login" style="width: 100%;">Falar com Vendas</a>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="container reveal">
            <div class="section-header">
                <span class="section-tag">Dúvidas Frequentes</span>
                <h2 class="section-title">Perguntas comuns</h2>
            </div>
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">Preciso saber programar para usar? <i data-lucide="chevron-down"></i></div>
                    <div class="faq-answer">Não! Nossa plataforma possui um Flow Builder visual onde você arrasta e solta os elementos para criar seu chatbot.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">O Zynkora integra com WhatsApp Business API? <i data-lucide="chevron-down"></i></div>
                    <div class="faq-answer">Sim, temos integração nativa e oficial com a API do WhatsApp Business para garantir estabilidade e conformidade.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Posso usar minha própria marca (White-label)? <i data-lucide="chevron-down"></i></div>
                    <div class="faq-answer">Sim, o plano Enterprise permite a personalização completa da plataforma com sua identidade visual e domínio próprio.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Como funciona o suporte técnico? <i data-lucide="chevron-down"></i></div>
                    <div class="faq-answer">Oferecemos suporte por e-mail nos planos básicos e suporte prioritário via chat/WhatsApp nos planos Pro e Enterprise.</div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="container reveal">
            <div class="contact-container">
                <div class="contact-info">
                    <h2 class="section-title" style="margin-bottom: 1.5rem;">Pronto para revolucionar seu atendimento?
                    </h2>
                    <p style="color: rgba(255,255,255,0.7); margin-bottom: 2rem;">
                        Nossa equipe está pronta para ajudar você a implementar a melhor estratégia de automação para o
                        seu negócio.
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; color: white;">
                            <i data-lucide="mail"></i> contato@chatbotpro.com
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem; color: white;">
                            <i data-lucide="phone"></i> +55 (11) 9999-9999
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <form>
                        <div class="form-group">
                            <label>Nome Completo</label>
                            <input type="text" class="form-control" placeholder="Seu nome">
                        </div>
                        <div class="form-group">
                            <label>E-mail Corporativo</label>
                            <input type="email" class="form-control" placeholder="seuemail@empresa.com">
                        </div>
                        <div class="form-group">
                            <label>Mensagem</label>
                            <textarea class="form-control" rows="4" placeholder="Como podemos ajudar?"></textarea>
                        </div>
                        <button type="submit" class="btn-primary"
                            style="width: 100%; border: none; cursor: pointer;">Enviar Mensagem</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container footer-grid">
            <div class="footer-about">
                <a href="/" class="logo" style="margin-bottom: 1.5rem;">
                    <img src="{{ asset('images/logo.png') }}" alt="Zynkora" style="height: 70px;">
                </a>
                <p>A solução definitiva para atendimento inteligente e automação omnichannel para sua empresa.</p>
            </div>
            <div class="footer-col">
                <h4>Produto</h4>
                <ul class="footer-links">
                    <li><a href="#features">Funcionalidades</a></li>
                    <li><a href="#pricing">Preços</a></li>
                    <li><a href="#">Integrações</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Empresa</h4>
                <ul class="footer-links">
                    <li><a href="#">Sobre Nós</a></li>
                    <li><a href="#contact">Contato</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('privacy') }}">Privacidade</a></li>
                    <li><a href="{{ route('terms') }}">Termos de Uso</a></li>
                </ul>
            </div>
        </div>
        <div class="container footer-bottom">
            <p>&copy; 2026 Zynkora. Todos os direitos reservados.</p>
            <p>Feito com ❤️ para o seu sucesso.</p>
        </div>
    </footer>

    <script>
        // Set JS enabled flag for animations
        document.documentElement.classList.add('js-enabled');

        // Initialize Lucide icons
        try {
            lucide.createIcons();
        } catch (e) {
            console.error('Lucide icons failed to load:', e);
        }

        // Mobile Toggle Logic
        const mobileToggle = document.querySelector('.mobile-toggle');
        const navLinks = document.querySelector('.nav-links');

        mobileToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = mobileToggle.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.setAttribute('data-lucide', 'x');
            } else {
                icon.setAttribute('data-lucide', 'menu');
            }
            lucide.createIcons();
        });

        // FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                item.classList.toggle('active');
            });
        });

        // Scroll Reveal Logic
        const reveals = document.querySelectorAll('.reveal');
        const revealOnScroll = () => {
            const windowHeight = window.innerHeight;
            reveals.forEach(el => {
                const elementTop = el.getBoundingClientRect().top;
                const elementVisible = 100;
                if (elementTop < windowHeight - elementVisible) {
                    el.classList.add('active');
                }
            });
        };
        window.addEventListener('scroll', revealOnScroll);
        setTimeout(revealOnScroll, 100); // Trigger after a short delay to ensure layout is ready

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                navLinks.classList.remove('active'); // Close mobile menu
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>

    <x-cookie-consent />
</body>

</html>