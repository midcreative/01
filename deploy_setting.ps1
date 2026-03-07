$localFile = "c:\Users\User\Desktop\承昕\潘炩禕\01web\admin\src\Controllers\SettingController.php"
$remoteUri = "ftp://85.187.128.60/admin/src/Controllers/SettingController.php"
$req = [System.Net.FtpWebRequest]::Create($remoteUri)
$req.Credentials  = New-Object System.Net.NetworkCredential("xin@demo10.midcreative.com", "Xin@2026!")
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
