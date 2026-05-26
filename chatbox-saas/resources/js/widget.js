import '../css/widget.css';
import { Bot, Send, createIcons } from 'lucide';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

function initLucide(root) {
    createIcons({
        icons: {
            bot: Bot,
            send: Send,
        },
        nameAttr: 'data-lucide',
        attrs: {
            class: 'lucide-icon',
        },
        root: root ?? document,
    });
}

function senderRole(senderType) {
    if (senderType === 'visitor') {
        return 'user';
    }
    if (senderType === 'agent') {
        return 'agent';
    }

    return 'bot';
}

/**
 * @param {{ 
 *   apiBase: string, 
 *   welcomeMessage: string, 
 *   echoHost: string, 
 *   echoKey: string,
 *   echoPort: string,
 *   echoScheme: string
 * }} config
 */
export function bootChatboxWidget(config) {
    const { apiBase, welcomeMessage } = config;
    const messagesContainer = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');

    if (!messagesContainer || !messageInput || !sendBtn) {
        return;
    }

    const companySlug = config.companySlug;
    const storageKey = `chatbox_conversation_${companySlug}`;

    let conversationId = null;
    let visitorToken = null;
    let ready = false;
    let echo = null;
    const processedMessageIds = new Set();

    initLucide(document.querySelector('.chatbox-widget') ?? document);

    function clearMessages() {
        messagesContainer.innerHTML = '';
    }

    function addMessage(content, role, id = null, type = 'text') {
        if (id && processedMessageIds.has(id)) return;
        if (id) processedMessageIds.add(id);

        const div = document.createElement('div');
        div.className = `message ${role}`;
        
        if (type === 'calendar') {
            div.innerHTML = `
                <div style="margin-bottom: 8px;">${content}</div>
                <div style="background: white; border-radius: 8px; padding: 12px; border: 1px solid var(--chat-primary);">
                    <input type="datetime-local" class="chat-calendar-input" style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 6px; margin-bottom: 8px; font-family: inherit;">
                    <button class="chat-calendar-btn" style="width: 100%; background: var(--chat-primary); color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; font-weight: 600;">Confirmar Horário</button>
                </div>
            `;
            
            // Attach event listener
            const btn = div.querySelector('.chat-calendar-btn');
            const input = div.querySelector('.chat-calendar-input');
            btn.addEventListener('click', () => {
                if (input.value) {
                    const formatted = new Date(input.value).toLocaleString('pt-BR');
                    messageInput.value = `Agendar para: ${formatted}`;
                    btn.disabled = true;
                    btn.textContent = 'Solicitado';
                    sendMessage();
                }
            });
        } else {
            div.textContent = content;
        }

        messagesContainer.appendChild(div);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function showError(msg) {
        clearMessages();
        const div = document.createElement('div');
        div.className = 'message bot';
        div.style.background = '#fef2f2';
        div.style.color = '#991b1b';
        div.textContent = msg;
        messagesContainer.appendChild(div);
    }

    async function fetchAllMessages() {
        const all = [];
        let page = 1;
        let lastPage = 1;
        const maxPages = 50;

        do {
            const url = `${apiBase}/conversations/${conversationId}/messages?per_page=100&page=${page}`;
            const res = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Visitor-Token': visitorToken,
                },
            });
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }
            const json = await res.json();
            const chunk = Array.isArray(json.data) ? json.data : [];
            all.push(...chunk);
            lastPage = json.meta && json.meta.last_page ? json.meta.last_page : 1;
            page++;
        } while (page <= lastPage && page <= maxPages);

        return all;
    }

    async function bootstrapConversation() {
        sendBtn.disabled = true;
        messageInput.disabled = true;

        let stored = null;
        try {
            stored = JSON.parse(localStorage.getItem(storageKey) || 'null');
        } catch (e) {
            stored = null;
        }

        const startRes = await fetch(`${apiBase}/conversations`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                visitor_token: stored && stored.visitor_token ? stored.visitor_token : undefined,
            }),
        });

        if (!startRes.ok) {
            throw new Error('Não foi possível iniciar o chat.');
        }

        const conv = await startRes.json();
        conversationId = conv.id;
        visitorToken = conv.visitor_token;
        localStorage.setItem(
            storageKey,
            JSON.stringify({
                conversation_id: conversationId,
                visitor_token: visitorToken,
            }),
        );

        const rows = await fetchAllMessages();
        const loadingEl = document.getElementById('chat-loading');
        if (loadingEl) {
            loadingEl.remove();
        }

        clearMessages();
        if (rows.length === 0) {
            addMessage(welcomeMessage, 'bot', null, 'text');
        } else {
            rows.forEach((m) => {
                const body = m.body != null ? String(m.body) : '';
                addMessage(body, senderRole(m.sender_type || 'bot'), m.id, m.message_type || 'text');
            });
        }

        initEcho();

        ready = true;
        sendBtn.disabled = false;
        messageInput.disabled = false;
        initLucide(document.querySelector('.chatbox-widget') ?? document);
    }

    function initEcho() {
        if (echo || !conversationId) return;

        try {
            echo = new Echo({
                broadcaster: 'reverb',
                key: config.echoKey,
                wsHost: config.echoHost,
                wsPort: config.echoPort ?? 80,
                wssPort: config.echoPort ?? 443,
                forceTLS: config.echoScheme === 'https',
                enabledTransports: ['ws', 'wss'],
            });

            echo.channel(`conversation.v2.${conversationId}.${visitorToken}`)
                .listen('.message.created', (e) => {
                    const msg = e.message;
                    addMessage(msg.body, senderRole(msg.sender_type), msg.id, msg.message_type || 'text');
                });
        } catch (err) {
            console.error('[Chatbox] Real-time init failed:', err);
        }
    }

    async function sendMessage() {
        if (!ready || !conversationId || !visitorToken) {
            return;
        }

        const text = messageInput.value.trim();
        if (!text) {
            return;
        }

        messageInput.value = '';
        addMessage(text, 'user');

        sendBtn.disabled = true;
        messageInput.disabled = true;

        try {
            const res = await fetch(`${apiBase}/conversations/${conversationId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Visitor-Token': visitorToken,
                },
                body: JSON.stringify({ body: text }),
            });

            if (!res.ok) {
                throw new Error('Falha ao enviar');
            }

            const data = await res.json();
            if (data.bot_message && data.bot_message.body) {
                addMessage(String(data.bot_message.body), senderRole(data.bot_message.sender_type || 'bot'), data.bot_message.id, data.bot_message.message_type || 'text');
            }
        } catch (e) {
            addMessage('Não foi possível enviar a mensagem. Tente novamente.', 'bot');
        } finally {
            sendBtn.disabled = false;
            messageInput.disabled = false;
            messageInput.focus();
        }
    }

    bootstrapConversation().catch(() => {
        const loadingEl = document.getElementById('chat-loading');
        if (loadingEl) {
            loadingEl.remove();
        }
        showError('Não foi possível ligar ao servidor de chat. Recarregue a página.');
        sendBtn.disabled = true;
        messageInput.disabled = true;
    });

    sendBtn.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
}

const root = document.getElementById('chatbox-widget-root');
if (root && root.dataset.apiBase) {
    bootChatboxWidget({
        companySlug: root.dataset.companySlug ?? '',
        apiBase: root.dataset.apiBase,
        welcomeMessage: root.dataset.welcome ?? 'Olá! Como podemos ajudar hoje?',
        echoHost: root.dataset.echoHost ?? window.location.hostname,
        echoKey: root.dataset.echoKey ?? '',
        echoPort: root.dataset.echoPort ?? '8080',
        echoScheme: root.dataset.echoScheme ?? 'http',
    });
}
