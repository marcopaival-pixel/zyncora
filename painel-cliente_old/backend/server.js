const express = require('express');
const http = require('http');
const cors = require('cors');
require('dotenv').config();

const { initSocket } = require('./src/services/socket');
const { verifyToken } = require('./src/utils/auth');

const app = express();
const server = http.createServer(app);

// Inicializa Real-time
const io = initSocket(server);

app.use(cors());
app.use(express.json());

// Injeta o IO no request para uso nas rotas
app.use((req, res, next) => {
    req.io = io;
    next();
});

// Middleware de Autenticação e Tenancy
const authMiddleware = (req, res, next) => {
    const authHeader = req.headers.authorization;
    if (!authHeader) return res.status(401).json({ error: 'Token não fornecido' });

    const token = authHeader.split(' ')[1];
    const decoded = verifyToken(token);

    if (!decoded) return res.status(401).json({ error: 'Token inválido' });

    // Injeta dados do usuário e da empresa no request
    req.user = decoded;
    req.companyId = decoded.companyId;
    next();
};

// Rotas
const conversationRoutes = require('./src/routes/conversations');
const chatbotRoutes = require('./src/routes/chatbots');

app.use('/api/conversations', authMiddleware, conversationRoutes);
app.use('/api/chatbots', authMiddleware, chatbotRoutes);

const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
    console.log(`🚀 Painel do Cliente Full-stack rodando na porta ${PORT}`);
});
