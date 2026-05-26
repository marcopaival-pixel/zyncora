## Criar release no GitHub (sem `gh` CLI)

1. Abra: https://github.com/marcopaival-pixel/zyncora/releases/new?tag=v1.0.0
2. **Title:** `Chatbox SaaS 1.0.0`
3. Cole o corpo abaixo (ou use [RELEASE_v1.0.0.md](./RELEASE_v1.0.0.md))
4. Marque **Set as the latest release** → **Publish release**

### Corpo sugerido

```markdown
## Chatbox SaaS 1.0.0 — go-live

Primeiro release production-ready (planos P0–P12).

### Destaques
- Multi-tenant + Filament + widget + WhatsApp
- Billing Stripe e Mercado Pago
- Período de graça, avisos e expiração de assinaturas
- RBAC, Policies, OpenAPI/Swagger
- Backup Spatie, `go-live:verify`, `go-live:smoke`, `system:health-check`
- Alertas Sentry + Slack + webhook
- 82 testes PHPUnit

### Setup
Ver `chatbox-saas/README.md` e `chatbox-saas/scripts/go-live-xampp.ps1`.

### Documentação
- `chatbox-saas/docs/GO_LIVE_CHECKLIST.md`
- `chatbox-saas/docs/DEPLOY_PRODUCAO.md`
- `chatbox-saas/docs/MONITORING.md`
- `chatbox-saas/docs/XAMPP_DEPLOY.md`
```

## Branch default `main`

Após `git push origin main`, em **Settings → General → Default branch**, seleccione **`main`** e apague `master` remoto se desejar:

```powershell
git push origin --delete master
```
