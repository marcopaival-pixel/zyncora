#Requires -Version 5.1
<#
.SYNOPSIS
  Verificação pós go-live (após migrate e .env configurado).

.EXAMPLE
  .\scripts\post-go-live.ps1
  .\scripts\post-go-live.ps1 -Strict -Url http://chatbox.local
#>
param(
    [switch] $Strict,
    [string] $Url = '',
    [string] $CompanySlug = '',
    [switch] $SkipTests,
    [switch] $SkipBackup
)

$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

Write-Host "== Chatbox SaaS — pós go-live ==" -ForegroundColor Cyan

function Invoke-Step($Label, [scriptblock] $Action) {
    Write-Host "`n>> $Label" -ForegroundColor Yellow
    & $Action
    if ($LASTEXITCODE -and $LASTEXITCODE -ne 0) {
        throw "Falhou: $Label (exit $LASTEXITCODE)"
    }
}

Invoke-Step "Migrações" { php artisan migrate --force }
Invoke-Step "RBAC sync" { php artisan rbac:sync-users }
Invoke-Step "Storage link" { php artisan storage:link 2>$null; $true }

$verifyArgs = @('artisan', 'go-live:verify')
if ($Strict) { $verifyArgs += '--strict' }
Invoke-Step "go-live:verify" { php @verifyArgs }

$smokeArgs = @('artisan', 'go-live:smoke')
if ($Url) { $smokeArgs += @('--url=' + $Url) }
if ($CompanySlug) { $smokeArgs += @('--company-slug=' + $CompanySlug) }
Invoke-Step "go-live:smoke" { php @smokeArgs }

Invoke-Step "system:health-check" { php artisan system:health-check }

if (-not $SkipBackup) {
    Invoke-Step "backup:run (db)" { php artisan backup:run --only-db }
}

Invoke-Step "schedule:list" { php artisan schedule:list }

if (-not $SkipTests) {
    Invoke-Step "PHPUnit" {
        $env:DB_CONNECTION = 'sqlite'
        $env:DB_DATABASE = ':memory:'
        php artisan test
    }
}

Write-Host "`n== Pós go-live concluído ==" -ForegroundColor Green
Write-Host @"

Próximos passos:
  1. Confirme worker: .\scripts\start-queue-worker.ps1
  2. Confirme scheduler: .\scripts\register-scheduler-task.ps1
  3. Production: APP_ENV=production + go-live:verify --strict
  4. Tag Git: ver docs/GIT_RELEASE.md

"@
