#Requires -Version 5.1
<#
.SYNOPSIS
  Prepara o Chatbox SaaS em ambiente XAMPP (local).

.DESCRIPTION
  Executa dependências, migrações, RBAC, storage:link e go-live:verify.
  Pré-requisitos: MySQL XAMPP activo, PHP no PATH, Composer, Node/npm.

.EXAMPLE
  .\scripts\go-live-xampp.ps1
  .\scripts\go-live-xampp.ps1 -Strict
  .\scripts\go-live-xampp.ps1 -SkipNpm
#>
param(
    [switch] $Strict,
    [switch] $SkipNpm,
    [switch] $SkipTests
)

$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

Write-Host "== Chatbox SaaS — go-live XAMPP ==" -ForegroundColor Cyan
Write-Host "Diretório: $Root"

function Invoke-Step($Label, [scriptblock] $Action) {
    Write-Host "`n>> $Label" -ForegroundColor Yellow
    & $Action
    if ($LASTEXITCODE -and $LASTEXITCODE -ne 0) {
        throw "Falhou: $Label (exit $LASTEXITCODE)"
    }
}

Invoke-Step "PHP" { php -v | Select-Object -First 1 }
Invoke-Step "Composer install" { composer install --no-interaction --prefer-dist }

if (-not (Test-Path '.env')) {
    Invoke-Step "Copiar .env" { Copy-Item '.env.example' '.env' }
}

$envContent = Get-Content '.env' -Raw
if ($envContent -notmatch 'APP_KEY=base64:') {
    Invoke-Step "Gerar APP_KEY" { php artisan key:generate --force }
}

Invoke-Step "Migrações" { php artisan migrate --force }
Invoke-Step "Storage link" { php artisan storage:link }
Invoke-Step "RBAC sync" { php artisan rbac:sync-users --force }

if (-not $SkipNpm) {
    if (Get-Command npm -ErrorAction SilentlyContinue) {
        Invoke-Step "npm ci" { npm ci }
        Invoke-Step "npm run build" { npm run build }
    } else {
        Write-Host "npm não encontrado — a saltar build frontend." -ForegroundColor DarkYellow
    }
}

$verifyArgs = @('artisan', 'go-live:verify')
if ($Strict) { $verifyArgs += '--strict' }
Invoke-Step "go-live:verify" { php @verifyArgs }

if (-not $SkipTests) {
    Invoke-Step "go-live:smoke" { php artisan go-live:smoke }
    Invoke-Step "PHPUnit" {
        $env:DB_CONNECTION = 'sqlite'
        $env:DB_DATABASE = ':memory:'
        php artisan test
    }
}

Write-Host "`n== Concluído ==" -ForegroundColor Green
Write-Host @"

Próximos passos manuais (XAMPP):
  1. Inicie MySQL e Apache no XAMPP Control Panel
  2. Document root do Apache → public/ deste projecto
  3. Ajuste APP_URL no .env ao URL real (ex.: http://localhost/chatbox-saas/public)
  4. Worker de filas: php artisan queue:work
  5. Checklist completo: docs/GO_LIVE_CHECKLIST.md

"@
