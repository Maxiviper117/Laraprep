param(
    [string]$WorkbenchPath = "workbench/laravel13-app"
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot
$workbenchFullPath = Join-Path $repoRoot $WorkbenchPath

if (-not (Test-Path $workbenchFullPath)) {
    Write-Host "Workbench does not exist at $workbenchFullPath"
    exit 0
}

Write-Host "Removing workbench at $workbenchFullPath"
Remove-Item -LiteralPath $workbenchFullPath -Recurse -Force
