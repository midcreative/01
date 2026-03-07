#Requires -Version 5.1
<#
.SYNOPSIS
    Quick FTP connection test for DentFlow deployment.
.DESCRIPTION
    Connects to the FTP server, lists the remote root directory,
    and confirms credentials are correct.
    Run: .\deploy\test-connection.ps1
#>

. "$PSScriptRoot\ftp-config.ps1"

Write-Host "`nTesting FTP connection to $FTP_HOST ..." -ForegroundColor Cyan

try {
    $uri = "ftp://$FTP_HOST$FTP_REMOTE_ROOT/"
    $req = [System.Net.FtpWebRequest]::Create($uri)
    $req.Credentials = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)
    $req.Method      = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $req.UsePassive  = $true
    $req.UseBinary   = $true

    $resp   = $req.GetResponse()
    $reader = New-Object System.IO.StreamReader($resp.GetResponseStream())
    $list   = $reader.ReadToEnd()
    $reader.Close()
    $resp.Close()

    Write-Host "[OK] Connection successful!" -ForegroundColor Green
    Write-Host "`nRemote directory listing ($FTP_REMOTE_ROOT):" -ForegroundColor Yellow
    $list.Trim().Split("`n") | ForEach-Object { Write-Host "  $_" }

} catch {
    Write-Host "[FAIL] Connection failed: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Please check ftp-config.ps1 and try again." -ForegroundColor Yellow
}
