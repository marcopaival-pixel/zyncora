// painel-cliente/backend/src/services/socket.js
const { Server } = require("socket.io");

let io;

const initSocket = (server) => {
    io = new Server(server, {
        cors: {
            origin: "*",
            methods: ["GET", "POST"]
        }
    });

    io.on("connection", (socket) => {
        console.log("Novo cliente conectado:", socket.id);

        // O cliente deve se juntar a uma sala baseada no seu company_id
        socket.on("joinCompanyRoom", (companyId) => {
            socket.join(companyId);
            console.log(`Socket ${socket.id} entrou na sala da empresa: ${companyId}`);
        });

        socket.on("disconnect", () => {
            console.log("Cliente desconectado");
        });
    });

    return io;
};

const getIO = () => {
    if (!io) {
        throw new Error("Socket.io não inicializado!");
    }
    return io;
};

module.exports = { initSocket, getIO };
