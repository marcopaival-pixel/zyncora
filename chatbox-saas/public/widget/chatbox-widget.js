/**
 * Zynkora Chatbox Widget — Premium Real-time Version (Secured)
 * Inclua no site com:
 * <script src="https://SEU_DOMINIO/widget/chatbox-widget.js" data-token="SEU_TOKEN_PUBLICO" data-api="https://SEU_DOMINIO/api" defer></script>
 */
(function () {
    const el = document.currentScript;
    if (!el) return;

    // Aceita data-token ou fallback para data-slug por retrocompatibilidade
    const token = el.getAttribute('data-token') || el.getAttribute('data-slug');
    const apiBase = (el.getAttribute('data-api') || '').replace(/\/$/, '');
    if (!token || !apiBase) return;

    // --- State ---
    let config = null;
    let sessionJwt = null;
    let widgetSessionId = localStorage.getItem('cb_session_' + token) || null;
    let visitorToken = localStorage.getItem('cb_visitor_' + token) || '';
    let conversationId = localStorage.getItem('cb_conv_' + token) || '';
    let isOpen = false;
    let pusher = null;
    let channel = null;

    // --- DOM Elements ---
    const root = document.createElement('div');
    root.id = 'cb-widget-root';
    document.body.appendChild(root);

    const style = document.createElement('style');
    style.textContent = `
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        #cb-widget-root { font-family: 'Inter', system-ui, -apple-system, sans-serif; --cb-primary: #0ea5e9; }
        .cb-fab {
            position: fixed; bottom: 24px; right: 24px; z-index: 99999;
            width: 60px; height: 60px; border-radius: 30px;
            background: var(--cb-primary); color: white; border: none;
            cursor: pointer; box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .cb-fab:hover { transform: scale(1.05); }
        .cb-fab svg { width: 28px; height: 28px; transition: transform 0.3s; }
        .cb-fab.open svg { transform: rotate(90deg); }

        .cb-panel {
            position: fixed; bottom: 100px; right: 24px; z-index: 99998;
            width: 380px; height: 600px; max-height: calc(100vh - 120px);
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);
            border-radius: 24px; box-shadow: 0 12px 48px rgba(0,0,0,0.12);
            display: none; flex-direction: column; overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05); transform: translateY(20px); opacity: 0;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .cb-panel.visible { display: flex; transform: translateY(0); opacity: 1; }

        .cb-header {
            padding: 20px 24px; background: var(--cb-primary); color: white;
            display: flex; align-items: center; gap: 12px;
        }
        .cb-header img { width: 32px; height: 32px; border-radius: 8px; object-fit: cover; background: white; }
        .cb-header-info h3 { margin: 0; font-size: 16px; font-weight: 700; }
        .cb-header-info p { margin: 0; font-size: 12px; opacity: 0.8; }

        .cb-messages {
            flex: 1; overflow-y: auto; padding: 20px;
            display: flex; flex-direction: column; gap: 12px;
            background: #f8fafc; scroll-behavior: smooth;
        }
        .cb-msg {
            max-width: 80%; padding: 12px 16px; border-radius: 16px;
            font-size: 14px; line-height: 1.5; word-break: break-word;
            animation: cb-fade-in 0.3s ease;
        }
        .cb-msg-visitor { align-self: flex-end; background: var(--cb-primary); color: white; border-bottom-right-radius: 4px; }
        .cb-msg-bot, .cb-msg-agent { align-self: flex-start; background: white; color: #1e293b; border-bottom-left-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        .cb-footer { padding: 16px; background: white; border-top: 1px solid #f1f5f9; display: flex; gap: 8px; align-items: center; }
        .cb-input {
            flex: 1; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 10px 16px; font-size: 14px; outline: none; transition: border-color 0.2s;
        }
        .cb-input:focus { border-color: var(--cb-primary); }
        .cb-send {
            width: 40px; height: 40px; border-radius: 10px; background: var(--cb-primary);
            color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .cb-send svg { width: 20px; height: 20px; }

        @keyframes cb-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 480px) {
            .cb-panel { width: calc(100vw - 32px); right: 16px; bottom: 88px; height: calc(100vh - 120px); }
        }
    `;
    document.head.appendChild(style);

    const fab = document.createElement('button');
    fab.className = 'cb-fab';
    fab.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>`;

    const panel = document.createElement('div');
    panel.className = 'cb-panel';
    panel.innerHTML = `
        <div class="cb-header">
            <img src="" id="cb-logo" style="display:none">
            <div class="cb-header-info">
                <h3 id="cb-name">Chat</h3>
                <p>Online agora</p>
            </div>
        </div>
        <div class="cb-messages" id="cb-msgs"></div>
        <div class="cb-footer">
            <input type="text" class="cb-input" id="cb-input" placeholder="Digite sua mensagem...">
            <button class="cb-send" id="cb-send">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
        </div>
    `;

    root.append(fab, panel);

    const msgsContainer = panel.querySelector('#cb-msgs');
    const input = panel.querySelector('#cb-input');
    const sendBtn = panel.querySelector('#cb-send');

    // Branding Element (White Label Control)
    const branding = document.createElement('div');
    branding.className = 'cb-branding';
    branding.style.cssText = 'text-align: center; font-size: 11px; padding: 4px; color: #94a3b8; background: #f8fafc; border-top: 1px solid #f1f5f9; display: none;';
    branding.innerHTML = '⚡ Powered by <strong>Zynkora</strong>';
    panel.appendChild(branding);

    // --- Logic ---
    async function apiFetch(path, opts = {}) {
        const headers = Object.assign({
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Visitor-Token': visitorToken,
        }, opts.headers || {});

        if (sessionJwt) {
            headers['Authorization'] = 'Bearer ' + sessionJwt;
        }
        if (widgetSessionId) {
            headers['X-Widget-Session-Id'] = widgetSessionId;
        }

        const res = await fetch(apiBase + path, { ...opts, headers });
        if (!res.ok) {
            if (res.status === 403 || res.status === 429) {
                console.error('Widget Blocked/Rate Limited by Security Engine');
            }
            throw new Error('API Error');
        }
        return res.json();
    }

    function addMessage(msg) {
        const div = document.createElement('div');
        div.className = \`cb-msg cb-msg-\${msg.sender_type}\`;
        div.textContent = msg.body;
        msgsContainer.appendChild(div);
        msgsContainer.scrollTop = msgsContainer.scrollHeight;
    }

    async function loadMessages() {
        if (!conversationId) return;
        try {
            const data = await apiFetch(`/v1/widget/${token}/conversations/${conversationId}/messages`);
            msgsContainer.innerHTML = '';
            const messages = data.data || data; // handle pagination object or array
            (Array.isArray(messages) ? messages : []).forEach(addMessage);
        } catch (e) { console.error('Load error', e); }
    }

    async function sendMessage() {
        const body = input.value.trim();
        if (!body) return;
        input.value = '';

        try {
            // Se não tem conversa, inicia uma
            if (!conversationId) {
                const conv = await apiFetch(`/v1/widget/${token}/conversations`, { method: 'POST', body: JSON.stringify({}) });
                conversationId = conv.id;
                visitorToken = conv.visitor_token;
                localStorage.setItem('cb_visitor_' + token, visitorToken);
                localStorage.setItem('cb_conv_' + token, conversationId);
                initRealtime();
            }

            await apiFetch(`/v1/widget/${token}/conversations/${conversationId}/messages`, {
                method: 'POST',
                body: JSON.stringify({ body })
            });
            // A mensagem aparecerá via WebSocket se estiver ativo, senão adicionamos manualmente
            if (!pusher) {
                addMessage({ sender_type: 'visitor', body });
            }
        } catch (e) { console.error('Send error', e); }
    }

    function initRealtime() {
        if (!config || !config.broadcasting || !config.broadcasting.key || !conversationId || !visitorToken) return;
        if (pusher) return;

        const bc = config.broadcasting;
        
        // Carrega script do Pusher se não existir
        if (typeof Pusher === 'undefined') {
            const script = document.createElement('script');
            script.src = "https://js.pusher.com/8.0.1/pusher.min.js";
            script.onload = () => setupPusher(bc);
            document.head.appendChild(script);
        } else {
            setupPusher(bc);
        }
    }

    function setupPusher(bc) {
        pusher = new Pusher(bc.key, {
            cluster: 'mt1', // Reverb ignora isso mas Pusher-js exige
            wsHost: bc.host || window.location.hostname,
            wsPort: bc.port || 80,
            wssPort: bc.port || 443,
            forceTLS: bc.scheme === 'https',
            enabledTransports: ['ws', 'wss'],
            disableStats: true
        });

        // Canal seguro gerado no backend: conversation.v2.{id}.{token}
        const channelName = `conversation.v2.${conversationId}.${visitorToken}`;
        channel = pusher.subscribe(channelName);
        channel.bind('message.created', function(data) {
            if (data.message && data.message.sender_type !== 'visitor') {
                addMessage(data.message);
            }
            if (data.message && data.message.sender_type === 'visitor' && !document.contains(msgsContainer.lastChild)) {
                 // caso a gente queira confirmar o envio do próprio visitante
            }
        });
    }

    fab.onclick = async () => {
        try {
            if (!isOpen && !config) {
                // Realiza o Bootstrap de Segurança e Obtém Configurações com Graceful Degradation
                fab.style.opacity = '0.5'; // Loading state
                config = await apiFetch(`/v1/widget/bootstrap/${token}`);
                fab.style.opacity = '1';
                
                // Salva os dados de sessão retornados pelo backend para manter o fingerprint
                sessionJwt = config.jwt;
                if (config.session_id) {
                    widgetSessionId = config.session_id;
                    localStorage.setItem('cb_session_' + token, widgetSessionId);
                }

                if (config.theme && config.theme.color) {
                    root.style.setProperty('--cb-primary', config.theme.color);
                }
                if (config.name) panel.querySelector('#cb-name').textContent = config.name;
                if (config.theme && config.theme.logo) {
                    const logo = panel.querySelector('#cb-logo');
                    logo.src = config.theme.logo;
                    logo.style.display = 'block';
                }

                // Controle de White Label (Monetização)
                if (config.white_label === false) {
                    branding.style.display = 'block';
                }
                
                await loadMessages();
                initRealtime();
            }

            // Apenas abre se o config estiver carregado com sucesso
            if (config) {
                isOpen = !isOpen;
                panel.classList.toggle('visible', isOpen);
                fab.classList.toggle('open', isOpen);
            }

        } catch (error) {
            console.warn("Zynkora Widget: Indisponível no momento ou bloqueado por segurança.");
            fab.style.opacity = '1';
            // Graceful degradation: não quebra o site, apenas não abre o painel.
        }
    };

    sendBtn.onclick = sendMessage;
    input.onkeydown = (e) => { if (e.key === 'Enter') sendMessage(); };

})();
