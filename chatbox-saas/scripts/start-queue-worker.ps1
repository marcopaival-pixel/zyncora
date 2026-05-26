#Requires -Version 5.1
<#
.SYNOPSIS
  Inicia worker de filas Laravel (XAMPP / Windows).

.DESCRIPTION
  Usa QUEUE_CONNECTION do .env (database, redis, sync).
  Para desenvolvimento local sem Redis, use QUEUE_CONNECTION=database no .env.

.EXAMPLE
  .\scripts\start-queue-worker.ps1
  .\scripts\start-queue-worker.ps1 -PhpPath "C:\xampp\php\php.exe" -Connection database
#>
param(
    [string] $PhpPath = 'php',
    [string] $Connection = '',
    [string] $Queue = 'default',
    [int] $Tries = 3
)

$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

if (-not $Connection) {
    $envFile = Join-Path $Root '.env'
    if (Test-Path $envFile) {
        $match = Select-String -Path $envFile -Pattern '^\s*QUEUE_CONNECTION=(.+)$' | Select-Object -First 1
        if ($match) {
            $Connection = $match.Matches[0].Groups[1].Value.Trim()
        }
    }
}

$args = @('artisan', 'queue:work')

if ($Connection -and $Connection -ne 'sync') {
    $args += $Connection
}

$args += @('--queue=' + $Queue, '--sleep=3', "--tries=$Tries", '--max-time=3600')

Write-Host "A iniciar queue worker (Ctrl+C para parar)..." -ForegroundColor Yellow
Write-Host "Comando: $PhpPath $($args -join ' ')"

& $PhpPath @args
