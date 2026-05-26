# Manual rápido — pedidos à IA (funcionalidade nova ou correção)



Este ficheiro é um **guia curto** com modelos de mensagem. A explicação completa está em **`MANUAL_AGENTE_GOVERNANCA.md`**, secção **2**.



O **mapa do agente** (o que é núcleo, o que é só Cursor ou só `.github`) está em **`AGENTS.md`**, secções **«Estrutura do agente no repositório»** e **«Manuais e replicação»**.



---



## Ritual de 10 segundos (cada conversa nova)



1. Workspace na **raiz** do repo (onde está `AGENTS.md`).

2. Primeira linha: **«Segue o AGENTS.md.»**

3. **Cursor:** use `@AGENTS.md` e ficheiros relevantes; há regras **sempre ativas** (governança + **Agente PHP** — correção pontual de bugs) e outras quando os *globs* coincidem com o ficheiro em edição.

4. **VS Code + Copilot:** garanta **`.github/copilot-instructions.md`** no projeto; reforce no chat que deve cumprir `AGENTS.md`.

5. **Antigravity:** cite `AGENTS.md` e **`GEMINI.md`** em tarefas maiores.

6. **Pacote `governanca-ia/`:** se acabou de copiar só essa pasta para um projeto novo, corra **`.\governanca-ia\scripts\aplicar-na-raiz.ps1`** na raiz para o Cursor ver `AGENTS.md` e `.cursor/rules/`.



---



## Modelo — funcionalidade nova



Copie e preencha os `[colchetes]`:



```text

Segue o AGENTS.md.



Objetivo: [uma frase — o que o utilizador passa a conseguir]



Comportamento: [validação, permissões, erros esperados, dados]



Onde encaixa: [rotas, módulos, ecrãs já existentes — referências a ficheiros com @ ou caminhos]



Fora de âmbito: [o que NÃO deve ser alterado neste pedido]



Aceitação: [como testar — passos ou testes automáticos]



Antes de codificares: resume o plano em bullets, lista ficheiros a criar/alterar e confirma que não vais contra o AGENTS.md (BD, remoção de funcionalidades, dependências novas).

```



---



## Modelo — correção de bug / código



```text

Segue o AGENTS.md.



Bug: [sintoma — mensagem, URL, o que falha]



Reproduzir: [passos numerados]



Esperado: [comportamento correto]

Atual: [o que acontece]



Contexto: [@ficheiros ou pastas que suspeitas]



Restrições: correção mínima; sem alterar esquema de BD / sem novas dependências [ajuste conforme necessário].



Antes de alterares vários ficheiros, explica a causa provável e propõe a mudança mínima.

```



---



## Modelo — Agente PHP (Cursor, correção pontual)



Use quando quiser **reforçar** o modo correção mínima em PHP (a regra `agente-php-correcao-pontual.mdc` já está ativa no Cursor; esta mensagem alinha o pedido ao protocolo).



```text

Segue o AGENTS.md. Atua em modo Agente PHP.



Bug: [sintoma — mensagem, log, URL]



Reproduzir: [passos ou N/A]



Contexto: [@ficheiros ou trecho]



Pedido: identifica o erro, localiza o ponto exato, corrige só o necessário, mostra antes/depois apenas do trecho, explica a causa de forma simples e indica risco (baixo/médio/alto). Sem refactor, sem modernizar, sem mudar nomes fora do indispensável ao bug.

```



---



## Frases úteis durante a conversa



- *«Não inventes tabelas, rotas ou classes — verifica no repositório ou pergunta-me.»*

- *«Migrações ou dados destrutivos: só com a minha autorização explícita.»*

- *«Lista o impacto desta alteração em rotas, permissões e utilizadores.»*



---



## Depois que a IA responder



- Rever o **diff** (só o pedido?).

- Correr **testes / Pint / build** do projeto.

- **Commit** ou PR; mudanças sensíveis: revisão humana.



---



## Onde está o resto

| Documento | Conteúdo |
|-----------|----------|
| **`AGENTS.md`** | **Núcleo** + **Estrutura do agente** + **Manuais e replicação** + **Capacidades de governança** (validação, log, risco, padrões, limites) + regras |
| **`governanca-ia/`** | **Pacote copiável** (README + scripts `aplicar-na-raiz.ps1` / `copiar-governancia.ps1`); fonte para replicar governança noutro repo |
| **`docs/HISTORICO_DECISOES_IA.md`** | Template opcional para **histórico de decisões** da IA |
| **`MANUAL_AGENTE_GOVERNANCA.md`** | Instalação, secção **2** (pedidos), resolução de problemas |
| **`.cursor/rules/*.mdc`** | Regras **só no Cursor** |
| **`.github/copilot-instructions.md`** | **Copilot** → `AGENTS.md` |
| **`GEMINI.md`** | **Antigravity** |
