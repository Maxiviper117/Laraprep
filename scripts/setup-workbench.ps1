param(
    [string]$WorkbenchPath = "workbench/laravel13-app"
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot
$workbenchFullPath = Join-Path $repoRoot $WorkbenchPath

if (Test-Path $workbenchFullPath) {
    Write-Host "Workbench already exists at $workbenchFullPath"
    exit 0
}

Write-Host "Creating Laravel 13 workbench at $workbenchFullPath"
composer create-project laravel/laravel $workbenchFullPath "^13.0"

$composerJsonPath = Join-Path $workbenchFullPath "composer.json"

$repoRootForComposer = $repoRoot.Replace("\", "/")

$composerJson = Get-Content $composerJsonPath -Raw | ConvertFrom-Json -AsHashtable

$composerJson["repositories"] = @(
    @{
        type = "path"
        url = $repoRootForComposer
        options = @{
            symlink = $true
        }
    }
)

if (-not $composerJson.ContainsKey("require-dev") -or -not ($composerJson["require-dev"] -is [System.Collections.IDictionary])) {
    $composerJson["require-dev"] = @{}
}

$composerJson["require-dev"]["maxiviper117/laraprep"] = "@dev"

$composerJson |
    ConvertTo-Json -Depth 100 |
    Set-Content $composerJsonPath

composer require maxiviper117/laraprep:@dev --dev -W --working-dir=$workbenchFullPath

if ($LASTEXITCODE -ne 0) {
    throw "Failed to install maxiviper117/laraprep into the workbench."
}

Write-Host ""
Write-Host "Workbench ready."
Write-Host "Dry-run:  cd $workbenchFullPath; vendor/bin/laraprep fortify:backend"
Write-Host "Apply:    cd $workbenchFullPath; vendor/bin/laraprep fortify:backend --apply"
