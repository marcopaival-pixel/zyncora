# Manual — agente de governança (Cursor, VS Code, Antigravity)

Este manual explica **como instalar e usar** o pacote de governança. O mapa oficial do agente no repositório está em **`AGENTS.md`**, nas secções **«Estrutura do agente no repositório»**, **«Agentes especializados PHP (integrados no agente da raiz)»**, **«Manuais e replicação»** e **«Capacidades de governança»** — leia-as primeiro para saber o papel de cada pasta e o que é política/regra vs automação no CI.

---

## 1. O que é o “agente”

Não é um programa separado: é um **conjunto de instruções** que as ferramentas de IA leem automaticamente ou por contexto. O **núcleo** é sempre **`AGENTS.md`**; Cursor e GitHub acrescentam ficheiros **opcionais** conforme a ferramenta.

| Local | Função (resumo; detalhe em `AGENTS.md`) |
|-------|----------------------------------------|
| **`AGENTS.md`** | **Núcleo:** princípios, regras obrigatórias, anti-alucinação, fluxo de trabalho, stack (Laravel/XAMPP), regras transversais (segurança, qualidade) e **agentes PHP** (protocolos integrados na secção **«Agentes especializados PHP»**). Toda a IA deve alinhar-se aqui; citar só `AGENTS.md` aplica esses protocolos mesmo sem Cursor. |
| **`.cursor/rules/*.mdc`** | **Só Cursor:** governança automática (`governanca-projeto.mdc` com `alwaysApply: true`), modo **Agente PHP** — correção pontual de bugs (`agente-php-correcao-pontual.mdc`, `alwaysApply: true`) + regras por tipo/caminho de ficheiro. Sem Cursor, pode não existir. |
| **`GEMINI.md`** | **Antigravity:** reforço local; complementa `AGENTS.md`. |
| **`.github/`** | **Copilot (VS Code):** em geral `copilot-instructions.md` a mandar seguir `AGENTS.md`. Outros ficheiros (Actions, Dependabot) são **infra do repo**, não o “texto” do agente. |

**Como usar na prática:** no início de um pedido, *“Segue o AGENTS.md e as regras do projeto.”* No **Cursor**, as regras em `.cursor/rules/` reforçam o comportamento quando edita ficheiros que coincidem com os *globs*.

Guia rápido com **modelos de mensagem** (funcionalidade nova / correção): **`MANUAL_PEDIDOS_IA.md`** (também listado em `AGENTS.md` → «Manuais e replicação»).

---

## 2. Funcionalidade nova ou correção — como usar o agente no dia a dia

O agente **não** é um botão: o mínimo é **`AGENTS.md`**. Se usar **Cursor**, as regras em **`.cursor/rules/`** aplicam-se em paralelo (sempre ativas + por ficheiro). Funciona melhor quando **pede de forma alinhada** a essas regras — escopo claro, contexto real no repo.

### 2.1 Antes do primeiro pedido

1. Abra o **workspace na raiz** do repositório onde está `AGENTS.md`.
2. **(Recomendado)** Na primeira mensagem, mencione a governança, por exemplo: *«Segue integralmente o AGENTS.md; não inventes APIs, tabelas nem ficheiros.»*
3. Forneça **contexto verificável**: `@AGENTS.md`, `@routes/web.php`, `@app/Models/...`, trechos de código ou caminhos exatos — evite descrições só em linguagem natural quando existir código-fonte.

### 2.2 Nova funcionalidade — o que pedir (e em que ordem)

Estruture o pedido para a IA **alinhar com escopo e segurança** antes de codificar:

| Parte do pedido | Conteúdo |
|-----------------|----------|
| **Objetivo** | Uma frase: o que o utilizador ou o sistema passa a conseguir fazer. |
| **Comportamento** | Validações, permissões, estados de erro, dados de entrada/saída. |
| **Onde encaixa** | Módulos, ecrãs, rotas ou serviços **já existentes** (referências a ficheiros). |
| **Fora de âmbito** | O que **não** deve ser alterado neste pedido (outros módulos, BD, refactors grandes). |
| **Aceitação** | Como validar (passos manuais, testes a correr, critérios objetivos). |

