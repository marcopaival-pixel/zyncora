<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Zynkora - Sua Central de Atendimento Inteligente')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        .legal-container {
            max-width: 800px;
            margin: 120px auto 60px;
            padding: 40px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .legal-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #fff;
        }
        .legal-content h2 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #10b981;
            font-size: 1.5rem;
        }
        .legal-content h3 {
            margin-top: 20px;
            margin-bottom: 10px;
            color: #fff;
            font-size: 1.2rem;
        }
        .legal-content p, .legal-content li {
            color: rgba(255,255,255,0.8);
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .legal-content ul {
            padding-left: 20px;
            margin-bottom: 20px;
        }
        .legal-date {
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
            margin-bottom: 40px;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(0,0,0,0.5);
            color: white;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="reveal-ready">
    <header>
        <div class="container nav">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Zynkora" style="height: 80px;">
            </a>
            <button class="mobile-toggle" aria-label="Abrir menu">
                <i data-lucide="menu"></i>
            </button>
            <div class="nav-links">
                <a href="/#features">Funcionalidades</a>
                <a href="/#pricing">Planos</a>
                <a href="/#faq">FAQ</a>
                <a href="/#contact">Contato</a>
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
        @yield('content')
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
                    <li><a href="/#features">Funcionalidades</a></li>
                    <li><a href="/#pricing">Preços</a></li>
                    <li><a href="#">Integrações</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Empresa</h4>
                <ul class="footer-links">
                    <li><a href="#">Sobre Nós</a></li>
                    <li><a href="/#contact">Contato</a></li>
                    <li><a href="{{ route('legal.lgpd-central') }}">Central LGPD</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('legal.privacy') }}">Privacidade</a></li>
                    <li><a href="{{ route('legal.terms') }}">Termos de Uso</a></li>
                    <li><a href="{{ route('legal.cookies') }}">Política de Cookies</a></li>
                </ul>
            </div>
        </div>
        <div class="container footer-bottom">
            <p>&copy; 2026 Zynkora. Todos os direitos reservados.</p>
            <p>Feito com ❤️ para o seu sucesso.</p>
        </div>
    </footer>

    <script>
        document.documentElement.classList.add('js-enabled');
        try {
            lucide.createIcons();
        } catch (e) {
            console.error('Lucide icons failed to load:', e);
        }

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
    </script>
</body>
</html>
