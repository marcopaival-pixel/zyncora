# Monitorização operacional — Chatbox SaaS

Guia para observabilidade pós go-live: health checks, alertas, Sentry e dashboard admin.

## 1. Comando `system:health-check`

Verificações automáticas:

| Verificação | Crítico? |
|-------------|----------|
| Base de dados | Sim |
| Redis (se em uso) | Sim |
| Storage gravável | Sim |
| Fila de jobs (profundidade) | Sim acima do limite crítico |
| Jobs falhados (`failed_jobs`) | Aviso |
| Erros `SystemErrorLog` (24h) | Aviso |
| Idade do último backup | Aviso |
| Espaço livre em disco | Sim abaixo do mínimo |
| Sentry DSN (production) | Aviso se em falta |

```bash
php artisan system:health-check
php artisan system:health-check --json
php artisan system:health-check --alert   # log + Sentry se degradado/crítico
composer health-check
```

### Limites (.env)

```env
HEALTH_QUEUE_PENDING_WARNING=50
HEALTH_QUEUE_PENDING_CRITICAL=200
HEALTH_FAILED_JOBS_WARNING=1
HEALTH_ERROR_LOG_WARNING_24H=10
HEALTH_BACKUP_MAX_AGE_HOURS=26
HEALTH_DISK_FREE_MIN_MB=500
```

## 2. API para uptime monitors

Endpoint protegido por token (desactivado se `HEALTH_CHECK_TOKEN` vazio):

```env
HEALTH_CHECK_TOKEN=gerar-token-longo-aleatorio
```

```bash
curl -H "Authorization: Bearer SEU_TOKEN" https://seudominio.com/api/v1/health/status
curl "https://seudominio.com/api/v1/health/status?token=SEU_TOKEN"
```

Resposta:

```json
{
  "status": "ok|degraded|critical",
  "checked_at": "2026-05-26T12:00:00+00:00",
  "checks": [ ... ]
}
```

HTTP **503** quando estado `critical`.

## 3. Scheduler

| Hora | Comando |
|------|---------|
| 00:30 | `subscriptions:process-grace-period` |
| 01:00 | `subscriptions:warn-expiring` |
| 02:00 | `subscriptions:expire-overdue` |
| 03:00 | `backup:run` |
| 04:00 | `backup:clean` |
| 04:30 | `backup:monitor` (Spatie) |
| 05:00 | `system:health-check --alert` |

Ver: `php artisan schedule:list`

## 4. Dashboard Filament (admin plataforma)

Widget **SystemHealthWidget** no painel `/admin`:

- BD, fila, jobs falhados, erros 24h, broadcasting, métricas de negócio

Recurso **SystemErrorLog** para detalhe de excepções.

## 5. Sentry

```env
SENTRY_LARAVEL_DSN=https://...@sentry.io/...
```

Teste (se pacote configurado):

```bash
php artisan sentry:test
```

Alertas de health degradado são enviados via `\Sentry\captureMessage` quando `--alert` está activo e DSN definido.

### Slack / webhook genérico

```env
HEALTH_ALERT_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
HEALTH_ALERT_WEBHOOK_URL=https://seu-monitor/webhook
```

```bash
php artisan system:health-check --alert
```

O webhook genérico recebe JSON com `status`, `summary`, `checks` e `checked_at`.

## 6. Logs

- Aplicação: `storage/logs/laravel.log`
- Alertas health: canal `stack` com nível `warning`
- Nunca logar passwords/tokens (já mascarados em `SystemErrorLog`)

## 7. Integração externa (exemplos)

**UptimeRobot / Better Stack:** ping `GET /up` (público) + opcional `GET /api/v1/health/status` com token.

**Cron servidor:** além do scheduler Laravel, garantir `* * * * * php artisan schedule:run`.

---

Ver também: [GO_LIVE_CHECKLIST.md](./GO_LIVE_CHECKLIST.md), [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
