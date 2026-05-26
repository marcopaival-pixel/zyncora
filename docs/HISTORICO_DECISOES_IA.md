# Histórico de decisões da IA (opcional)

Use este ficheiro para registar **decisões relevantes** tomadas com apoio de IA (arquitetura, segurança, alterações amplas), além do que já fica em **commits** e **PRs**.

Apague este bloco de instruções quando começar a usar, ou mova-o para o README interno da equipa.

---

## Entradas (modelo)

Copie o bloco abaixo para cada registo:

```markdown
### YYYY-MM-DD — [título curto]

- **Pedido / contexto:** …
- **Ferramenta:** Cursor | Copilot | Antigravity | outro
- **Decisão ou resultado:** …
- **Ficheiros / PR:** …
- **Riscos / notas:** …
- **Validado por:** … (opcional)
```

---

## Registos

### 2026-05-26 — P12 release v1.0.0 + graça + alertas + CRUD E2E

- **Pedido / contexto:** Concluir todos os passos pendentes (release, graça, Slack, Filament CRUD).
- **Ferramenta:** Cursor
- **Decisão ou resultado:** `SUBSCRIPTION_GRACE_PERIOD_DAYS`, `subscriptions:process-grace-period`, `HealthAlertService` (Slack/webhook), testes Filament CRUD, `docs/RELEASE_v1.0.0.md`, versão 1.0.0.
- **Ficheiros / PR:** `chatbox-saas/` (services, commands, tests, docs, CHANGELOG)
- **Riscos / notas:** Tag Git manual após deploy; webhooks de alerta requerem URLs reais no `.env`.
- **Validado por:** PHPUnit (82 testes)

### 2026-05-26 — P11 monitorização operacional

- **Pedido / contexto:** Fase P11 — health checks, alertas, dashboard.
- **Ferramenta:** Cursor
- **Decisão ou resultado:** `SystemHealthMonitoringService`, `system:health-check`, API token `/api/v1/health/status`, widget admin alargado, scheduler backup:monitor + health alert.
- **Ficheiros / PR:** `app/Services/SystemHealthMonitoringService.php`, commands, middleware, `docs/MONITORING.md`
- **Riscos / notas:** Endpoint health só activo com `HEALTH_CHECK_TOKEN`; alertas Sentry dependem de DSN.
- **Validado por:** PHPUnit (72 testes)

### 2026-05-26 — P10 deploy XAMPP + smoke tests

- **Pedido / contexto:** Continuação pós-P9 (release readiness).
- **Ferramenta:** Cursor
- **Decisão ou resultado:** `go-live:smoke`, guia `XAMPP_DEPLOY.md`, scripts vhost/scheduler/worker, integração no script go-live-xampp.
- **Ficheiros / PR:** `chatbox-saas/app/Services/GoLiveSmokeService.php`, `scripts/*`, `docs/XAMPP_DEPLOY.md`
- **Riscos / notas:** Smoke com `--url` requer Apache activo; worker/scheduler Windows são manuais ou Task Scheduler.
- **Validado por:** PHPUnit (66 testes)

### 2026-05-26 — Plano go-live P0–P9 (Chatbox SaaS)

- **Pedido / contexto:** Auditoria 360° e implementação faseada até release readiness (XAMPP, CI, verify).
- **Ferramenta:** Cursor
- **Decisão ou resultado:** Stripe + Mercado Pago, RBAC/Policies, OpenAPI/Swagger, backup Spatie, scheduler de assinaturas, `go-live:verify`, script `scripts/go-live-xampp.ps1`, CHANGELOG e CI com verify.
- **Ficheiros / PR:** `chatbox-saas/` (config, services, commands, tests, docs, `.github/workflows/ci.yml`)
- **Riscos / notas:** Produção exige `.env` estrito, worker de filas e cron; pagamentos reais dependem de chaves Stripe/MP.
- **Validado por:** 63 testes PHPUnit + `php artisan go-live:verify`

### (exemplo) 2026-04-01 — Estrutura inicial do agente de governança

- **Pedido / contexto:** Definir pacote AGENTS + regras Cursor + manuais.
- **Ferramenta:** Cursor
- **Decisão ou resultado:** Adotado `AGENTS.md` como núcleo; `.cursor/rules` e `.github/copilot-instructions.md` como extensões por ferramenta.
- **Ficheiros / PR:** raiz do repo `ProjetoPiloto`
- **Riscos / notas:** Revisar quando o stack da aplicação Laravel existir no mesmo repo ou noutro.
- **Validado por:** —

---

*(Adicione novas entradas acima desta linha, do mais recente para o mais antigo, ou inverta a convenção da equipa.)*
