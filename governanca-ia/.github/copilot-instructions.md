# Instruções para o GitHub Copilot (Visual Studio Code)

Este ficheiro faz parte da pasta **`.github/`** do agente de governança: liga o **Copilot** ao núcleo do projeto (**`AGENTS.md`**). O mapa de todas as peças (núcleo, Cursor, Antigravity) está em **`AGENTS.md`**, secção **«Estrutura do agente no repositório»**.

## Obrigatório

1. Siga integralmente **`AGENTS.md`** na raiz deste repositório **antes e durante** qualquer sugestão ou chat de código.
2. Cumpra as secções de **princípios**, **regras obrigatórias**, **anti-alucinação**, **fluxo antes de desenvolver**, **código limpo e seguro (transversal a stacks)**, **checklist de fecho** e, para PHP/Laravel, **«Agentes especializados PHP (integrados no agente da raiz)»** em `AGENTS.md` — aí estão consolidados os protocolos (correção pontual, monitoramento, logs, auditoria, segurança, universal Laravel); **aplique o que corresponder ao pedido** sem exigir ficheiros `.mdc`.
3. Respeite o **stack** descrito em `AGENTS.md` (ex.: PHP/Laravel com ambiente local **XAMPP** — Apache, `public/`, MySQL — conforme a secção **«Ambiente XAMPP (servidor web Apache)»** em `AGENTS.md`, quando o repositório for esse caso).

## Referência rápida

- **Pedidos ao utilizador** (modelos de mensagem): **`MANUAL_PEDIDOS_IA.md`**
- **Instalação e ferramentas** (Cursor, VS Code, Antigravity): **`MANUAL_AGENTE_GOVERNANCA.md`**

**Nota:** regras em **`.cursor/rules/*.mdc`** aplicam-se ao **Cursor**, não ao Copilot; o Copilot deve basear-se em **`AGENTS.md`** e neste ficheiro.

## PHP / Laravel — agentes no `AGENTS.md`

O detalhe operacional está em **`AGENTS.md`**, secção **«Agentes especializados PHP (integrados no agente da raiz)»**. Para **correção pontual**, pode reforçar com o modelo **«Agente PHP»** em **`MANUAL_PEDIDOS_IA.md`**. Comportamento: seguir o protocolo indicado nessa secção (incluindo nível de risco até **Crítico** quando aplicável); **não** refatorar nem modernizar sem pedido; respeitar validação, autorização, segredos e BD só com autorização explícita.
