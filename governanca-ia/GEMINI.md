# Antigravity — extensão ao agente de governança

O **`AGENTS.md`** na raiz continua a **fonte única** de princípios, regras obrigatórias e anti-alucinação. Inclui a secção **«Agentes especializados PHP (integrados no agente da raiz)»**, que consolida os protocolos dos agentes (correção, monitoramento, logs, auditoria, segurança, universal Laravel) para **qualquer ferramenta** — em Antigravity, **basta seguir `AGENTS.md`** para aplicar esses comportamentos. A tabela **«Estrutura do agente no repositório»** no início de `AGENTS.md` explica o papel de cada pasta (`GEMINI.md`, `.cursor/rules/`, `.github/`). Este ficheiro **reforça** o comportamento no **Antigravity**; as regras **`.mdc`** do Cursor **não** são carregadas aqui — o critério essencial está em `AGENTS.md`.

Para instalar este pacote em projeto **novo ou existente** e usar em VS Code / Cursor / Antigravity, consulte **`MANUAL_AGENTE_GOVERNANCA.md`**. Para **cada pedido** de funcionalidade nova ou correção, use os modelos em **`MANUAL_PEDIDOS_IA.md`** (ou a secção **2** do manual completo). Em Windows, use o pacote **`governanca-ia/`**: **`governanca-ia/scripts/copiar-governancia.ps1`** para copiar para outro projeto, ou **`aplicar-na-raiz.ps1`** para materializar ficheiros na raiz (ver **`governanca-ia/README.md`**).

## Antes de alterar o projeto

1. Carregar e seguir `AGENTS.md` (não contornar regras de segurança ou de BD).
2. Preferir leitura de ficheiros reais do workspace a suposições sobre rotas, modelos ou esquema.
3. Comandos de terminal ou alterações destrutivas: alinhar com `AGENTS.md` (confirmação do utilizador quando aplicável).

## Transversal (qualquer stack)

A secção **«Código limpo e seguro (transversal a stacks)»** em `AGENTS.md` aplica-se a **toda** alteração (PHP, JavaScript, Docker, YAML de CI, etc.): segredos, dependências, validação, logs, CI e qualidade estática.

## Laravel / PHP neste repositório

Quando `composer.json` indicar Laravel, aplicar as boas práticas já descritas em `AGENTS.md` e **reutilizar** padrões existentes no código (namespaces, pastas `app/`, convenções do projeto).

Para projetos executados em **XAMPP** (Apache + PHP + MySQL/MariaDB no Windows ou equivalente), seguir em `AGENTS.md` a secção **«Ambiente XAMPP (servidor web Apache)»** dentro de **Stack e contexto**, para não assumir apenas `artisan serve` ou Docker e para alinhar document root, URLs e base de dados ao servidor web local.

## Agentes PHP sem Cursor (Antigravity)

Os ficheiros **`.cursor/rules/agente-*.mdc`** **não** são carregados no Antigravity. Use **`AGENTS.md`**, secção **«Agentes especializados PHP (integrados no agente da raiz)»**, como única referência: cite *«Segue o AGENTS.md»* ou anexe `@AGENTS.md` para **ativar** os protocolos (correção pontual, monitoramento, logs, auditoria, segurança, universal Laravel) conforme o pedido. Opcionalmente, modelos de mensagem em **`MANUAL_PEDIDOS_IA.md`** (ex.: Agente PHP). Cumprir sempre segurança, segredos e BD só com autorização.