**Frase de reforço (copiar/adaptar):**

> Antes de escreveres código, resume em bullets o **plano**, os **ficheiros** que vais criar ou alterar e confirma que não vais contra o AGENTS.md (BD, remoção de funcionalidades, dependências novas).

### 2.3 Correção de bug ou código — o que pedir

| Parte | Conteúdo |
|-------|----------|
| **Sintoma** | Erro visível, mensagem, HTTP status, stack trace (se houver). |
| **Reproduzir** | Passos numerados ou URL + cliques. |
| **Esperado vs atual** | Comportamento correto desejado. |
| **Suspeitos** | Ficheiros ou áreas que já identificou (ou peça à IA para **ler** o repo antes de concluir). |
| **Restrições** | Por exemplo: *«correção mínima»*, *«sem alterar esquema de BD»*, *«sem novas dependências»*. |

**Frase de reforço:**

> Não refatores fora do necessário. Se precisares de alterar mais ficheiros do que os indicados, explica porquê antes.

#### 2.3.1 Agente PHP (Cursor — correção pontual em PHP legado)

No **Cursor**, a regra **`.cursor/rules/agente-php-correcao-pontual.mdc`** está com **`alwaysApply: true`**: em pedidos de **correção de bug** (erro, log, trecho, ecrã) a IA deve seguir o protocolo **Agente PHP**: identificar o erro, localizar o ponto exato, propor **apenas** o trecho alterado (antes/depois), explicar a causa em linguagem simples e indicar **risco (baixo / médio / alto)** — sem refactor nem modernização fora do necessário, alinhado a **`AGENTS.md`**.

**Frase de reforço (copiar/adaptar):**

> Atua em modo **Agente PHP**: correção mínima só no bug descrito; mostra antes/depois só do trecho; não mudes nomes nem estrutura sem necessidade; indica o nível de risco.

Para **funcionalidade nova** ou **refactor amplo**, o mesmo ficheiro de regra indica que o rigor “só o trecho” não deve bloquear o escopo legítimo do pedido; mantêm-se `AGENTS.md` e foco nas alterações pedidas.

### 2.4 Durante a conversa

- Se a IA **assumir** nomes de tabelas, rotas ou classes: *«Verifica no repositório ou pergunta-me — não inventes.»*
- Para **migrações, seeds ou dados destrutivos**: *«Só com autorização explícita; propõe mas não assumes execução.»*
- Para **diffs grandes**: peça *«lista de impacto»* (rotas, utilizadores afetados, config).

### 2.5 Depois da resposta da IA

1. Rever alterações (git diff / painel): confere com o **âmbito** pedido.
2. Correr **testes e linters** que o projeto já use (ex.: PHPUnit/Pest, Pint, `npm run build`).
3. Commit/PR com mensagem clara; mudanças sensíveis (auth, pagamentos, BD): revisão humana.

### 2.6 Por ferramenta

| Ferramenta | Uso prático |
|------------|-------------|
| **Cursor** | `@` em `AGENTS.md` e ficheiros relevantes; Composer/Agent com projeto aberto na raiz. |
| **VS Code + Copilot** | Abrir `AGENTS.md`; no chat, pedir cumprimento das regras **antes** do código. |
| **Antigravity** | Raiz do repo; na primeira mensagem de tarefas grandes, citar `AGENTS.md` e `GEMINI.md`. |

---

## 3. Projeto novo — instalação completa

### 3.0 Script de cópia (Windows / PowerShell)

Se este repositório for a **fonte** do pacote, pode copiar tudo para outra pasta com:

```powershell
Set-Location "c:\caminho\para\SeuRepo\governanca-ia\scripts"
.\copiar-governancia.ps1 -Destino "c:\src\NomeDoOutroProjeto"
```

