const express = require('express');
const router = express.Router();

// Listar Logs da Empresa
router.get('/', (req, res) => {
    // const logs = await prisma.auditLog.findMany({ where: { companyId: req.companyId }, orderBy: { createdAt: 'desc' } });
    res.json([
        { id: 1, user: 'Alice Oliveira', action: 'Transferiu atendimento #2024', time: 'Há 2 min' },
        { id: 2, user: 'Sistema', action: 'Chatbot "Boas Vindas" atualizado', time: 'Há 15 min' },
        { id: 3, user: 'Admin Tech', action: 'Novo atendente cadastrado: Bruno', time: 'Há 1h' },
        { id: 4, user: 'Bruno Mendes', action: 'Login realizado com sucesso', time: 'Há 2h' }
    ]);
});

// Criar Log (Função auxiliar interna)
// const createAuditLog = async (companyId, userId, action) => {
//     await prisma.auditLog.create({ data: { companyId, userId, action } });
// };

module.exports = router;
