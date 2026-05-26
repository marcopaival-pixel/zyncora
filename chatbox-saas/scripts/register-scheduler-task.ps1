#Requires -Version 5.1
<#
.SYNOPSIS
  Regista tarefa no Task Scheduler (Windows) para o Laravel scheduler.

.EXAMPLE
  .\scripts\register-scheduler-task.ps1
  .\scripts\register-scheduler-task.ps1 -TaskName "ChatboxScheduler" -PhpPath "C:\xampp\php\php.exe"
#>
param(
    [string] $TaskName = 'ChatboxLaravelScheduler',
    [string] $PhpPath = 'php'
)

$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$Artisan = Join-Path $Root 'artisan'

if (-not (Test-Path $Artisan)) {
    throw "artisan não encontrado em $Artisan"
}

$Action = New-ScheduledTaskAction -Execute $PhpPath -Argument "`"$Artisan`" schedule:run" -WorkingDirectory $Root
$Trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1) -RepetitionDuration ([TimeSpan]::MaxValue)
$Settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger -Settings $Settings -Force | Out-Null

Write-Host "Tarefa '$TaskName' registada (executa a cada minuto)." -ForegroundColor Green
Write-Host "Verifique: Get-ScheduledTask -TaskName '$TaskName'"
Write-Host "Teste manual: cd $Root; php artisan schedule:list"
