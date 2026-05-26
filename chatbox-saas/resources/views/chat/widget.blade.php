<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat com {{ $company->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/js/widget.js'])
</head>
<body class="chatbox-widget-page">
    <div
        id="chatbox-widget-root"
        class="chatbox-widget"
        style="--chat-primary: {{ $company->chat_color ?? '#0ea5e9' }}"
        data-company-slug="{{ $company->slug }}"
        data-api-base="{{ url('/api/v1/widget/'.rawurlencode($company->slug)) }}"
        data-welcome="{{ e($company->welcome_message ?? 'Olá! Como podemos ajudar hoje?') }}"
    >
        <div class="chat-container">
            <div class="chat-header">
                @if($company->logo_path)
                    <img src="{{ asset('storage/'.$company->logo_path) }}" alt="{{ $company->name }}">
                @else
                    <div class="chat-header-placeholder">
                        <i data-lucide="bot" style="width: 22px; height: 22px;"></i>
                    </div>
                @endif
                <div>
                    <h4 style="font-size: 1rem; font-weight: 600;">{{ $company->name }}</h4>
                    <p style="font-size: 0.7rem; opacity: 0.9;">Atendimento Online</p>
                </div>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="message bot" id="chat-loading">Carregando conversa…</div>
            </div>

            <div class="chat-footer">
                <input type="text" class="chat-input" id="message-input" placeholder="Digite sua mensagem..." autocomplete="off">
                <button type="button" class="send-btn" id="send-btn" aria-label="Enviar">
                    <i data-lucide="send" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <div class="branding">
                Powered by <strong>Chatbox SaaS</strong>
            </div>
        </div>
    </div>
</body>
</html>
