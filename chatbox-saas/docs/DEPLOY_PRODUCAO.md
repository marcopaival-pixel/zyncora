# Deploy em produção — Chatbox SaaS

Guia operacional alinhado ao plano de ação de go-live. A aplicação vive em `chatbox-saas/`; o document root do Apache/Nginx deve ser **`public/`**.

## Pré-requisitos do servidor

| Componente | Versão mínima |
|------------|---------------|
| PHP | 8.2+ (extensões Laravel + `redis`) |
| Composer | 2.x |
| Node.js | 18+ (apenas build) |
| MySQL/MariaDB | 8+ |
| Redis | 6+ (**obrigatório** em produção) |
| Supervisor | Para filas |
| Cron | Scheduler Laravel |

## Variáveis críticas (produção)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

CORS_ALLOWED_ORIGINS=https://cliente1.com,https://cliente2.com

# Segurança go-live (defaults seguros em production)
FILAMENT_REGISTRATION_ENABLED=false
DEMO_ROUTES_ENABLED=false
BILLING_SIMULATION_ENABLED=false
PAYMENT_DRIVER=none

SENTRY_LARAVEL_DSN=https://...
GEMINI_API_KEY=...
```

Em **local/staging**, pode activar simulação:

```env
FILAMENT_REGISTRATION_ENABLED=true
DEMO_ROUTES_ENABLED=true
BILLING_SIMULATION_ENABLED=true
```

## Build e deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env   # ou copiar .env do cofre
php artisan key:generate # só na primeira vez
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Permissões

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

## Supervisor — fila Redis

Ficheiro `/etc/supervisor/conf.d/chatbox-worker.conf`:

```ini
[program:chatbox-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /caminho/para/chatbox-saas/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/chatbox-worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread && supervisorctl update && supervisorctl start chatbox-worker:*
```

**WhatsApp e chatbot** dependem deste worker.

## Cron — scheduler

```cron
* * * * * cd /caminho/para/chatbox-saas && php artisan schedule:run >> /dev/null 2>&1
```

Tarefas agendadas: retenção LGPD (diário), período de graça (`subscriptions:process-grace-period` 00:30), aviso de expiração (`subscriptions:warn-expiring` 01:00), expiração (`subscriptions:expire-overdue` 02:00), backup Spatie (`backup:run` 03:00, `backup:clean` 04:00).

Checklist operacional completo: **[GO_LIVE_CHECKLIST.md](./GO_LIVE_CHECKLIST.md)**.

## Redis

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Usado para sessão, cache, filas e locks do webhook WhatsApp.

## Reverb (tempo real — opcional)

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=seudominio.com
REVERB_PORT=443
REVERB_SCHEME=https
```

Processo separado: `php artisan reverb:start`

## Validação pós-deploy

1. `GET /up` → 200
2. Login `/admin` (sem `/admin/register` se `FILAMENT_REGISTRATION_ENABLED=false`)
3. Widget `/chat/{slug}` + API widget
4. Worker a processar jobs (`php artisan queue:monitor` ou logs)
5. Webhook WhatsApp (GET verify + POST com assinatura)
6. Sentry a receber erros de teste
7. `GET /api/v1/openapi.yaml` — documentação API
8. `GET /api/docs` — Swagger UI (se `API_DOCS_ENABLED=true`)
9. Headers CSP sem `'unsafe-eval'` em production

## Backup

O comando `php artisan system:backup` foi substituído por **Spatie Backup**:

```bash
php artisan backup:run
php artisan backup:clean
php artisan backup:list
```

Agendado às **03:00** (backup) e **04:00** (limpeza). Destino local: `storage/app/backups/`.

### Off-site (S3)

```env
BACKUP_DISKS=backups,s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=chatbox-backups
```

Teste manual após configurar credenciais:

```bash
php artisan backup:run --only-db
php artisan backup:list
```

Restore: descarregar o zip do S3 ou local, extrair o dump SQL e importar com `mysql` (validar em staging antes de produção).

## Stripe (assinaturas recorrentes)

1. Defina no `.env`: `PAYMENT_DRIVER=stripe`, `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_CURRENCY=brl`.
2. (Recomendado) Crie **Prices** recorrentes na consola Stripe e preencha `stripe_price_id` na tabela `plans` (ou use `price_data` dinâmico em teste).
3. Webhook URL: `{APP_URL}/api/v1/payments/stripe/webhook`
4. Eventos a activar:
   - `checkout.session.completed`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.paid`
   - `invoice.payment_failed`
5. Após checkout, a empresa recebe `stripe_customer_id`, `stripe_subscription_id` e `subscription_status`; renovações actualizam `expires_at` via webhook.

## Mercado Pago (assinaturas — Brasil)

1. Defina no `.env`: `PAYMENT_DRIVER=mercadopago`, `MERCADOPAGO_ACCESS_TOKEN`, `MERCADOPAGO_WEBHOOK_SECRET`, `MERCADOPAGO_CURRENCY=BRL`.
2. Webhook URL: `{APP_URL}/api/v1/payments/mercadopago/webhook`
3. Tópico mínimo: `subscription_preapproval`
4. Checkout via API `/preapproval` (assinatura recorrente mensal/anual conforme `plans.interval`).
5. Após autorização, a empresa recebe `mercadopago_preapproval_id` e `subscription_status=active`.
6. Active o tópico `payment` para renovar `expires_at` a cada cobrança aprovada.

## Documentação API (Swagger UI)

- Especificação: `GET /api/v1/openapi.yaml`
- Portal interactivo: `GET /api/docs` (requer `API_DOCS_ENABLED=true`; default off em production)
- Protecção opcional em staging: `API_DOCS_BASIC_AUTH_USER` + `API_DOCS_BASIC_AUTH_PASSWORD`
