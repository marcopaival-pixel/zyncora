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
        style="
            --chat-primary: {{ $chatbot->primary_color ?? $company->chat_color ?? '#0ea5e9' }};
            --chat-header: {{ $chatbot->header_color ?? $chatbot->primary_color ?? $company->chat_color ?? '#0ea5e9' }};
            --chat-message-bg: {{ $chatbot->message_color ?? '#f1f5f9' }};
        "
        data-company-slug="{{ $company->slug }}"
        data-api-base="{{ url('/api/v1/widget/'.rawurlencode($company->slug)) }}"
        data-welcome="{{ e($chatbot->initial_message ?? $company->welcome_message ?? 'Olá! Como podemos ajudar hoje?') }}"
        data-actions="{{ json_encode($chatbot ? $chatbot->actionCards()->where('is_active', true)->get() : []) }}"
    >
        <div class="chat-container">
            <div class="chat-header">
                @php
                    $avatarPath = null;
                    if ($chatbot && $chatbot->avatar_type === 'custom' && $chatbot->avatar_path) {
                        $avatarPath = asset('storage/'.$chatbot->avatar_path);
                    } elseif ($chatbot && $chatbot->avatar_type === 'company' && $company->logo_path) {
                        $avatarPath = asset('storage/'.$company->logo_path);
                    } elseif ($company->logo_path && (!$chatbot || $chatbot->avatar_type === 'default')) {
                        $avatarPath = asset('storage/'.$company->logo_path);
                    }
                @endphp
                @if($avatarPath)
                    <img src="{{ $avatarPath }}" alt="{{ $chatbot->name ?? $company->name }}" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover;">
                @else
                    <div class="chat-header-placeholder">
                        <i data-lucide="{{ $chatbot && $chatbot->avatar_type === 'ai' ? 'sparkles' : 'bot' }}" style="width: 22px; height: 22px;"></i>
                    </div>
                @endif
                <div>
                    <h4 style="font-size: 1rem; font-weight: 600;">{{ $chatbot->name ?? $company->name }}</h4>
                    <p style="font-size: 0.7rem; opacity: 0.9;">Atendimento Online</p>
                </div>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="message bot" id="chat-loading">Carregando conversa…</div>
            </div>

            <div class="chat-footer-wrapper">
                <div id="chat-action-cards" class="chat-action-cards"></div>
                <div class="chat-footer">
                    <input type="text" class="chat-input" id="message-input" placeholder="Digite sua mensagem..." autocomplete="off">
                    <button type="button" class="send-btn" id="send-btn" aria-label="Enviar">
                        <i data-lucide="send" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
            </div>

            <div class="branding">
                Powered by <strong>Chatbox SaaS</strong>
            </div>
        </div>

        @if($chatbot && $chatbot->mascot_type && $chatbot->mascot_type !== 'none')
            <div class="chatbox-mascot-container" id="chatbox-mascot-container">
                <div class="mascot-bubble">
                    {{ $chatbot->mascot_greeting ?? 'Posso Ajudar?' }}
                </div>
                <img src="{{ asset('images/mascots/' . $chatbot->mascot_type . '.png') }}" alt="Mascote" class="mascot-img">
            </div>
        @endif
    </div>
</body>
</html>
