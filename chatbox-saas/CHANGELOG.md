# Changelog — Chatbox SaaS

Formato orientado por [Keep a Changelog](https://keepachangelog.com/).

## [Unreleased]

_(Sem alterações pendentes.)_

## [1.0.0] — 2026-05-26

Primeiro release go-live do Chatbox SaaS (planos P0–P12).

### Added

- SaaS multi-tenant (Filament 3, widget, API v1, WhatsApp webhooks)
- Assinaturas **Stripe** e **Mercado Pago** + `BillingCheckoutService`
- **Período de graça** (`SUBSCRIPTION_GRACE_PERIOD_DAYS`, `subscriptions:process-grace-period`)
- RBAC unificado, Policies Laravel, OpenAPI + Swagger UI
- Backup Spatie, scheduler completo, expiração e avisos de assinatura
- Go-live: `go-live:verify`, `go-live:smoke`, script XAMPP, checklist
- Monitorização: `system:health-check`, API `/api/v1/health/status`, alertas **Sentry + Slack + webhook**
- Testes: **82** Feature/Unit incluindo CRUD Filament E2E

### Security

- Flags production (registo, demo, billing simulado, API docs)
- CSP, CORS restrito, webhooks assinados

Ver [docs/RELEASE_v1.0.0.md](docs/RELEASE_v1.0.0.md).

## Documentação

- [docs/DEPLOY_PRODUCAO.md](docs/DEPLOY_PRODUCAO.md)
- [docs/GO_LIVE_CHECKLIST.md](docs/GO_LIVE_CHECKLIST.md)
- [docs/MONITORING.md](docs/MONITORING.md)
- [docs/XAMPP_DEPLOY.md](docs/XAMPP_DEPLOY.md)
- [README.md](README.md)

[1.0.0]: https://github.com/org/projeto-chatbot/releases/tag/v1.0.0
