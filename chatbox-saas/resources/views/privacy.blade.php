<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Política de Privacidade - ChatbotPro</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        .content-page { padding: 8rem 0; color: white; }
        .content-card { background: rgba(255, 255, 255, 0.05); padding: 3rem; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.1); }
        h1 { margin-bottom: 2rem; font-size: 2.5rem; }
        h2 { margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem; color: var(--primary); }
        p { margin-bottom: 1rem; color: rgba(255, 255, 255, 0.7); }
    </style>
</head>
<body>
    <header>
        <div class="container nav">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="ChatFlow" style="height: 70px;">
            </a>
            <div class="nav-links">
                <a href="/">Voltar ao Início</a>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn-login">Login</a>
                @elseif (Route::has('filament.admin.auth.login'))
                    <a href="{{ route('filament.admin.auth.login') }}" class="btn-login">Login</a>
                @else
                    <a href="/admin/login" class="btn-login">Login</a>
                @endif
            </div>
        </div>
    </header>

    <main class="container content-page">
        <div class="content-card">
            <h1>Política de Privacidade</h1>
            <p>Sua privacidade é importante para nós. É política do ChatbotPro respeitar a sua privacidade em relação a qualquer informação sua que possamos coletar no site ChatbotPro, e outros sites que possuímos e operamos.</p>
            
            <h2>1. Coleta de Informações</h2>
            <p>Solicitamos informações pessoais apenas quando realmente precisamos delas para lhe fornecer um serviço. Fazemo-lo por meios justos e legais, com o seu conhecimento e consentimento. Também informamos por que estamos coletando e como será usado.</p>
            
            <h2>2. Uso de Dados</h2>
            <p>Apenas retemos as informações coletadas pelo tempo necessário para fornecer o serviço solicitado. Quando armazenamos dados, os protegemos dentro de meios comercialmente aceitáveis ​​para evitar perdas e roubos, bem como acesso, divulgação, cópia, uso ou modificação não autorizados.</p>
            
            <h2>3. Cookies</h2>
            <p>Utilizamos cookies para melhorar sua experiência em nossa plataforma. Você pode optar por desativar os cookies nas configurações do seu navegador.</p>
            
            <h2>4. Compartilhamento com Terceiros</h2>
            <p>Não compartilhamos informações de identificação pessoal publicamente ou com terceiros, exceto quando exigido por lei.</p>
            
            <h2>5. Contato</h2>
            <p>Se você tiver alguma dúvida sobre como lidamos com dados do usuário e informações pessoais, entre em contato conosco em contato@chatbotpro.com.</p>
        </div>
    </main>

    <footer style="margin-top: 0;">
        <div class="container footer-bottom">
            <p>&copy; 2026 ChatbotPro. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
</body>
</html>
