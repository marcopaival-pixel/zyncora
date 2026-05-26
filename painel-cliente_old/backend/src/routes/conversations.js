const express = require('express');
const router = express.Router();

// Mock do DB (Substituir por Prisma nas queries reais)
let conversations = [
    { id: '1', contact: 'João Silva', status: 'open', lastMessage: 'Olá...', createdAt: new Date() },
    { id: '2', contact: 'Maria Souza', status: 'pending', lastMessage: 'Como funciona?', createdAt: new Date() }
];

// Listar conversas (Filtrado por Empresa e Atendente/Setor via Middleware)
router.get('/', (req, res) => {
    const { role, id: userId, sectorId } = req.user;
    
    // Se for atendente comum, filtra apenas o que é dele ou do setor dele
    // Em Prisma seria: 
    // const where = { companyId: req.companyId, OR: [{ agentId: userId }, { sectorId: sectorId }] };
    
    res.json({ message: `Lista de conversas para o papel: ${role}` });
});

// Transferir Conversa
router.post('/:id/transfer', (req, res) => {
    const { id } = req.params;
    const { targetAgentId, targetSectorId } = req.body;
    
    // Lógica: Update conversation SET agentId = targetAgentId, sectorId = targetSectorId
    // req.io.to(req.companyId).emit('conversationTransferred', { id, targetAgentId });
    
    res.json({ success: true, message: 'Conversa transferida com sucesso' });
});

// Alterar Status (Aberta, Em Atendimento, Finalizada)
router.patch('/:id/status', (req, res) => {
    const { id } = req.params;
    const { status } = req.body; // 'open', 'ongoing', 'closed'
    
    res.json({ success: true, status });
});

module.exports = router;
