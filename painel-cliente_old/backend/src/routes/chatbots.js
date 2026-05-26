const express = require('express');
const router = express.Router();

// Listar Bots da Empresa
router.get('/', (req, res) => {
    // const bots = await prisma.chatbot.findMany({ where: { companyId: req.companyId } });
    res.json([
        { id: '1', name: 'Boas Vindas', active: true, updatedAt: new Date() },
        { id: '2', name: 'Suporte Técnico', active: false, updatedAt: new Date() }
    ]);
});

// Atualizar Fluxo do Bot
router.put('/:id/flow', (req, res) => {
    const { id } = req.params;
    const { flowDefinition } = req.body;
    
    // Na vida real: await prisma.chatbot.update({ where: { id, companyId: req.companyId }, data: { flowDefinition } });
    
    res.json({ success: true, message: 'Fluxo atualizado com sucesso' });
});

module.exports = router;
