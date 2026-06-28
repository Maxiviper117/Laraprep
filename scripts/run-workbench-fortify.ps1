param(
    [string]$WorkbenchPath = "workbench/laravel13-app",
    [switch]$Apply
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot
$workbenchFullPath = Join-Path $repoRoot $WorkbenchPath

if (-not (Test-Path $workbenchFullPath)) {
    throw "Workbench does not exist at $workbenchFullPath. Run scripts/setup-workbench.ps1 first."
}

$arguments = @("fortify:backend")

if ($Apply) {
    $arguments += "--apply"
}

& (Join-Path $workbenchFullPath "vendor/bin/laraprep") @arguments
