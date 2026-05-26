# Painel do Cliente & Atendente (SaaS Chatbot)

Este repositório contém o novo sistema de front-end e back-end para o **Painel do Cliente** e **Painel do Atendente**, construído com uma stack moderna focada em performance, tempo real e design premium (Glassmorphism).

## 🚀 Tecnologias Utilizadas

### Frontend
- **Framework**: Next.js 14+ (App Router)
- **Styling**: Tailwind CSS + Custom Design System
- **Real-time**: Socket.io-client
- **Animações**: Framer Motion / CSS Transitions

### Backend
- **Runtime**: Node.js
- **Framework**: Express.js
- **ORM**: Prisma (PostgreSQL / MySQL)
- **Real-time**: Socket.io
- **Auth**: JWT (JSON Web Tokens)

---

## 🛠️ Instalação e Execução

### 1. Pré-requisitos
- Node.js (v18 ou superior)
- Banco de Dados (PostgreSQL recomendado)

### 2. Configuração do Backend
Entre na pasta do backend e instale as dependências:
```bash
cd backend
npm install
```

Crie um ficheiro `.env` na raiz da pasta `backend`:
```env
PORT=3001
DATABASE_URL="postgresql://user:password@localhost:5432/chatbox_saas"
JWT_SECRET="sua_chave_secreta_aqui"
```

Inicie o servidor:
```bash
npm run dev
```

### 3. Configuração do Frontend (Painel)
Entre na pasta do frontend e instale as dependências:
```bash
cd frontend
npm install
```

Inicie o painel:
```bash
npm run dev
```
O painel estará disponível em `http://localhost:3000`.

---

## 📂 Estrutura de Módulos (Navegação)

| Módulo | Caminho (URL) | Público |
| :--- | :--- | :--- |
| **Login** | `/login` | Todos |
| **Dashboard** | `/dashboard` | Administrador Empresa |
| **Chat ao Vivo** | `/chat` | Administrador / Supervisor |
| **Painel do Agente** | `/agent/chat` | Atendente (Lite) |
| **Editor de Bot** | `/bots/flow` | Administrador |
| **Relatórios** | `/reports` | Administrador |
| **Integração** | `/integration` | Administrador |

---

## 🔗 Widget de Chat (Instalação em Clientes)

Para que os seus clientes usem o chat nos sites deles, eles devem incluir o script localizado em:
`painel-cliente/public/chatbox-widget.js`

**Exemplo de uso:**
```html
<script 
  src="http://seu-dominio.com/chatbox-widget.js" 
  data-company-id="UUID_DA_EMPRESA" 
  data-api-url="http://sua-api.com"
  defer
></script>
```

---

## 🛡️ Governança e Multi-tenancy

O sistema foi desenhado seguindo os princípios de isolamento de dados:
- **Middleware de Tenancy**: Todas as rotas de API injetam o `company_id` validado pelo JWT.
- **Rooms de Socket**: O tráfego de mensagens é isolado por salas virtuais no Socket.io baseadas no ID da empresa.
- **Auditoria**: Todas as ações são registradas no log de auditoria para conformidade e controle.

---

## ⚠️ Notas de Produção
1. Certifique-se de executar `npx prisma migrate dev` para criar as tabelas antes de iniciar.
2. Altere o `JWT_SECRET` para uma chave forte em produção.
3. Para o chat em tempo real via WhatsApp, configure o **Webhook URL** no painel da Meta apontando para `/api/webhooks/whatsapp/:slug`.
