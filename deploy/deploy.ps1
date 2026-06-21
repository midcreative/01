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
        -All      : Force upload of all files (ignores fast incremental deployment)
.EXAMPLE
    .\deploy\deploy.ps1           # Normal incremental deploy
    .\deploy\deploy.ps1 -DryRun   # Preview mode
    .\deploy\deploy.ps1 -Force    # Deploy without prompt
    .\deploy\deploy.ps1 -All      # Full upload
#>

param(
    [switch]$DryRun,
    [switch]$Force,
    [switch]$All
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

# Gather files to deploy
if ($All) {
    Write-Step "Scanning all local files (Full Deploy)..."
    $allItems = Get-ChildItem -Path $LOCAL_SOURCE -Recurse -File -Force -ErrorAction SilentlyContinue
    $candidateFiles = $allItems.FullName
} else {
    Write-Step "Detecting modified files via Git (Incremental Deploy)..."
    # Get all added, modified, renmaed, or untracked files
    # Exclude deleted files as FTP upload doesn't delete them anyway in this script yet
    $gitStatus = git -C $LOCAL_SOURCE status -s
    $candidateFiles = @()
    
    foreach ($line in $gitStatus) {
        if ([string]::IsNullOrWhiteSpace($line)) { continue }
        # Status code is first 2 chars
        $status = $line.Substring(0, 2)
        # Skip deleted files
        if ($status -match "D" -and $status -notmatch "R") { continue }
        
        # Extract relative path (handle rename formats like "R  old -> new")
        $filePath = ""
        if ($line -match "->\s+(.*)$") {
            $filePath = $matches[1].Trim()
        } else {
            $filePath = $line.Substring(3).Trim()
        }
        
        # Remove quotes if git output them
        $filePath = $filePath -replace '^"|"$', ''
        
        $fullPath = Join-Path -Path $LOCAL_SOURCE -ChildPath $filePath
        if (Test-Path -Path $fullPath -PathType Leaf) {
            $candidateFiles += $fullPath
        }
    }
    
    if ($candidateFiles.Count -eq 0) {
        Write-Host "  No changes detected. Working tree clean." -ForegroundColor Green
        exit 0
    }
}

$filesToUpload = @()
$dirsToCreate  = @()

foreach ($file in $candidateFiles) {
    # Check each path segment for exclusions
    $relParts = $file.Substring($LOCAL_SOURCE.Length + 1).Split([IO.Path]::DirectorySeparatorChar)
    $excluded = $false
    foreach ($part in $relParts) {
        if (Is-Excluded $part) { $excluded = $true; break }
    }
    if ($excluded) { Write-Skip $file.Substring($LOCAL_SOURCE.Length + 1); continue }

    $filesToUpload += $file
    
    # Track the parent directories we might need to create
    $dirName = [IO.Path]::GetDirectoryName($file)
    if ($dirName -ne $LOCAL_SOURCE -and $dirsToCreate -notcontains $dirName) {
        $dirsToCreate += $dirName
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