Parâmetros opcionais: `-Origem` (pasta **`governanca-ia`** que contém `AGENTS.md`, se não for a pasta pai do script), `-SemManual` (não copia **`MANUAL_AGENTE_GOVERNANCA.md`** nem **`MANUAL_PEDIDOS_IA.md`**). Detalhes em **`governanca-ia/README.md`**. Para **materializar** o pacote na raiz do projeto onde está `governanca-ia`, use **`governanca-ia/scripts/aplicar-na-raiz.ps1`**.

### 3.1 Copiar ficheiros para a raiz do novo repositório

**Atalho:** copie a pasta **`governanca-ia/`** para dentro do novo repositório e, na **raiz** desse repositório, execute **`.\governanca-ia\scripts\aplicar-na-raiz.ps1`** — materializa os passos 1–6 abaixo.

Ordem manual (alinhada à **«Estrutura do agente no repositório»** em `AGENTS.md`):

1. Copie **`AGENTS.md`** para a **raiz** (obrigatório — núcleo do agente).
2. Copie **`GEMINI.md`** para a raiz (recomendado se usar **Antigravity**).
3. Copie a pasta **`.cursor/rules/`** inteira (todos os `*.mdc`) — **só necessário se usar Cursor**; caminho: `.cursor/rules/`.
4. Crie **`.github/`** e copie **`copilot-instructions.md`** — **só necessário se usar GitHub Copilot no VS Code** (o ficheiro aponta para `AGENTS.md`).
5. *(Recomendado para equipa)* Copie **`MANUAL_AGENTE_GOVERNANCA.md`** e **`MANUAL_PEDIDOS_IA.md`** (ou use o script da secção 3.0, que já os inclui).
6. *(Opcional)* Copie **`docs/HISTORICO_DECISOES_IA.md`** para histórico de decisões da IA (o script da secção 3.0 também o copia, se existir na origem).

**Resumo:** sem Cursor, pode omitir o passo 3; sem Copilot, pode omitir o passo 4; o passo 1 é sempre obrigatório.

### 3.2 Ajustar ao projeto (recomendado)

Abra **`AGENTS.md`** e adapte:

- **Stack e contexto** — se não for PHP/Laravel/XAMPP, altere a secção (por exemplo Node, .NET, só API). O detalhe para projetos servidos pelo **XAMPP** (Apache, pasta `public/`, `.env`, URLs) está na subsecção **«Ambiente XAMPP (servidor web Apache)»** em `AGENTS.md`; remova ou adapte se o ambiente local for outro.
- **Regras obrigatórias** — acrescente ou remova bullets específicos da equipa.
- Remova ou ignore regras Cursor (`.mdc`) que **nunca** se aplicam (por exemplo `webpack.mix.js` se só usar Vite).

Não é obrigatório ter Laravel: as regras `.mdc` para pastas que **não existem** simplesmente não são ativadas.

### 3.3 Confirmar no Git

Faça commit do que aplicar ao vosso fluxo: no mínimo **`AGENTS.md`**; acrescente **`GEMINI.md`**, **`.cursor/rules/`**, **`.github/copilot-instructions.md`** e os **manuais** conforme usarem Cursor, Copilot, Antigravity e documentação interna.

---

## 4. Projeto existente — integração

### 4.1 Mesmos passos da secção 3.1

Copie os ficheiros para a **raiz** do repositório existente.

### 4.2 Se já existir `.cursor/rules/`

- **Opção A — Fundir:** mantenha as suas regras e **adicione** os `.mdc` deste pacote. Se dois ficheiros tiverem o **mesmo nome**, compare conteúdo e una manualmente.
- **Opção B — Prefixo:** renomeie os `.mdc` antigos (ex. `meu-projeto-*.mdc`) para não sobrescrever.

As regras **`governanca-projeto.mdc`** e **`agente-php-correcao-pontual.mdc`** usam `alwaysApply: true` neste pacote; devem **alinhar-se** a `AGENTS.md` e entre si (correção mínima em bugs **sem** contradizer segurança ou escopo). Ao fundir com regras antigas do destino, evite **duas** regras sempre ativas que se contradizem.

### 4.3 Se já existir `.github/copilot-instructions.md`

Una o texto: mantenha a instrução de seguir **`AGENTS.md`** e acrescente requisitos específicos do projeto num só ficheiro.

