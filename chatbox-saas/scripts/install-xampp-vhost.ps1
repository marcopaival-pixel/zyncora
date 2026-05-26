#Requires -Version 5.1
<#
.SYNOPSIS
  Instala Virtual Host chatbox.local no XAMPP (Apache + hosts).

.EXAMPLE
  .\scripts\install-xampp-vhost.ps1
  .\scripts\install-xampp-vhost.ps1 -ServerName chatbox.local -SkipHosts
#>
param(
    [string] $ServerName = 'chatbox.local',
    [string] $XamppRoot = 'C:\xampp',
    [switch] $SkipHosts
)

$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$PublicPath = Join-Path $Root 'public'
$PublicPathUnix = ($PublicPath -replace '\\', '/')

$vhostsFile = Join-Path $XamppRoot 'apache\conf\extra\httpd-vhosts.conf'
$marker = '# Chatbox SaaS — zyncora'

if (-not (Test-Path $vhostsFile)) {
    throw "Ficheiro não encontrado: $vhostsFile (ajuste -XamppRoot)"
}

$content = Get-Content $vhostsFile -Raw
if ($content -notmatch [regex]::Escape($marker)) {
    $block = @"

$marker
<VirtualHost *:80>
    ServerName $ServerName
    DocumentRoot "$PublicPathUnix"

    <Directory "$PublicPathUnix">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/chatbox-error.log"
    CustomLog "logs/chatbox-access.log" combined
</VirtualHost>

"@
    Add-Content -Path $vhostsFile -Value $block -Encoding UTF8
    Write-Host "VirtualHost adicionado em $vhostsFile" -ForegroundColor Green
} else {
    Write-Host "VirtualHost já presente em $vhostsFile" -ForegroundColor Cyan
}

if (-not $SkipHosts) {
    $hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"
    $hostsLine = "127.0.0.1`t$ServerName"
    $hosts = Get-Content $hostsPath -ErrorAction Stop
    if ($hosts -notcontains $hostsLine -and ($hosts -notmatch [regex]::Escape($ServerName))) {
        try {
            Add-Content -Path $hostsPath -Value "`n$hostsLine" -Encoding ASCII -ErrorAction Stop
            Write-Host "Entrada hosts: $hostsLine" -ForegroundColor Green
        } catch {
            Write-Host "Não foi possível editar hosts (execute PowerShell como Administrador):" -ForegroundColor Yellow
            Write-Host "  Adicione manualmente: $hostsLine" -ForegroundColor Yellow
        }
    } else {
        Write-Host "Entrada hosts já existe para $ServerName" -ForegroundColor Cyan
    }
}

$httpd = Join-Path $XamppRoot 'apache\bin\httpd.exe'
if (Test-Path $httpd) {
    $test = & $httpd -t 2>&1 | Out-String
    Write-Host $test.Trim()
    Write-Host "`nReinicie Apache no XAMPP Control Panel." -ForegroundColor Yellow
}

Write-Host @"

Próximo passo no .env:
  APP_URL=http://$ServerName

Smoke test:
  php artisan go-live:smoke --url=http://$ServerName

"@
