const express = require('express');
const router = express.Router();
const { getIO } = require('../services/socket');

// Webhook para receber mensagens do WhatsApp (Simulação Cloud API Meta)
router.post('/whatsapp/:companySlug', async (req, res) => {
    const { companySlug } = req.params;
    const { messages, contacts } = req.body; // Estrutura simplificada do webhook da Meta

    console.log(`Mensagem recebida para a empresa ${companySlug}:`, messages[0].text.body);

    // 1. Localizar empresa e contato no Banco (Simulado)
    const companyId = "uuid-da-empresa-baseado-no-slug";
    
    // 2. Emitir evento via Socket.io para o painel do atendente em tempo real
    const io = getIO();
    io.to(companyId).emit('newMessage', {
        id: Math.random().toString(36).substr(2, 9),
        content: messages[0].text.body,
        sender: 'visitor',
        contactName: contacts[0].profile.name,
        timestamp: new Date()
    });

    // 3. Notificação Global para o Painel
    io.to(companyId).emit('notification', {
        title: 'Nova Mensagem WhatsApp',
        message: `${contacts[0].profile.name}: ${messages[0].text.body}`,
        type: 'message'
    });

    res.status(200).send('EVENT_RECEIVED');
});

// Verificação do Webhook (Obrigatório pela Meta)
router.get('/whatsapp/:companySlug', (req, res) => {
    const mode = req.query['hub.mode'];
    const token = req.query['hub.verify_token'];
    const挑战 = req.query['hub.challenge'];

    if (mode && token === 'SEU_VERIFY_TOKEN_CONFIGURADO') {
        res.status(200).send(挑战);
    } else {
        res.status(403).end();
    }
});

module.exports = router;
