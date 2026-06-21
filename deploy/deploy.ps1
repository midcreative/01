#Requires -Version 5.1
<#
.SYNOPSIS
    FTP Deployment Script for DentFlow Web Project
.DESCRIPTION
    Uploads the local project files to the FTP server.
    Run this script from PowerShell:
        .\deploy\deploy.ps1
    Optional flags:
        -DryRun   : Show what would be uploaded without uploading
        -Force    : Skip confirmation prompt
.EXAMPLE
    .\deploy\deploy.ps1           # Normal deploy with confirmation
    .\deploy\deploy.ps1 -DryRun   # Preview mode
    .\deploy\deploy.ps1 -Force    # Deploy without prompt
#>

param(
    [switch]$DryRun,
    [switch]$Force
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

# ── Load config ────────────────────────────────────────────────────────────────
. "$PSScriptRoot\ftp-config.ps1"

# ── Helpers ────────────────────────────────────────────────────────────────────
function Write-Step([string]$msg) {
    Write-Host "`n[>] $msg" -ForegroundColor Cyan
}

function Write-Ok([string]$msg) {
    Write-Host "    [OK] $msg" -ForegroundColor Green
}

function Write-Skip([string]$msg) {
    Write-Host "    [--] $msg" -ForegroundColor DarkGray
}

function Write-Err([string]$msg) {
    Write-Host "    [!!] $msg" -ForegroundColor Red
}

function Is-Excluded([string]$name) {
    foreach ($pattern in $EXCLUDE_LIST) {
        if ($name -like $pattern) { return $true }
    }
    return $false
}

# Build base FTP URI (no trailing slash)
function Get-FtpUri([string]$localPath) {
    $rel = $localPath.Substring($LOCAL_SOURCE.Length).Replace("\", "/")
    return "ftp://$FTP_HOST$FTP_REMOTE_ROOT$rel"
}

function New-FtpRequest([string]$uri, [string]$method) {
    $req = [System.Net.FtpWebRequest]::Create($uri)
    $req.Credentials  = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)
    $req.Method       = $method
    $req.UseBinary    = $true
    $req.UsePassive   = $true
    $req.KeepAlive    = $false
    $req.Timeout      = 60000
    return $req
}

function Ensure-RemoteDir([string]$dirUri) {
    try {
        $req  = New-FtpRequest $dirUri "MKD"
        $resp = $req.GetResponse()
        $resp.Close()
    } catch {
        # Directory likely already exists — ignore 550 errors
    }
}

function Upload-File([string]$localFile, [string]$remoteUri) {
    $req = New-FtpRequest $remoteUri "STOR"
    $fileBytes = [System.IO.File]::ReadAllBytes($localFile)
    $req.ContentLength = $fileBytes.Length
    $stream = $req.GetRequestStream()
    $stream.Write($fileBytes, 0, $fileBytes.Length)
    $stream.Close()
    $resp = $req.GetResponse()
    $resp.Close()
}

# ── Main ───────────────────────────────────────────────────────────────────────
$banner = @"
╔══════════════════════════════════════════════╗
║       DentFlow FTP Deployment Tool           ║
╚══════════════════════════════════════════════╝
"@
Write-Host $banner -ForegroundColor Yellow

Write-Host "  Host   : $FTP_HOST"
Write-Host "  User   : $FTP_USER"
Write-Host "  Remote : $FTP_REMOTE_ROOT"
Write-Host "  Source : $LOCAL_SOURCE"
if ($DryRun) { Write-Host "  Mode   : DRY RUN (no files will be uploaded)" -ForegroundColor Magenta }

if (-not $Force -and -not $DryRun) {
    $confirm = Read-Host "`nProceed with deployment? (y/N)"
    if ($confirm -ne "y" -and $confirm -ne "Y") {
        Write-Host "Deployment cancelled." -ForegroundColor Yellow
        exit 0
    }
}

# Gather all files to deploy
Write-Step "Scanning local files..."
$allItems = Get-ChildItem -Path $LOCAL_SOURCE -Recurse -Force -ErrorAction SilentlyContinue

$filesToUpload = @()
$dirsToCreate  = @()

foreach ($item in $allItems) {
    # Check each path segment for exclusions
    $relParts = $item.FullName.Substring($LOCAL_SOURCE.Length + 1).Split([IO.Path]::DirectorySeparatorChar)
    $excluded = $false
    foreach ($part in $relParts) {
        if (Is-Excluded $part) { $excluded = $true; break }
    }
    if ($excluded) { Write-Skip $item.FullName.Substring($LOCAL_SOURCE.Length + 1); continue }

    if ($item.PSIsContainer) {
        $dirsToCreate += $item.FullName
    } else {
        $filesToUpload += $item.FullName
    }
}

Write-Host "`n  Directories : $($dirsToCreate.Count)"
Write-Host "  Files       : $($filesToUpload.Count)"

# Create remote directories
Write-Step "Creating remote directories..."
foreach ($dir in $dirsToCreate) {
    $uri = Get-FtpUri $dir
    if ($DryRun) {
        Write-Skip "mkdir $uri"
    } else {
        try {
            Ensure-RemoteDir $uri
            Write-Ok $dir.Substring($LOCAL_SOURCE.Length + 1)
        } catch {
            Write-Err "Failed to create dir: $($_.Exception.Message)"
        }
    }
}

# Upload files
Write-Step "Uploading files..."
$success = 0
$failed  = 0

foreach ($file in $filesToUpload) {
    $uri = Get-FtpUri $file
    $relPath = $file.Substring($LOCAL_SOURCE.Length + 1)
    if ($DryRun) {
        Write-Skip "upload $relPath"
        $success++
    } else {
        try {
            Upload-File $file $uri
            Write-Ok $relPath
            $success++
        } catch {
            Write-Err "$relPath — $($_.Exception.Message)"
            $failed++
        }
    }
}

# Summary
Write-Host "`n══════════════════════════════════════════════" -ForegroundColor Yellow
if ($DryRun) {
    Write-Host "  DRY RUN complete. $($filesToUpload.Count) files would be uploaded." -ForegroundColor Magenta
} else {
    Write-Host "  Deployment complete!" -ForegroundColor Green
    Write-Host "  Uploaded : $success file(s)"
    if ($failed -gt 0) {
        Write-Host "  Failed   : $failed file(s)" -ForegroundColor Red
    }
}
Write-Host "══════════════════════════════════════════════" -ForegroundColor Yellow
