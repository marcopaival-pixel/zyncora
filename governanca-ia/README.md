# Pacote `governanca-ia`

Pasta **única** com governança de IA para projetos PHP/Laravel: `AGENTS.md`, manuais, regras Cursor (`.cursor/rules`), Copilot (`.github`) e documentação opcional.

## O que está aqui

| Conteúdo | Descrição |
|----------|-----------|
| `AGENTS.md` | Núcleo do agente (regras, agentes PHP integrados, stack). |
| `GEMINI.md` | Extensão Antigravity. |
| `MANUAL_*.md` | Instalação e modelos de pedidos. |
| `.cursor/rules/*.mdc` | Regras Cursor (governança + agentes PHP + Laravel). |
| `.github/copilot-instructions.md` | Instruções Copilot. |
| `docs/HISTORICO_DECISOES_IA.md` | Template opcional de decisões. |
| `scripts/aplicar-na-raiz.ps1` | Copia este pacote para a **raiz** do projeto. |
| `scripts/copiar-governancia.ps1` | Copia este pacote para **outra** pasta/projeto. |

## Usar noutro projeto

1. Copie a pasta **`governanca-ia`** inteira para dentro do repositório destino (ex.: `MeuApp/governanca-ia/`).
2. Na **raiz** do projeto destino, execute no PowerShell:

```powershell
Set-Location "C:\caminho\para\MeuApp"
.\governanca-ia\scripts\aplicar-na-raiz.ps1
```

Isto cria/atualiza na raiz: `AGENTS.md`, `GEMINI.md`, manuais, `.cursor/rules/`, `.github/copilot-instructions.md`, `docs/HISTORICO_DECISOES_IA.md`.

**Porquê:** o Cursor lê `.cursor/rules` na **raiz do workspace**, não dentro de `governanca-ia/`. O pacote é a fonte; `aplicar-na-raiz.ps1` mantém a raiz sincronizada.

## Copiar só o pacote para outro disco/projeto

```powershell
Set-Location "c:\caminho\para\MeuApp\governanca-ia\scripts"
.\copiar-governancia.ps1 -Destino "C:\src\OutroRepo"
```

A origem por omissão é a pasta **`governanca-ia`** (pai deste `scripts`).

## Manutenção

- Edite os ficheiros **dentro de `governanca-ia/`**.
- Volte a correr **`aplicar-na-raiz.ps1`** no projeto onde usa o Cursor, para refletir alterações na raiz.

## Resolução rápida

| Situação | O que fazer |
|----------|-------------|
| Cursor **não** carrega regras | Confirme que existe **`.cursor/rules/` na raiz do workspace** (não só dentro de `governanca-ia/`). Corra **`aplicar-na-raiz.ps1`**. |
| Abriu só a subpasta `governanca-ia` como workspace | Abra a **raiz** do repositório da aplicação ou aplique o script na raiz correta. |
| Copiou o pacote mas falta `AGENTS.md` na raiz | Idem: **`aplicar-na-raiz.ps1`**. |

## Nome

**governanca-ia** — governança + uso de IA no desenvolvimento; fácil de reconhecer e de copiar como um bloco só.