### 4.4 VS Code — Copilot “Instructions”

Além do ficheiro em `.github/`, no VS Code pode configurar **instruções de workspace**:

- Ficheiro **`.github/copilot-instructions.md`** (já suportado pelo Copilot em muitos setups), ou
- Instruções em **Settings** do Copilot apontando para o repositório.

O importante é que o Copilot **saiba** que `AGENTS.md` é obrigatório (o ficheiro copiado já diz isso).

---

## 5. Cursor — utilização

### 5.1 Ativação

- As regras em **`.cursor/rules/`** são carregadas pelo Cursor quando:
  - **`alwaysApply: true`** (neste pacote: `governanca-projeto.mdc` e `agente-php-correcao-pontual.mdc`) — em conversas do agente/composer relevantes ao workspace; ou
  - o **glob** do ficheiro corresponde ao ficheiro em que está a trabalhar (ex.: `app/**/*.php`).

### 5.2 O que fazer no dia a dia

1. Mantenha **`AGENTS.md`** na raiz e atualizado.
2. Para pedidos grandes, **abra** `AGENTS.md` ou mencione-o na primeira mensagem.
3. Em **Cursor Settings → Rules**, pode ver/ativar regras do projeto; confirme que a governança global não foi desativada por engano.

### 5.3 Projeto só Cursor (sem VS Code / Antigravity)

Basta **`AGENTS.md`** + **`.cursor/rules/`**. `GEMINI.md` e `copilot-instructions.md` são opcionais.

---

## 6. Visual Studio Code — utilização

### 6.1 GitHub Copilot

1. Coloque **`.github/copilot-instructions.md`** no projeto (como na secção **3.1**, passo 4).
2. Garanta que **`AGENTS.md`** está na raiz.
3. Ao pedir sugestões ou chat, pode referenciar: *“Cumpre o AGENTS.md do repositório.”*

### 6.2 Outras extensões de IA

Se usar outra extensão que aceite ficheiro de instruções, **aponte para `AGENTS.md`** ou copie um resumo das regras para o campo de “custom instructions” dessa extensão, se existir.

### 6.3 Sem Copilot

Mesmo sem IA, **`AGENTS.md`** serve de guia humano para revisões e onboarding. As regras `.mdc` **não** são lidas nativamente pelo VS Code — só pelo Cursor.

---

## 7. Google Antigravity — utilização

1. Coloque **`AGENTS.md`** na **raiz** do workspace (padrão suportado pelo Antigravity).
2. Coloque **`GEMINI.md`** na raiz para reforço específico Antigravity (prioridade local quando ambos são aplicados, conforme documentação do produto). Inclui secção sobre **modo Agente PHP** (correção pontual em PHP), porque os `.mdc` do Cursor **não** são carregados no Antigravity.
3. No Antigravity, use **regras de workspace** se a UI permitir apontar para ficheiros adicionais; mantenha `AGENTS.md` como documento principal.

**Nota:** As regras **`.cursor/rules/*.mdc`** não são consumidas pelo Antigravity. Para paridade máxima, o conteúdo crítico deve estar em **`AGENTS.md`**; para o protocolo Agente PHP, use **`GEMINI.md`** e o modelo em **`MANUAL_PEDIDOS_IA.md`**.

---

## 8. Checklist rápido por ferramenta

| Ferramenta | Ficheiros mínimos | Verificação |
|------------|-------------------|-------------|
| **Mapa do agente** | `AGENTS.md` (tabela no início) | Abrir **«Estrutura do agente no repositório»** e **«Manuais e replicação»** para saber o que commitar. |
| **Cursor** | `AGENTS.md` + `.cursor/rules/**` | Pedido de teste: “Lista as regras obrigatórias do AGENTS.md antes de codificar.” Inclui **Agente PHP** (`agente-php-correcao-pontual.mdc`) para correção pontual de bugs. |
| **VS Code + Copilot** | `AGENTS.md` + `.github/copilot-instructions.md` | Abrir `AGENTS.md`; no chat, pedir conformidade explícita. O ficheiro do Copilot inclui secção equivalente ao **Agente PHP** para bugs em PHP. |
| **Antigravity** | `AGENTS.md` + `GEMINI.md` (recomendado) | Workspace na raiz; `.mdc` do Cursor **não** são usados; para correção pontual em PHP, **`GEMINI.md`** e modelo **Agente PHP** em **`MANUAL_PEDIDOS_IA.md`**. |
| **Qualquer uma** | `AGENTS.md` atualizado | Rever secção transversal “código limpo e seguro” antes de releases. |
| **Pedidos ao dia a dia** | `MANUAL_PEDIDOS_IA.md` ou manual secção **2** | Modelos de mensagem antes de funcionalidade nova ou correção. |

