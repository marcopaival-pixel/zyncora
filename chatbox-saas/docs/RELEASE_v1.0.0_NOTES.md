## Chatbox SaaS 1.0.0 — go-live

Primeiro release production-ready do módulo **chatbox-saas** (planos P0–P12).

**Stack:** Laravel 11, Filament 3, PHP 8.2+, MySQL, Redis (produção)

### Destaques

- Multi-tenant (Filament + widget + WhatsApp)
- Billing **Stripe** e **Mercado Pago**
- Período de **graça**, avisos e expiração de assinaturas
- RBAC, Policies, OpenAPI/Swagger
- Backup Spatie, `go-live:verify`, `go-live:smoke`, `system:health-check`
- Alertas Sentry + Slack + webhook
- **82 testes PHPUnit**

### Setup rápido

```powershell
cd chatbox-saas
copy .env.example .env
.\scripts\go-live-xampp.ps1
.\scripts\post-go-live.ps1
```

### Scheduler (produção)

| Hora | Comando |
|------|---------|
| 00:30 | `subscriptions:process-grace-period` |
| 01:00 | `subscriptions:warn-expiring` |
| 02:00 | `subscriptions:expire-overdue` |
| 03:00 | `backup:run` |
| 04:00 | `backup:clean` |
| 04:30 | `backup:monitor` |
| 05:00 | `system:health-check --alert` |

### Documentação

- `chatbox-saas/docs/GO_LIVE_CHECKLIST.md`
- `chatbox-saas/docs/DEPLOY_PRODUCAO.md`
- `chatbox-saas/docs/MONITORING.md`
- `chatbox-saas/docs/XAMPP_DEPLOY.md`
