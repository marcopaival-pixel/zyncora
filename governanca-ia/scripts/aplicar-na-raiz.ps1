#Requires -Version 5.1
<#
.SYNOPSIS
    Copia o conteúdo do pacote governanca-ia para a raiz do projeto (AGENTS.md, .cursor/rules, .github, manuais, docs).

.DESCRIPTION
    A pasta governanca-ia é a fonte única copiável. O Cursor e muitas ferramentas esperam .cursor/rules e AGENTS.md na raiz do workspace — este script materializa esses ficheiros a partir do pacote.

.PARAMETER Destino
    Raiz do repositório de destino. Por omissão: pasta pai de governanca-ia (ex.: se governanca-ia está em C:\src\MeuApp\governanca-ia, destino é C:\src\MeuApp).

.EXAMPLE
    cd C:\src\MeuApp
    .\governanca-ia\scripts\aplicar-na-raiz.ps1

.EXAMPLE
    .\aplicar-na-raiz.ps1 -Destino "C:\src\OutroProjeto"
#>
[CmdletBinding()]
param(
    [string] $Destino = "",

    [switch] $SemManual
)

$ErrorActionPreference = "Stop"

$scriptDir = $PSScriptRoot
$pacoteRoot = (Resolve-Path (Join-Path $scriptDir "..")).Path

if (-not $Destino) {
    $Destino = Split-Path $pacoteRoot -Parent
}

$Destino = $Destino.TrimEnd('\', '/')
if (-not (Test-Path -LiteralPath $Destino)) {
    New-Item -ItemType Directory -Path $Destino -Force | Out-Null
}
$Destino = (Resolve-Path -LiteralPath $Destino).Path

$agents = Join-Path $pacoteRoot "AGENTS.md"
if (-not (Test-Path -LiteralPath $agents)) {
    Write-Error "Pacote invalido: nao encontrado AGENTS.md em '$pacoteRoot'"
}

$items = @(
    @{ Src = "AGENTS.md"; Dst = "AGENTS.md" },
    @{ Src = "GEMINI.md"; Dst = "GEMINI.md" },
    @{ Src = "MANUAL_AGENTE_GOVERNANCA.md"; Dst = "MANUAL_AGENTE_GOVERNANCA.md" },
    @{ Src = "MANUAL_PEDIDOS_IA.md"; Dst = "MANUAL_PEDIDOS_IA.md" }
)

foreach ($i in $items) {
    if ($SemManual -and ($i.Src -eq "MANUAL_AGENTE_GOVERNANCA.md" -or $i.Src -eq "MANUAL_PEDIDOS_IA.md")) { continue }
    $from = Join-Path $pacoteRoot $i.Src
    if (Test-Path -LiteralPath $from) {
        Copy-Item -LiteralPath $from -Destination (Join-Path $Destino $i.Dst) -Force
        Write-Host "OK  $($i.Dst)"
    } else {
        Write-Warning "Ficheiro ausente no pacote (ignorado): $($i.Src)"
    }
}

$rulesSrc = Join-Path $pacoteRoot ".cursor\rules"
if (Test-Path -LiteralPath $rulesSrc) {
    $rulesDst = Join-Path $Destino ".cursor\rules"
    New-Item -ItemType Directory -Path $rulesDst -Force | Out-Null
    Copy-Item -Path (Join-Path $rulesSrc "*") -Destination $rulesDst -Recurse -Force
    Write-Host "OK  .cursor\rules\ (*.mdc)"
} else {
    Write-Warning "Pasta ausente no pacote: .cursor\rules"
}

$histSrc = Join-Path $pacoteRoot "docs\HISTORICO_DECISOES_IA.md"
if (Test-Path -LiteralPath $histSrc) {
    $docsDir = Join-Path $Destino "docs"
    New-Item -ItemType Directory -Path $docsDir -Force | Out-Null
    Copy-Item -LiteralPath $histSrc -Destination (Join-Path $docsDir "HISTORICO_DECISOES_IA.md") -Force
    Write-Host "OK  docs\HISTORICO_DECISOES_IA.md"
}

$ghSrc = Join-Path $pacoteRoot ".github\copilot-instructions.md"
if (Test-Path -LiteralPath $ghSrc) {
    $ghDir = Join-Path $Destino ".github"
    New-Item -ItemType Directory -Path $ghDir -Force | Out-Null
    Copy-Item -LiteralPath $ghSrc -Destination (Join-Path $ghDir "copilot-instructions.md") -Force
    Write-Host "OK  .github\copilot-instructions.md"
} else {
    Write-Warning "Ficheiro ausente no pacote: .github\copilot-instructions.md"
}

Write-Host ""
Write-Host "Concluido. Pacote: $pacoteRoot"
Write-Host "Destino (raiz do projeto): $Destino"
Write-Host "Ajuste AGENTS.md ao stack do projeto e faça commit."
