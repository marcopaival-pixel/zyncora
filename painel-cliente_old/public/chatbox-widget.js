/**
 * ChatBox SaaS - Universal Widget Loader
 * Este script deve ser carregado pelos clientes nos seus sites.
 */
(function() {
    // Configurações extraídas do script tag
    const script = document.currentScript;
    const companyId = script.getAttribute('data-company-id');
    const apiUrl = script.getAttribute('data-api-url') || 'https://api.seusistema.com';

    // 1. Criar Containers
    const widgetContainer = document.createElement('div');
    widgetContainer.id = 'chatbox-widget-root';
    Object.assign(widgetContainer.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        zIndex: '9999',
        fontFamily: 'sans-serif'
    });

    // 2. Botão de Abrir/Fechar
    const toggleBtn = document.createElement('button');
    toggleBtn.innerHTML = '💬';
    Object.assign(toggleBtn.style, {
        width: '60px',
        height: '60px',
        borderRadius: '50%',
        backgroundColor: '#6366f1',
        color: 'white',
        border: 'none',
        fontSize: '24px',
        cursor: 'pointer',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        transition: 'all 0.3s ease'
    });

    // 3. Iframe do Chat
    const chatIframe = document.createElement('iframe');
    chatIframe.src = `${apiUrl}/widget?id=${companyId}`;
    Object.assign(chatIframe.style, {
        width: '350px',
        height: '500px',
        border: 'none',
        borderRadius: '20px',
        boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
        display: 'none',
        marginBottom: '15px'
    });

    let isOpen = false;
    toggleBtn.onclick = () => {
        isOpen = !isOpen;
        chatIframe.style.display = isOpen ? 'block' : 'none';
        toggleBtn.innerHTML = isOpen ? '✕' : '💬';
        toggleBtn.style.backgroundColor = isOpen ? '#000' : '#6366f1';
    };

    widgetContainer.appendChild(chatIframe);
    widgetContainer.appendChild(toggleBtn);
    document.body.appendChild(widgetContainer);

    console.log('ChatBox Widget carregado para a empresa:', companyId);
})();
