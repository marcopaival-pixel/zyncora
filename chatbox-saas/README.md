# Chatbox SaaS

Aplicação **Laravel 11** (Filament, API do widget, filas). Ambiente local típico: **PHP**, **MySQL/MariaDB**, **Apache** (XAMPP ou equivalente).

## Requisitos

- PHP **8.2+** com extensões usadas pelo Laravel (incluindo `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`, `zip`)
- Composer 2.x
- Node.js 18+ e npm (assets Vite)
- MySQL ou MariaDB

## Configuração rápida

1. **Dependências PHP**

   ```bash
   composer install
   ```

2. **Ambiente**

   Crie o `.env` a partir do exemplo:

   - Windows (cmd/PowerShell): `copy .env.example .env`
   - Linux / macOS: `cp .env.example .env`

   Depois:

   ```bash
   php artisan key:generate
   ```

   Ajuste no `.env`, entre outros:

   - **`APP_URL`**: URL real em que o site responde (ex.: `http://localhost` ou vhost). Usada em redirects, links e na **base absoluta da API do widget**.
   - **`DB_*`**: base e credenciais MySQL (ex. XAMPP: `127.0.0.1`, porta `3306`).
   - **`CORS_ALLOWED_ORIGINS`**: em produção, liste origens permitidas (separadas por vírgula); evite `*` em produção.

3. **Base de dados**

   ```bash
   php artisan migrate
   ```

4. **Ligação simbólica do storage (uploads)**

   ```bash
   php artisan storage:link
   ```

5. **Frontend (painel / Vite)**

   ```bash
   npm install
   npm run build
   ```

   Em desenvolvimento: `npm run dev` (e o servidor PHP que utilizar).

## Servidor web (Apache / XAMPP)

- O **document root** deve apontar para a pasta **`public/`** do projeto, não para a raiz do repositório.
- Rotas amigáveis exigem **`mod_rewrite`** e `AllowOverride` para `.htaccess` em `public/`.

## Desenvolvimento local

O `composer.json` inclui o script `dev` (servidor Laravel, fila, logs e Vite em paralelo) quando fizer sentido no teu ambiente:

```bash
composer run dev
```

Garante **`QUEUE_CONNECTION`** coerente (ex. `database`) e um worker se usares filas (ex.: `php artisan queue:work`). Necessário para processamento assíncrono de webhooks (ex. WhatsApp).

## Widget de chat

- Rota web de exemplo: `/chat/{slug}` (empresa ativa).
- A API do widget está sob **`/api/v1/widget/...`**; o JavaScript do widget usa **`APP_URL`** via `url()` para pedidos absolutos.

## Integração WhatsApp Cloud (Meta)

Configure uma linha em **`company_integrations`** para o `driver` **`whatsapp_cloud`** (via painel ou seed), associada à empresa correta.

- **URLs de webhook** (substitua `{slug}` pelo slug da empresa e use o mesmo host que em **`APP_URL`**):
  - Verificação (GET): `{APP_URL}/api/v1/integrations/whatsapp/webhook/{slug}`
  - Eventos (POST): `{APP_URL}/api/v1/integrations/whatsapp/webhook/{slug}`
- **`webhook_verify_token`**: token definido na Meta e guardado na integração; tem de coincidir com o da consola Meta na verificação do webhook.
- **`credentials` (JSON encriptado)** — inclua pelo menos **`app_secret`** (App Secret da aplicação Meta). Sem este valor, o pedido **POST** é **rejeitado** (validação `X-Hub-Signature-256`).
- Fila: o processamento assíncrono de respostas do bot usa jobs; mantenha um **worker** ativo (`php artisan queue:work`).
- Versão da Graph API: ver `config/chatbox.php` (`WHATSAPP_GRAPH_VERSION`).

## Documentação de governança

No repositório-mãe, ver **`AGENTS.md`** na raiz do workspace para regras de segurança, alterações e multi-tenant.

### Go-live e deploy

| Recurso | Descrição |
|---------|-----------|
| [docs/GO_LIVE_CHECKLIST.md](docs/GO_LIVE_CHECKLIST.md) | Checklist operacional pós-deploy |
| [docs/DEPLOY_PRODUCAO.md](docs/DEPLOY_PRODUCAO.md) | Redis, Supervisor, cron, pagamentos |
| [CHANGELOG.md](CHANGELOG.md) | Alterações do plano go-live |
| `scripts/go-live-xampp.ps1` | Setup automatizado para XAMPP |
| `scripts/post-go-live.ps1` | Verificação pós-deploy (migrate, smoke, health, backup) |
| [docs/XAMPP_DEPLOY.md](docs/XAMPP_DEPLOY.md) | Virtual host, scheduler, worker Windows |
| [docs/MONITORING.md](docs/MONITORING.md) | Health check, Sentry, alertas, API token |
| [docs/RELEASE_v1.0.0.md](docs/RELEASE_v1.0.0.md) | Notas do release 1.0.0 |

**XAMPP (PowerShell):**

```powershell
cd chatbox-saas
.\scripts\go-live-xampp.ps1
.\scripts\go-live-xampp.ps1 -Strict    # go-live:verify estrito
.\scripts\go-live-xampp.ps1 -SkipNpm   # sem build Vite
```

**Verificação manual:**

```bash
composer go-live-verify
composer go-live-verify-strict
composer go-live-smoke
composer health-check
php artisan system:health-check --json
php artisan go-live:smoke --url=http://chatbox.local --company-slug=minha-empresa
php artisan schedule:list
```

## Backup

Comando manual:

```bash
php artisan backup:run
php artisan backup:list
php artisan backup:clean
```

Agendado via `routes/console.php` (03:00 backup, 04:00 limpeza). Destino: `storage/app/backups/`.

Para S3 em produção: configure disco `s3` em `config/filesystems.php` e `BACKUP_DISK=s3` no `.env`.

Ver **`docs/DEPLOY_PRODUCAO.md`** — Redis, Supervisor, cron, variáveis `FILAMENT_REGISTRATION_ENABLED`, `DEMO_ROUTES_ENABLED`, `BILLING_SIMULATION_ENABLED`.

**Checklist de go-live:** [`docs/GO_LIVE_CHECKLIST.md`](docs/GO_LIVE_CHECKLIST.md)

Verificação automática antes do deploy:

```bash
php artisan go-live:verify
php artisan go-live:verify --strict   # falha também em avisos
```

Assinaturas (scheduler):

```bash
php artisan subscriptions:warn-expiring   # aviso por e-mail (default 7 dias antes)
php artisan subscriptions:process-grace-period  # marca graça + e-mail
php artisan subscriptions:expire-overdue        # expira após graça
```

CI: workflow **`.github/workflows/ci.yml`** (Pint + PHPUnit).

RBAC: após migração ou alteração de perfis, execute `php artisan rbac:sync-users`.
