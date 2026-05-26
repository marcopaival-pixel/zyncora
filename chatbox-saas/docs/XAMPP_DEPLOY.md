# Deploy local — XAMPP (Windows)

Guia operacional para correr o Chatbox SaaS em **Apache + MySQL + PHP** (XAMPP). Document root **obrigatório**: pasta **`public/`**.

## 1. Pré-requisitos

| Componente | Notas |
|------------|--------|
| XAMPP | Apache + MySQL activos |
| PHP 8.2+ | No PATH ou `C:\xampp\php\php.exe` |
| Composer | 2.x |
| Node.js 18+ | Apenas para `npm run build` |
| Base MySQL | Ex.: `chatbox_saas` (utilizador `root`, password vazia no XAMPP default) |

## 2. Setup automatizado

```powershell
cd chatbox-saas
.\scripts\go-live-xampp.ps1
```

Opções: `-SkipNpm`, `-SkipTests`, `-Strict`.

## 3. Virtual Host (recomendado)

1. Copie `scripts/xampp-vhost.example.conf` para `C:\xampp\apache\conf\extra\httpd-vhosts.conf` (ou inclua com `Include`).
2. Edite `DocumentRoot` para o caminho real de **`public/`**.
3. Em `hosts`: `127.0.0.1 chatbox.local`
4. Reinicie Apache.
5. No `.env`:

```env
APP_URL=http://chatbox.local
```

Alternativa sem vhost: `http://localhost/chatbox-saas/public` — ajuste `APP_URL` em conformidade.

## 4. Drivers sem Redis (dev local)

Em produção use Redis; no XAMPP local pode usar:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
```

Crie a tabela de jobs se ainda não existir:

```bash
php artisan queue:table
php artisan migrate
```

**Backup Spatie:** no Windows/XAMPP, adicione o `mysqldump` ao PATH ou defina no `.env`:

```env
MYSQL_DUMP_BINARY_PATH=C:/xampp/mysql/bin
```

Teste: `php artisan backup:run --only-db`

## 5. Scheduler (Windows)

O Laravel exige `schedule:run` **a cada minuto**.

**Opção A — Task Scheduler (recomendado):**

```powershell
.\scripts\register-scheduler-task.ps1 -PhpPath "C:\xampp\php\php.exe"
```

**Opção B — terminal dedicado (dev):**

```powershell
while ($true) { php artisan schedule:run; Start-Sleep -Seconds 60 }
```

Ver tarefas agendadas:

```bash
php artisan schedule:list
```

## 6. Worker de filas

WhatsApp outbound e jobs assíncronos exigem worker:

```powershell
.\scripts\start-queue-worker.ps1 -PhpPath "C:\xampp\php\php.exe"
```

Mantenha a janela aberta ou registe como serviço (NSSM) em ambientes persistentes.

## 7. Verificação pós-configuração

```bash
php artisan go-live:verify
php artisan go-live:smoke --url=http://chatbox.local
php artisan go-live:smoke --url=http://chatbox.local --company-slug=sua-empresa
composer test
```

## 8. Checklist rápido

- [ ] MySQL a correr, migrações aplicadas
- [ ] `APP_URL` coincide com o URL no browser
- [ ] `storage/` e `bootstrap/cache/` graváveis
- [ ] `php artisan storage:link`
- [ ] Frontend: `npm ci && npm run build`
- [ ] Scheduler activo (Task Scheduler ou loop)
- [ ] Worker de filas activo se `QUEUE_CONNECTION` ≠ `sync`
- [ ] Smoke tests HTTP OK

---

Ver também: [GO_LIVE_CHECKLIST.md](./GO_LIVE_CHECKLIST.md), [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
