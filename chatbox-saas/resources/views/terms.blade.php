<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Termos de Uso - ChatbotPro</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        .content-page { padding: 8rem 0; color: white; }
        .content-card { background: rgba(255, 255, 255, 0.05); padding: 3rem; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.1); }
        h1 { margin-bottom: 2rem; font-size: 2.5rem; }
        h2 { margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem; color: var(--primary); }
        p { margin-bottom: 1rem; color: rgba(255, 255, 255, 0.7); }
        ul { margin-left: 2rem; margin-bottom: 1rem; color: rgba(255, 255, 255, 0.7); }
        li { margin-bottom: 0.5rem; }
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
            <h1>Termos de Uso</h1>
            <p>Ao acessar ao site ChatbotPro, você concorda em cumprir estes termos de serviço, todas as leis e regulamentos aplicáveis ​​e concorda que é responsável pelo cumprimento de todas as leis locais aplicáveis.</p>
            
            <h2>1. Licença de Uso</h2>
            <p>É concedida permissão para baixar temporariamente uma cópia dos materiais (informações ou software) no site ChatbotPro, apenas para visualização transitória pessoal e não comercial.</p>
            
            <h2>2. Isenção de Responsabilidade</h2>
            <p>Os materiais no site da ChatbotPro são fornecidos 'como estão'. ChatbotPro não oferece garantias, expressas ou implícitas, e, por este meio, isenta e nega todas as outras garantias, incluindo, sem limitação, garantias implícitas ou condições de comercialização, adequação a um fim específico ou não violação de propriedade intelectual ou outra violação de direitos.</p>
            
            <h2>3. Limitações</h2>
            <p>Em nenhum caso o ChatbotPro ou seus fornecedores serão responsáveis ​​por quaisquer danos (incluindo, sem limitação, danos por perda de dados ou lucro ou devido a interrupção dos negócios) decorrentes do uso ou da incapacidade de usar os materiais em ChatbotPro.</p>
            
            <h2>4. Precisão dos Materiais</h2>
            <p>Os materiais exibidos no site da ChatbotPro podem incluir erros técnicos, tipográficos ou fotográficos. ChatbotPro não garante que qualquer material em seu site seja preciso, completo ou atual.</p>
            
            <h2>5. Links</h2>
            <p>O ChatbotPro não analisou todos os sites vinculados ao seu site e não é responsável pelo conteúdo de nenhum site vinculado. A inclusão de qualquer link não implica endosso por ChatbotPro do site.</p>
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
