// painel-cliente/backend/src/utils/auth.js
const jwt = require('jsonwebtoken');

const SECRET = process.env.JWT_SECRET || 'sua_chave_secreta_super_segura';

const generateToken = (payload) => {
    return jwt.sign(payload, SECRET, { expiresIn: '7d' });
};

const verifyToken = (token) => {
    try {
        return jwt.verify(token, SECRET);
    } catch (error) {
        return null;
    }
};

module.exports = { generateToken, verifyToken };
