# Release v1.0.0 — Chatbox SaaS

**Data:** 2026-05-26  
**Stack:** Laravel 11, Filament 3, PHP 8.2+, MySQL, Redis (produção)

## Resumo

Primeiro release production-ready do módulo **chatbox-saas**, cobrindo auditoria 360° e plano de acção P0–P12.

## Funcionalidades principais

| Área | Entrega |
|------|---------|
| Multi-tenant | Isolamento por `company_id`, widget, painel Filament |
| Omnichannel | Conversas, WhatsApp, chatbot, filas |
| Billing | Stripe + Mercado Pago, expiração, graça, avisos e-mail |
| Segurança | RBAC, Policies, CSP, CORS, flags go-live |
| Ops | Backup Spatie, health check, smoke tests, CI |
| Docs | Deploy, XAMPP, checklist, monitorização |

## Comandos operacionais

```bash
php artisan migrate --force
php artisan rbac:sync-users
php artisan go-live:verify --strict
php artisan go-live:smoke --url=https://seudominio.com
php artisan system:health-check --alert
php artisan schedule:list
composer test
```

## Scheduler (produção)

| Hora | Comando |
|------|---------|
| 00:30 | `subscriptions:process-grace-period` |
| 01:00 | `subscriptions:warn-expiring` |
| 02:00 | `subscriptions:expire-overdue` |
| 03:00 | `backup:run` |
| 04:00 | `backup:clean` |
| 04:30 | `backup:monitor` |
| 05:00 | `system:health-check --alert` |

## Variáveis críticas (.env production)

```env
APP_ENV=production
APP_DEBUG=false
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
FILAMENT_REGISTRATION_ENABLED=false
DEMO_ROUTES_ENABLED=false
BILLING_SIMULATION_ENABLED=false
PAYMENT_DRIVER=stripe
SENTRY_LARAVEL_DSN=...
SUBSCRIPTION_GRACE_PERIOD_DAYS=7
HEALTH_CHECK_TOKEN=...
HEALTH_ALERT_SLACK_WEBHOOK_URL=...
```

## Tag Git (manual)

Após revisão e deploy validado:

```bash
cd chatbox-saas
git tag -a v1.0.0 -m "Chatbox SaaS 1.0.0 — go-live"
git push origin v1.0.0
```

## Testes

**82 testes PHPUnit** (última execução no release).

## Referências

- [GO_LIVE_CHECKLIST.md](./GO_LIVE_CHECKLIST.md)
- [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
- [MONITORING.md](./MONITORING.md)
- [XAMPP_DEPLOY.md](./XAMPP_DEPLOY.md)
