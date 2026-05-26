# 📘 Manual de Utilização — Chatbox SaaS

> **Versão:** 1.0 · **Plataforma:** Laravel + Filament v3 · **Atualizado:** Abril 2026

---

## Índice

1. [Visão Geral do Sistema](#1-visão-geral-do-sistema)
2. [Perfis de Acesso](#2-perfis-de-acesso)
3. [Acessando o Painel](#3-acessando-o-painel)
4. [Dashboard](#4-dashboard)
5. [Atendimento — Conversas](#5-atendimento--conversas)
6. [Automação — Chatbots](#6-automação--chatbots)
7. [Automação — Fluxos (Palavra-chave)](#7-automação--fluxos-palavra-chave)
8. [Integrações — Canais](#8-integrações--canais)
9. [Integrações — WhatsApp Cloud API](#9-integrações--whatsapp-cloud-api)
10. [Operação — Atendentes](#10-operação--atendentes)
11. [Operação — Logs](#11-operação--logs)
12. [Segurança — Sessão Única e Auditoria](#12-segurança--sessão-única-e-auditoria)
13. [Plataforma — Empresas (Admin)](#13-plataforma--empresas-admin)
14. [Fluxo Completo de Configuração](#14-fluxo-completo-de-configuração)
15. [API REST — Referência Rápida](#15-api-rest--referência-rápida)
16. [FAQ e Resolução de Problemas](#16-faq-e-resolução-de-problemas)

---

## 1. Visão Geral do Sistema

O **Zynkora** é uma plataforma multilocatária (_multi-tenant_) de atendimento omnichannel com automação via chatbot. Permite que múltiplas empresas (**tenants**) operem de forma isolada e segura no mesmo ambiente, com chatbots configuráveis, integração com WhatsApp Cloud API e um painel administrativo completo.

```
┌─────────────────────────────────────────────────────┐
│                      ZYNKORA                        │
│                                                     │
│  ┌──────────────┐   ┌────────────┐   ┌──────────┐  │
│  │  Empresa A   │   │ Empresa B  │   │ ... N    │  │
│  │  (tenant 1)  │   │ (tenant 2) │   │          │  │
│  └──────┬───────┘   └─────┬──────┘   └────┬─────┘  │
│         │                 │               │         │
│  ┌──────▼─────────────────▼───────────────▼──────┐  │
│  │          Multi-Tenant Core                    │  │
│  │   Conversas · Chatbots · Canais · Usuários    │  │
│  └───────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

### Principais funcionalidades

| Módulo | Descrição |
|--------|-----------|
| **Dashboard** | Indicadores em tempo real: conversas abertas, em espera e encerradas hoje |
| **Conversas** | Gerenciamento do atendimento omnichannel com histórico de mensagens |
| **Chatbots** | Robôs de atendimento com mensagem inicial, horários e canal padrão |
| **Fluxos** | Respostas automáticas por palavra-chave (gatilho) em estilo FAQ |
| **Canais** | WhatsApp, site (widget), API e canais internos |
| **Integrações** | Conexão com WhatsApp Cloud API (Meta) via webhook |
| **Atendentes** | Gestão de equipe com perfis de acesso hierárquicos |
| **Logs** | Auditoria e rastreamento de eventos do sistema |
| **Empresas** | Gestão de tenants (exclusivo do administrador da plataforma) |

---

## 2. Perfis de Acesso

O sistema possui **4 perfis distintos**, com permissões progressivas:

| Perfil | Identificador | Pode fazer |
|--------|---------------|------------|
| **Administrador de Plataforma** | `platform_admin` | Tudo + gestão de todas as empresas |
| **Administrador de Empresa** | `company_admin` | Tudo dentro da própria empresa |
| **Supervisor** | `supervisor` | Ver e gerenciar conversas e relatórios |
| **Atendente** | `agent` | Atender e responder conversas |

> **Importante:** Cada usuário pertence a **uma única empresa**. Os dados de uma empresa nunca são visíveis para outra — isolamento automático por `company_id`.

> **Nota:** O **Administrador de Plataforma** é o único que visualiza dados de **todas** as empresas e pode alterar o perfil (`role`) de outros usuários.

---

## 3. Acessando o Painel

### URL de acesso

```
http://[seu-domínio]/admin
```

Exemplo local (XAMPP):
```
http://localhost/chatbox-saas/public/admin
```

### Login

1. Acesse a URL acima
2. Informe **e-mail** e **senha**
3. Clique em **Entrar**

> **Atenção:** Apenas usuários com `status = active` conseguem acessar o painel. Usuários inativos são bloqueados automaticamente.

### Navegação — Sidebar lateral

Após o login, o menu lateral esquerdo exibe as seções disponíveis para o seu perfil:

```
╔══════════════════╗
║  ⬡  Chatbox SaaS ║  ← Logo + nome da plataforma
╠══════════════════╣
║  ATENDIMENTO     ║
║  › Conversas     ║
╠══════════════════╣
║  AUTOMAÇÃO       ║
║  › Chatbots      ║
║  › Fluxos        ║
╠══════════════════╣
║  INTEGRAÇÕES     ║
║  › Integrações   ║
║  › Canais        ║
╠══════════════════╣
║  OPERAÇÃO        ║
║  › Atendentes    ║
║  › Logs          ║
╠══════════════════╣
║  PLATAFORMA*     ║  ← Somente platform_admin
║  › Empresas      ║
╚══════════════════╝
```

- Clique no **ícone `«`** no topo da sidebar para recolhê-la (modo ícones)
- Em **mobile**, a sidebar abre como overlay ao tocar no ícone do menu

---

## 4. Dashboard

O painel inicial apresenta uma visão geral em tempo real da operação.

### Widgets disponíveis

#### Boas-vindas (WelcomeHero)
Exibe uma saudação personalizada (Bom dia / Boa tarde / Boa noite) com:
- Status de uptime da IA
- Latência média do sistema
- Atalhos rápidos: **Criar Chatbot**, **Novo Fluxo**, **Ver Conversas**, **Relatórios**

#### Indicadores de Conversas

| Card | O que mostra |
|------|-------------|
| **Conversas abertas** | Total de conversas com status `open` |
| **Aguardando** | Conversas com status `waiting` (na fila) |
| **Encerradas hoje** | Conversas finalizadas nas últimas 24h |

#### 📊 Novos Indicadores de Performance (AdvancedMetrics)

| Métrica | O que indica |
|---------|--------------|
| **TMA (Média)** | Tempo Médio de Atendimento entre abertura e fechamento |
| **Taxa de Resolução** | Percentual de conversas encerradas com sucesso |
| **Satisfação (CSAT)** | Média de notas coletadas após o atendimento |

> Os dados são filtrados automaticamente pelo `company_id` do usuário logado.

#### 📈 Gráfico — Volume de Conversas
Linha do tempo dos últimos **7 dias** com contagem de novas conversas por dia.

---

## 5. Atendimento — Conversas

### O que são conversas?

Uma **Conversa** representa uma sessão de comunicação entre um cliente e a empresa, podendo ser atendida por um chatbot ou por um agente humano.

### Campos principais

| Campo | Descrição |
|-------|-----------|
| **ID** | Identificador único |
| **Empresa** | Tenant ao qual pertence |
| **Cliente** | Nome, telefone e e-mail do cliente |
| **Canal** | WhatsApp, site, API ou sistema interno |
| **Status** | `open` · `waiting` · `closed` |
| **Atendente** | Usuário responsável (pode ser nulo) |
| **Iniciada em** | Data/hora de abertura |
| **Encerrada em** | Data/hora de fechamento |

### Status do ciclo de vida

```
   [Cliente envia mensagem]
           │
           ▼
    ┌─────────────┐
    │   waiting   │  ← Na fila de espera
    └──────┬──────┘
           │ Chatbot ou atendente assume
           ▼
    ┌─────────────┐
    │    open     │  ← Em atendimento
    └──────┬──────┘
           │ Atendimento finalizado
           ▼
    ┌─────────────┐
    │   closed    │  ← Encerrada
    └─────────────┘
```

### Ações disponíveis

| Ação | Disponível para | O que faz |
|------|----------------|-----------|
| **Visualizar** | Todos | Abre o histórico completo de mensagens |
| **Editar** | Admin / Supervisor | Altera status e atendente responsável |
| **Assumir** | Atendentes | Atribui a conversa a si mesmo |
| **Encerrar** | Todos | Muda o status para `closed` |
| **IA Sugerir** | Atendentes | Gera sugestão de resposta inteligente baseada no contexto |

### Sugestão de Resposta via IA

O sistema utiliza inteligência artificial para ler o histórico da conversa e sugerir a melhor resposta para o atendente.

1. Dentro da conversa, clique no botão **IA Sugerir** (ícone de faíscas).
2. Uma janela flutuante exibirá a sugestão.
3. Clique em **Copiar Texto** e cole na área de resposta.

---

### Como atender uma conversa

1. Clique em **Atendimento → Conversas**
2. Use o filtro **Status** para ver conversas `open` ou `waiting`
3. Clique em **Assumir** para tornar-se responsável
4. Clique em **Visualizar** para ver o histórico
5. Após finalizar, clique em **Encerrar**

> **Dica:** A tabela de conversas atualiza automaticamente a cada **5 segundos**. Não é necessário recarregar a página.

---

## 6. Automação — Chatbots

### O que é um Chatbot?

O **Chatbot** é o robô de atendimento configurado para cada canal. Ele responde automaticamente às mensagens dos clientes seguindo os **Fluxos** cadastrados.

### Campos de configuração

| Campo | Descrição | Exemplo |
|-------|-----------|---------|
| **Nome** | Identificação interna | `Bot WhatsApp Suporte` |
| **Canal** | Canal vinculado (opcional) | WhatsApp |
| **Telefone WhatsApp** | Número no formato internacional | `+5511999998888` |
| **Mensagem inicial** | Texto enviado ao iniciar conversa | `Olá! Como posso ajudar?` |
| **Horário início / fim** | Funcionamento automático | `08:00` / `18:00` |
| **Canal padrão** | `whatsapp` · `site` · `internal` | `whatsapp` |
| **Status** | `active` ou `inactive` | `active` |

### Como criar um Chatbot

1. Clique em **Automação → Chatbots → + Novo**
2. Preencha o **Nome** e selecione o **Canal**
3. Configure o **Telefone WhatsApp** (se aplicável)
4. Escreva a **Mensagem inicial**
5. Defina o **Horário de funcionamento**
6. Escolha o **Canal padrão**
7. Defina o **Status** como `Ativo`
8. Clique em **Salvar**

> **Nota:** O chatbot somente responde automaticamente quando há **Fluxos ativos** cadastrados.

---

## 7. Automação — Fluxos (Palavra-chave)

### O que são Fluxos?

Os **Fluxos** definem respostas automáticas baseadas em **palavras-chave** detectadas nas mensagens dos clientes.

### Como funciona

```
Mensagem: "preciso de ajuda com boleto"
         │
         ▼ Sistema verifica triggers
         │
Trigger "boleto" encontrado? → SIM
         │
         ▼
Envia resposta automática configurada
```

### Campos de configuração

| Campo | Descrição | Exemplo |
|-------|-----------|---------|
| **Palavra-chave / gatilho** | Trecho em minúsculas | `boleto` |
| **Pergunta / contexto** | Texto auxiliar | `Dúvida sobre segunda via` |
| **Resposta automática** | Texto enviado ao cliente | `Para 2ª via, acesse...` |
| **Próximo fluxo** | Chave do próximo gatilho | `pagamento_ok` |
| **Ação** | Ação após resposta | `close_conversation` |
| **Ativo** | Liga/desliga sem apagar | `Sim` |
| **Ordem** | Prioridade de verificação | `1`, `2`, `3`... |

### Como criar um Fluxo

1. Clique em **Automação → Fluxos → + Novo**
2. Informe a **Palavra-chave** (em minúsculas, sem acento)
3. Escreva a **Resposta automática**
4. Defina a **Ordem** para priorizar fluxos
5. Marque como **Ativo**
6. Clique em **Salvar**

### Encadeamento de Fluxos

```
Fluxo 1: trigger="oi"  → resposta="Bem-vindo! 1-Suporte, 2-Vendas"
                         next_flow_key="escolha_menu"

Fluxo 2: trigger="1"   → resposta="Abrindo ticket de suporte..."
                         action="open_ticket"

Fluxo 3: trigger="2"   → resposta="Conectando com vendas..."
                         action="transfer_sales"
```

---

## 8. Integrações — Canais

### Tipos de canal

| Tipo | Identificador | Descrição |
|------|--------------|-----------|
| **WhatsApp** | `whatsapp` | Via WhatsApp Cloud API (Meta) |
| **Site (widget)** | `site` | Chat flutuante no site |
| **Sistema interno** | `internal` | Uso interno da plataforma |
| **API / parceiro** | `api` | Integração com sistemas externos |

### Como criar um Canal

1. Clique em **Integrações → Canais → + Novo**
2. Selecione o **Tipo** de canal
3. Informe a **Referência externa** e o **Token / segredo**
4. Defina o **Status** como `Ativo`
5. Clique em **Salvar**

---

## 9. Integrações — WhatsApp Cloud API

### Pré-requisitos

- Conta Meta Business verificada
- App configurado em [developers.facebook.com](https://developers.facebook.com)
- WhatsApp Business Account (WABA) ativa
- Número de telefone verificado na Meta

### Campos de configuração

| Campo | Onde encontrar na Meta |
|-------|----------------------|
| **Driver** | Escolha `WhatsApp Cloud API (Meta)` |
| **Token de verificação do webhook** | Você define (qualquer string segura) |
| **Phone Number ID** | Meta → App → WhatsApp → Configuração |
| **Access Token** | Meta → App → Tokens de acesso |
| **WABA ID** | Meta → Gerenciador de Negócios |
| **App Secret** | Meta → App → Painel → App Secret |

### Como configurar

**Passo 1 — Criar a integração no sistema**

1. Vá em **Integrações → Integrações → + Nova**
2. Selecione o **Driver** `WhatsApp Cloud API (Meta)`
3. Defina um **Token de verificação** (ex.: `meu_token_secreto_123`)
4. Preencha as credenciais (Phone Number ID, Access Token, WABA ID, App Secret)
5. Salve — a **URL do Webhook** será gerada automaticamente

**Passo 2 — Configurar o webhook na Meta**

1. Acesse seu App → **WhatsApp → Configuração**
2. Cole a **URL do Webhook** gerada:
   ```
   https://[domínio]/api/v1/integrations/whatsapp/webhook/[slug-empresa]
   ```
3. Cole o **Token de verificação** definido no passo 1
4. Assine os eventos **messages** e **message_status**

**Passo 3 — Confirmar conexão**

1. Edite a integração e mude o **Status** para `Conectado`
2. Envie uma mensagem de teste pelo WhatsApp

> **Atenção:** O **App Secret** é essencial para validar a assinatura dos webhooks da Meta. Sem ele, o sistema não consegue verificar a autenticidade das mensagens.

---

## 10. Operação — Atendentes

### Campos do atendente

| Campo | Descrição |
|-------|-----------|
| **Nome** | Nome completo |
| **E-mail** | Usado para login — deve ser único |
| **Telefone** | Contato interno (opcional) |
| **Senha** | Deixar em branco na edição para não alterar |
| **Perfil** | `Atendente` · `Supervisor` · `Administrador da empresa` |
| **Status** | `Ativo` ou `Inativo` |
| **Última atividade** | Data/hora do último acesso |

### Como adicionar um atendente

1. Clique em **Operação → Atendentes → + Novo**
2. Preencha **Nome**, **E-mail** e **Senha** inicial
3. Selecione o **Perfil** adequado
4. Mantenha **Status** como `Ativo`
5. Salve e compartilhe as credenciais com o colaborador

> **Atenção:** O campo **Perfil** só pode ser alterado pelo **Administrador de Plataforma**.

### Desativando um atendente

1. Edite o atendente
2. Mude o **Status** para `Inativo`
3. Salve — o acesso é revogado imediatamente

---

## 11. Operação — Logs

### O que são os Logs?

Registros automáticos de eventos do sistema (erros, integrações, ações do chatbot). **Somente leitura** — não é possível criar logs manualmente.

### Campos do log

| Campo | Descrição |
|-------|-----------|
| **Empresa** | Tenant ao qual pertence o evento |
| **Tipo** | Categoria (`error`, `webhook`, `chatbot`, etc.) |
| **Descrição** | Texto descritivo do evento |
| **Contexto** | Dados adicionais em chave-valor |
| **Data/hora** | Quando o evento ocorreu |

### Como usar

1. Acesse **Operação → Logs**
2. A tabela atualiza a cada **30 segundos**
3. Clique em **Visualizar** para ver os detalhes completos
4. Monitore logs com tipo `error` para identificar falhas

---

## 12. Segurança — Sessão Única e Auditoria

O sistema possui uma camada de segurança avançada para garantir que as contas não sejam compartilhadas indevidamente e para auditoria de acessos.

### Sessão Única (Single Session)

- **Comportamento:** O sistema permite apenas **uma sessão ativa** por e-mail.
- **Handover Automático:** Se você entrar na sua conta em um computador B, a sessão no computador A será encerrada automaticamente na próxima interação.
- **Mensagem de Alerta:** O usuário desconectado verá um aviso: *"Sua sessão foi encerrada porque você entrou em outro dispositivo."*

### Auditoria de Dispositivos

Administradores podem monitorar todos os acessos em **Segurança → Sessões e Dispositivos**:

| Campo | Descrição |
|-------|-----------|
| **Usuário** | Quem realizou o acesso |
| **IP** | Endereço IP de origem |
| **Dispositivo** | Tipo detectedo: `desktop`, `mobile` ou `tablet` |
| **Navegador** | Chrome, Firefox, Safari, Edge, etc. |
| **Ativa** | Indica se a sessão ainda está em uso |
| **Entrada/Saída** | Registro exato de data e hora |

> **Ação Remota:** Administradores podem clicar em **Encerrar** para desconectar instantaneamente qualquer sessão ativa de um usuário.

---

## 13. Plataforma — Empresas (Admin)

> **Exclusivo do Administrador de Plataforma.** Outros perfis não têm acesso.

### Campos da empresa

#### Identidade

| Campo | Descrição |
|-------|-----------|
| **Nome** | Razão social ou nome fantasia |
| **Slug** | Identificador único em URL (gerado do nome) |
| **CNPJ** | Documento da empresa (opcional) |
| **E-mail / Telefone** | Contato |
| **Logo** | Logotipo da empresa |
| **Cor do chat** | Cor principal do widget |
| **Status** | `Ativa` ou `Suspensa` |

#### Plano e Limites SaaS

| Campo | Descrição | Padrão |
|-------|-----------|--------|
| **Plano** | `Básico` · `Profissional` · `Empresarial` | Básico |
| **Expira em** | Data de vencimento do contrato | — |
| **Limite de Atendentes** | Máximo de usuários | 1 |
| **Limite de Canais** | Máximo de canais | 1 |
| **Limite de Chatbots** | Máximo de chatbots | 1 |

#### Chat e Horários

| Campo | Descrição |
|-------|-----------|
| **Mensagem de boas-vindas** | Exibida ao iniciar o chat |
| **Mensagem offline** | Exibida fora do horário |
| **Horários de funcionamento** | JSON com horários por dia da semana |
| **Resposta automática** | Liga/desliga o chatbot automático |

### Exemplo de horário (JSON)

```json
{
  "monday":    { "start": "08:00", "end": "18:00" },
  "tuesday":   { "start": "08:00", "end": "18:00" },
  "wednesday": { "start": "08:00", "end": "18:00" },
  "thursday":  { "start": "08:00", "end": "18:00" },
  "friday":    { "start": "08:00", "end": "17:00" },
  "saturday":  { "start": "09:00", "end": "12:00" }
}
```

---

## 14. Fluxo Completo de Configuração

Para colocar uma empresa em operação do zero:

```
1. [platform_admin] Criar a Empresa
         │
         ▼
2. [platform_admin] Criar o Admin da Empresa (company_admin)
         │
         ▼
3. [company_admin]  Criar um Canal (ex: WhatsApp)
         │
         ▼
4. [company_admin]  Configurar Integração WhatsApp
         │          └── Registrar webhook na Meta
         │
         ▼
5. [company_admin]  Criar o Chatbot (vinculado ao Canal)
         │
         ▼
6. [company_admin]  Criar os Fluxos (gatilhos e respostas)
         │
         ▼
7. [company_admin]  Cadastrar os Atendentes
         │
         ▼
8. ✅ Sistema operacional — teste pelo WhatsApp!
```

---

## 15. API REST — Referência Rápida

### Base URL

```
https://[seu-domínio]/api/v1/
```

### Endpoints públicos (widget de chat)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/widget/{slug}/config` | Configuração visual do chat |
| `POST` | `/widget/{slug}/conversations` | Inicia ou retoma conversa |
| `GET` | `/widget/{slug}/conversations/{id}/messages` | Busca mensagens |
| `POST` | `/widget/{slug}/conversations/{id}/messages` | Envia mensagem do cliente |

### Endpoints autenticados (agentes)

Requer: `Authorization: Bearer {token}`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `POST` | `/auth/token` | Gera token de acesso |
| `GET` | `/conversations` | Lista conversas do tenant |
| `GET` | `/conversations/{id}` | Detalhes de uma conversa |
| `POST` | `/conversations/{id}/messages` | Envia mensagem como agente |

### Webhook WhatsApp

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/integrations/whatsapp/webhook/{slug}` | Verificação (Meta) |
| `POST` | `/integrations/whatsapp/webhook/{slug}` | Recebe eventos |

### Autenticação — Exemplo

```bash
curl -X POST https://[domínio]/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"email": "agente@empresa.com", "password": "sua_senha"}'
```

Resposta:
```json
{ "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz..." }
```

---

## 16. FAQ e Resolução de Problemas

### Não consigo fazer login

- Verifique se o **Status** da conta está como `Ativo`
- Confirme que o **e-mail** está correto
- Peça ao administrador para redefinir a senha se necessário

### O chatbot não responde automaticamente

1. Verifique se o chatbot está com `Status = Ativo`
2. Confirme que há **Fluxos ativos** com gatilhos cadastrados
3. Certifique-se de que a mensagem do cliente contém a palavra-chave
4. Verifique o **Horário de funcionamento** — fora do horário o bot não responde
5. Consulte os **Logs** por erros recentes

### O webhook do WhatsApp não funciona

1. Confirme que a **URL do webhook** está correta na Meta
2. O **Token de verificação** deve ser idêntico ao cadastrado no sistema
3. Verifique se os eventos **messages** e **message_status** estão subscritos
4. Consulte os **Logs** de webhook por erros de validação

### Não vejo dados de outra empresa

Comportamento esperado — isolamento automático por tenant. Somente o `platform_admin` tem visão global.

### Como incorporar o widget no site?

```html
<script>
  window.ChatboxConfig = {
    slug: 'minha-empresa',
    apiBase: 'https://[domínio]/api/v1'
  };
</script>
<script src="https://[domínio]/widget.js" async></script>
```

### O limite de atendentes/chatbots foi atingido

Apenas o **Administrador de Plataforma** pode aumentar os limites em **Plataforma → Empresas → Editar**.

---

---

*Chatbox SaaS · Laravel 11 + Filament v3 · Multi-tenant · Omnichannel · Abril 2026*

| Usuário | E-mail | Senha | Perfil |
|---------|--------|-------|--------|
| **Admin Plataforma** | `admin@example.com` | `password` | `platform_admin` (Acesso Total) |
| **Atendente Demo** | `agente@demo.local` | `password` | `agent` (Somente Empresa Demo) |