---

## 9. Manutenção e evolução

- **Uma fonte:** altere políticas em **`AGENTS.md`** primeiro; depois ajuste `.mdc` só se precisar de reforço por tipo de ficheiro no Cursor.
- **Estrutura do agente:** se passar a usar só Copilot ou só Cursor, pode atualizar a **tabela** no início de `AGENTS.md` (e este manual) para refletir o que a equipa realmente versiona.
- **Versões:** quando mudar PHP/Laravel/stack, atualize a secção “Stack e contexto” em `AGENTS.md`.
- **Segurança:** nunca coloque segredos em `AGENTS.md`, `.mdc` ou `copilot-instructions.md` — apenas princípios e nomes de variáveis.

---

## 10. Resolução de problemas

| Problema | O que verificar |
|----------|-----------------|
| A IA “ignora” as regras | Mencione `AGENTS.md` na mensagem; abra o ficheiro no editor; no Cursor, confirme regras do projeto ativas. |
| Regras `.mdc` não disparam | O ficheiro editado tem de coincidir com o **glob** da regra (ex.: só `routes/*.php` ativa `routes.mdc`). |
| Copilot não parece seguir | Confirme que `.github/copilot-instructions.md` está no repo e que o workspace aberto é a raiz correta. |
| Conflito entre regras | Una texto em `AGENTS.md`; evite duas regras `alwaysApply: true` contraditórias no Cursor. `governanca-projeto.mdc` e `agente-php-correcao-pontual.mdc` foram pensados para **complementar** `AGENTS.md`, sem o substituir. |

---

## 11. Referência de ficheiros neste repositório

```
AGENTS.md
  ├─ Secção «Estrutura do agente no repositório»  → papel de AGENTS / GEMINI / .cursor/rules / .github
  ├─ Secção «Agentes especializados PHP»          → protocolos dos agentes PHP (única fonte sem Cursor)
  ├─ Secção «Manuais e replicação»                → ligações aos manuais e ao script
  └─ Demais secções                                → regras, fluxo, stack Laravel, transversal segurança

GEMINI.md                          ← extensão Antigravity (raiz)
MANUAL_AGENTE_GOVERNANCA.md        ← este manual (instalação + secção 2 = pedidos ao dia a dia)
MANUAL_PEDIDOS_IA.md               ← modelos de mensagem (funcionalidade / correção / Agente PHP)

.cursor/rules/*.mdc                ← só Cursor: governança + Agente PHP (alwaysApply) + regras por ficheiro
.github/copilot-instructions.md    ← Copilot VS Code: obriga a seguir AGENTS.md
.github/*                          ← outros ficheiros (CI, bots): infra do repo, não núcleo do agente

governanca-ia/                     ← pacote único: README + scripts (aplicar-na-raiz, copiar-governancia)
docs/HISTORICO_DECISOES_IA.md      ← opcional: registo manual de decisões da IA
```

Para **replicar** noutro projeto: copie a pasta **`governanca-ia/`** e execute **`governanca-ia/scripts/aplicar-na-raiz.ps1`** na raiz do destino; ou use **`copiar-governancia.ps1`** (secção **3.0**) ou cópia manual conforme **3.1** — mínimo **`AGENTS.md`**; acrescente **`.cursor/rules/`** (Cursor), **`.github/copilot-instructions.md`** (Copilot), **`GEMINI.md`** (Antigravity) e manuais conforme necessário.
