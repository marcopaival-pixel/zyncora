#Requires -Version 5.1
<#
.SYNOPSIS
    Copia o pacote de governança (AGENTS.md, GEMINI.md, manual, .cursor/rules, Copilot) para outro projeto.

.DESCRIPTION
    Origem por omissão: pasta governanca-ia (um nível acima deste script, em scripts/).
    Copia a pasta .cursor\rules completa (inclui governança e agentes PHP, entre outros .mdc).

.EXAMPLE
    .\copiar-governancia.ps1 -Destino "C:\src\MeuApp"
.EXAMPLE
    .\copiar-governancia.ps1 -Destino "C:\src\MeuApp" -Origem "D:\templates\fork-governanca"
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string] $Destino,

    [string] $Origem = "",

    [switch] $SemManual
)

$ErrorActionPreference = "Stop"

$scriptDir = $PSScriptRoot
if (-not $Origem) {
    $Origem = (Resolve-Path (Join-Path $scriptDir "..")).Path
}

$Origem = $Origem.TrimEnd('\', '/')
$Destino = $Destino.TrimEnd('\', '/')

$agents = Join-Path $Origem "AGENTS.md"
if (-not (Test-Path -LiteralPath $agents)) {
    Write-Error "Origem invalida: nao encontrado AGENTS.md em '$Origem'"
}

if (-not (Test-Path -LiteralPath $Destino)) {
    New-Item -ItemType Directory -Path $Destino -Force | Out-Null
}
$Destino = (Resolve-Path -LiteralPath $Destino).Path

$items = @(
    @{ Src = "AGENTS.md"; Dst = "AGENTS.md" },
    @{ Src = "GEMINI.md"; Dst = "GEMINI.md" },
    @{ Src = "MANUAL_AGENTE_GOVERNANCA.md"; Dst = "MANUAL_AGENTE_GOVERNANCA.md" },
    @{ Src = "MANUAL_PEDIDOS_IA.md"; Dst = "MANUAL_PEDIDOS_IA.md" }
)

foreach ($i in $items) {
    if ($SemManual -and ($i.Src -eq "MANUAL_AGENTE_GOVERNANCA.md" -or $i.Src -eq "MANUAL_PEDIDOS_IA.md")) { continue }
    $from = Join-Path $Origem $i.Src
    if (Test-Path -LiteralPath $from) {
        Copy-Item -LiteralPath $from -Destination (Join-Path $Destino $i.Dst) -Force
        Write-Host "OK  $($i.Dst)"
    } else {
        Write-Warning "Ficheiro ausente na origem (ignorado): $($i.Src)"
    }
}

$rulesSrc = Join-Path $Origem ".cursor\rules"
if (Test-Path -LiteralPath $rulesSrc) {
    $rulesDst = Join-Path $Destino ".cursor\rules"
    New-Item -ItemType Directory -Path $rulesDst -Force | Out-Null
    Copy-Item -Path (Join-Path $rulesSrc "*") -Destination $rulesDst -Recurse -Force
    Write-Host "OK  .cursor\rules\ (*.mdc)"
} else {
    Write-Warning "Pasta ausente: .cursor\rules"
}

$histSrc = Join-Path $Origem "docs\HISTORICO_DECISOES_IA.md"
if (Test-Path -LiteralPath $histSrc) {
    $docsDir = Join-Path $Destino "docs"
    New-Item -ItemType Directory -Path $docsDir -Force | Out-Null
    Copy-Item -LiteralPath $histSrc -Destination (Join-Path $docsDir "HISTORICO_DECISOES_IA.md") -Force
    Write-Host "OK  docs\HISTORICO_DECISOES_IA.md"
}

$ghSrc = Join-Path $Origem ".github\copilot-instructions.md"
if (Test-Path -LiteralPath $ghSrc) {
    $ghDir = Join-Path $Destino ".github"
    New-Item -ItemType Directory -Path $ghDir -Force | Out-Null
    Copy-Item -LiteralPath $ghSrc -Destination (Join-Path $ghDir "copilot-instructions.md") -Force
    Write-Host "OK  .github\copilot-instructions.md"
} else {
    Write-Warning "Ficheiro ausente: .github\copilot-instructions.md"
}

Write-Host ""
Write-Host "Concluido. Destino: $Destino"
Write-Host "Ajuste AGENTS.md ao stack do projeto e faça commit."
