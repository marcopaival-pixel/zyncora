# Checklist de go-live — Chatbox SaaS

Use este documento após deploy em produção. A aplicação vive em `chatbox-saas/`; document root Apache/Nginx = **`public/`**.

## 1. Infraestrutura

| Verificação | Comando / acção |
|-------------|-----------------|
| PHP 8.2+ | `php -v` |
| Extensões Laravel | `php -m` (pdo_mysql, mbstring, openssl, curl, zip, redis) |
| MySQL acessível | `php artisan db:show` |
| Redis activo | `redis-cli ping` → `PONG` |
| Supervisor worker | `supervisorctl status chatbox-worker:*` |
| Cron scheduler | crontab com `* * * * * php artisan schedule:run` |

## 2. Build e cache

```bash
cd chatbox-saas
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan rbac:sync-users
php artisan go-live:verify --strict
php artisan go-live:smoke --url=https://seudominio.com
```

## 3. Variáveis críticas (.env produção)

```env
APP_ENV=production
APP_DEBUG=false
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

FILAMENT_REGISTRATION_ENABLED=false
DEMO_ROUTES_ENABLED=false
BILLING_SIMULATION_ENABLED=false
API_DOCS_ENABLED=false

CORS_ALLOWED_ORIGINS=https://seu-dominio.com
SENTRY_LARAVEL_DSN=...
```

Pagamentos (escolher um):

```env
PAYMENT_DRIVER=stripe
# ou PAYMENT_DRIVER=mercadopago
```

## 4. Validação HTTP

| # | Teste | Esperado |
|---|-------|----------|
| 1 | `GET /up` | 200 |
| 2 | `GET /admin/login` | 200 |
| 3 | `GET /admin/register` | 404 (registo desactivado) |
| 4 | `GET /demo` | 404 (demo desactivado) |
| 5 | `GET /api/v1/openapi.yaml` | 200 |
| 6 | `GET /api/docs` | 404 (docs desactivados) ou 401/200 com basic auth |
| 7 | Widget `GET /chat/{slug}` | 200 |
| 8 | `POST /api/v1/widget/{slug}/conversations` | 200/422 (não 500) |

Exemplo com curl:

```bash
curl -s -o /dev/null -w "%{http_code}" https://seudominio.com/up
curl -s -o /dev/null -w "%{http_code}" https://seudominio.com/admin/login
```

## 5. Filas e jobs

```bash
php artisan queue:monitor redis:default
php artisan schedule:list
```

Tarefas agendadas esperadas:

- `subscriptions:process-grace-period` — 00:30 (período de graça)
- `subscriptions:warn-expiring` — 01:00
- `subscriptions:expire-overdue` — 02:00
- `backup:run` — 03:00
- `backup:clean` — 04:00
- `backup:monitor` — 04:30
- `system:health-check --alert` — 05:00
- LGPD retention — diário

Teste manual de expiração (staging):

```bash
php artisan subscriptions:expire-overdue
```

Backup:

```bash
php artisan backup:run --only-db
php artisan backup:list
```

## 6. Pagamentos

### Stripe

- Webhook: `{APP_URL}/api/v1/payments/stripe/webhook`
- Eventos: `checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`, `invoice.paid`, `invoice.payment_failed`
- Teste: checkout no painel `/admin/billing` → confirmar webhook no dashboard Stripe

### Mercado Pago

- Webhook: `{APP_URL}/api/v1/payments/mercadopago/webhook`
- Tópicos: `subscription_preapproval`, `payment`
- Teste: checkout → confirmar `mercadopago_preapproval_id` na empresa

## 7. WhatsApp

- Worker a processar jobs outbound
- Webhook GET verify + POST com assinatura HMAC
- `CompanyIntegration` configurada por tenant

## 8. Segurança

```bash
composer audit
php artisan test   # CI local antes do tag
```

Headers (resposta web):

- `Content-Security-Policy` sem `'unsafe-eval'` em production
- `X-Frame-Options`, `X-Content-Type-Options` presentes

## 9. Observabilidade

```bash
php artisan system:health-check
php artisan system:health-check --alert
composer health-check
```

- Sentry recebe erro de teste (`php artisan sentry:test` se configurado)
- Alertas automáticos às 05:00 se degradado (`system:health-check --alert`)
- Opcional: `HEALTH_CHECK_TOKEN` + `GET /api/v1/health/status` para uptime externo
- Logs em `storage/logs/` sem passwords/tokens
- `SystemErrorLog` regista excepções no painel (se activo)

Ver [MONITORING.md](./MONITORING.md).

## 10. Rollback rápido

1. Repor release anterior (código + `composer install`)
2. `php artisan migrate:rollback --step=1` **só** se a migração do release for reversível e aprovada
3. Restaurar backup: extrair zip de `storage/app/backups/` ou S3 → import SQL em staging primeiro

## 11. Pós go-live (primeiras 24 h)

- [ ] Monitorizar fila Redis (jobs pendentes)
- [ ] Verificar webhooks Stripe/MP (sem 4xx/5xx sustentados)
- [ ] Confirmar backup no destino (local ou S3)
- [ ] Testar login agente + assumir conversa no Filament
- [ ] Validar widget em domínio real com CORS correcto

---

Ver também: [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
