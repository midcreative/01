$localFile = "c:\Users\User\Desktop\?¿æ?\æ½˜ç‚©ç¦•\01web\check_admin.php"
$remoteUri = "ftp://85.187.128.60/check_admin.php"
$req = [System.Net.FtpWebRequest]::Create($remoteUri)
$req.Credentials  = New-Object System.Net.NetworkCredential("xin@panlingyi.tw", "Xin@2026!")
$req.Method       = "STOR"
$req.UseBinary    = $true
$req.UsePassive   = $true
$fileBytes = [System.IO.File]::ReadAllBytes($localFile)
$req.ContentLength = $fileBytes.Length
$stream = $req.GetRequestStream()
$stream.Write($fileBytes, 0, $fileBytes.Length)
$stream.Close()
$resp = $req.GetResponse()
$resp.Close()
Write-Host "Uploaded "$localFile" to "$remoteUri
