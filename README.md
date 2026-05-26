# Zyncora

Monorepo do produto **Chatbox SaaS** (omnichannel, multi-tenant, Filament + widget + WhatsApp).

| Pasta | Descrição |
|-------|-----------|
| **[chatbox-saas/](chatbox-saas/)** | Aplicação Laravel 11 — **código principal** |
| [governanca-ia/](governanca-ia/) | Pacote de governança IA (`AGENTS.md`, regras Cursor) |
| [docs/](docs/) | Histórico e decisões do projeto |

## Release actual

**v1.0.0** — go-live production-ready (P0–P12). Ver [chatbox-saas/docs/RELEASE_v1.0.0.md](chatbox-saas/docs/RELEASE_v1.0.0.md).

## Início rápido (XAMPP)

```powershell
cd chatbox-saas
copy .env.example .env
# Ajuste DB_* e MYSQL_DUMP_BINARY_PATH=C:/xampp/mysql/bin
.\scripts\go-live-xampp.ps1
.\scripts\post-go-live.ps1
```

Documentação completa: [chatbox-saas/README.md](chatbox-saas/README.md).

## Repositório

https://github.com/marcopaival-pixel/zyncora

## CI

GitHub Actions em [.github/workflows/ci.yml](.github/workflows/ci.yml) — Pint, audit, go-live verify, health check, PHPUnit (`chatbox-saas/`).
