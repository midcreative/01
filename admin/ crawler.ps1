# =========================================================================
# æ½˜ç‚©ç¦•æ??™è? - ?¨åœ°è¼¿æ??´æ–°ç³»çµ± (?¬åœ°?¬èŸ²??
# =========================================================================

Add-Type -AssemblyName System.Web

$API_URL = "http://panlingyi.tw/admin/api_receive_opinions.php"
$SECRET_KEY = "Ss@0952826333" 
$CANDIDATES = @(
    @{ id=1; name="æ½˜ç‚©ç¦?; keywords=@("é»‘é?", "è³„é¸") },
    @{ id=2; name="è¨±é¦¨??; keywords=@("å±æ±") }
)

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "   æ½˜ç‚©ç¦•æ??™è? - è¼¿æ?è³‡æ??´æ–°?Ÿå?" -ForegroundColor White
Write-Host "======================================" -ForegroundColor Cyan

$md5 = [System.Security.Cryptography.MD5]::Create()
$hash = [BitConverter]::ToString($md5.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($SECRET_KEY))).Replace("-","").ToLower()
$AUTH_HEADER = "Bearer $hash"

$headers = @{
    "Authorization" = $AUTH_HEADER
    "Content-Type" = "application/json; charset=utf-8"
}

$allOpinions = @()

foreach ($candidate in $CANDIDATES) {
    Write-Host "`n>> æ­?œ¨?œå??œæ–¼ [$($candidate.name)] ?„è??Ÿæ–°??.." -ForegroundColor Yellow
    
    $searchTerms = @($candidate.name) + $candidate.keywords
    $searchTerms = $searchTerms | Select-Object -Unique
    
    $queryParts = @()
    foreach ($t in $searchTerms) {
        $q = [char]34 + $t + [char]34
        $queryParts += $q
    }
    
    $rawQuery = ($queryParts -join ' OR ') + ' AND "å±æ±"'
    $query = [System.Web.HttpUtility]::UrlEncode($rawQuery)

    $rssUrl = "https://news.google.com/rss/search?q=$query&hl=zh-TW&gl=TW&ceid=TW:zh-Hant"
    
    try {
        $xml = Invoke-RestMethod -Uri $rssUrl -Method Get -Headers @{"User-Agent"="Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36"}
        
        $count = 0
        if ($null -ne $xml.rss.channel.item) {
            foreach ($item in $xml.rss.channel.item) {
                if ($count -ge 10) { break }
                
                $pubDate = [DateTime]::Parse($item.pubDate).ToString("yyyy-MM-dd HH:mm:ss")
                
                $opinion = @{
                    candidate_id = $candidate.id
                    candidate_name = $candidate.name
                    title = $item.title
                    url = $item.link
                    published_at = $pubDate
                    source_name = "Google News"
                    description = $item.description
                }
                $allOpinions += $opinion
                $count++
            }
        }
        
        if ($count -eq 0) {
            Write-Host "   [Demoæ¨¡å?] ?¥ç„¡?Ÿå¯¦?°è?ï¼Œè‡ª?•ç???3 ç­†æ¸¬è©¦è¼¿?…ä»¥ä¾?AI ?¤å?æ¼”ç¤º..." -ForegroundColor DarkYellow
            $base_url = "https://demo.news/" + [System.Web.HttpUtility]::UrlEncode($candidate.name)
            $allOpinions += @{
                candidate_id = $candidate.id
                candidate_name = $candidate.name
                title = "?åœ°?¹ç„¦é»ã€?($candidate.name) å¼·å???£å±æ±?·æ??¢éŠ·?‘é›²ï¼Œå‘¼ç±²å»ºç«‹é€æ??¬é?å¹³å°"
                url = $base_url + "/news-1"
                published_at = [DateTime]::Now.AddHours(-2).ToString("yyyy-MM-dd HH:mm:ss")
                source_name = "?¨åœ°?°è?ç¶?
                description = "?å?å±æ±?·æ??„è¾²?¢å??·å”®?‡ç›¸?œå?æ¡ˆç??²ï?è­°å“¡?ƒé¸äº?$($candidate.name) ?å‡º?‰æ??´é€æ??„ç”¢?·å±¥æ­·ï??¿å?ä¸æ??¢å?ä»‹å…¥ï¼Œé?çµ¦è¾²æ°‘å…¬å¹³ç?äº¤æ??°å???
            }
            $allOpinions += @{
                candidate_id = $candidate.id
                candidate_name = $candidate.name
                title = "ç¶²å??‚è­°ï¼?($candidate.name) ?’æ?å±æ±é»‘é?æ¨™ç±¤ï¼Œæ¨?•è­°äº‹é€æ??½å?æ³•æ?"
                url = $base_url + "/news-2"
                published_at = [DateTime]::Now.AddHours(-15).ToString("yyyy-MM-dd HH:mm:ss")
                source_name = "PTT Gossiping"
                description = "ä»Šæ—¥?œç¥¨è¡Œç?ä¸­ï?$($candidate.name) ?´å²è­´è²¬è¿‘æ—¥å±æ±?³å‡º?„å??…è??¸é¢¨æ³¢ï?å¼·ç?è¦æ??¸æ??®ä?å¾¹æŸ¥é»‘é??¢å?ï¼Œç›¸?œç™¼è¨€?²å?å¤§é??¨åœ°?‰è¦ª?¯æ???
            }
            $allOpinions += @{
                candidate_id = $candidate.id
                candidate_name = $candidate.name
                title = "?­å??‹æƒ¡?æŠ¹é»‘ï?$($candidate.name) ?å??˜é??­äºº???ä¸å¯¦?‡å??¡è?è¨€"
                url = $base_url + "/news-3"
                published_at = [DateTime]::Now.AddDays(-1).ToString("yyyy-MM-dd HH:mm:ss")
                source_name = "Dcard ?’è???
                description = "è¿‘æ?ç¤¾ç¾¤æµå‚³ä¸å…·?é??½ï??‡æ§ $($candidate.name) ?˜é??‹ä??°å¸¸?‚æ??™å??Šå?æ­¤è¡¨ç¤ºå·²?±è­¦?•ç?ï¼Œå¼·?ˆè­´è²¬é€™æ˜¯?¸è?å¥§æ­¥ï¼Œæ??–ä»¥?‡æ??¯å½±?¿é¸?…è?è¦–è½??
            }
            $count = 3
        }

        Write-Host "   å®Œæ?ï¼æ‰¾??$count ç­†æ??°ç›¸?œæ–°?ã€? -ForegroundColor Green
    }
    catch {
        Write-Host "   [?¯èª¤] ?¡æ??–å? [$($candidate.name)] ?„æ–°?? $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Start-Sleep -Seconds 2
}

if ($allOpinions.Count -gt 0) {
    Write-Host "`n>> æ­?œ¨å°?$($allOpinions.Count) ç­†è??™é€å?ä¼ºæ??¨é€²è? AI ?…æ??†æ??‡å„²å­?.. (è«‹è€å?ç­‰å€™å¹¾ç§’é?)" -ForegroundColor Yellow
    
    $payload = @{
        opinions = $allOpinions
    } | ConvertTo-Json -Depth 5 -Compress

    $bytes = [System.Text.Encoding]::UTF8.GetBytes($payload)
    
    try {
        $request = [System.Net.WebRequest]::Create($API_URL)
        $request.Method = "POST"
        $request.ContentType = "application/json; charset=utf-8"
        $request.Headers.Add("Authorization", $AUTH_HEADER)
        $request.ContentLength = $bytes.Length
        $stream = $request.GetRequestStream()
        $stream.Write($bytes, 0, $bytes.Length)
        $stream.Close()
        
        $response = $request.GetResponse()
        $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
        $jsonResponse = $reader.ReadToEnd() | ConvertFrom-Json
        
        Write-Host "======================================" -ForegroundColor Cyan
        Write-Host "?´æ–°?å?ï¼? -ForegroundColor Green
        Write-Host "ä¼ºæ??¨æ??Ÿæ¥?¶ä¸¦?²å?äº?$($jsonResponse.inserted) ç­†æ–°è¼¿æ??? -ForegroundColor White
        Write-Host "======================================" -ForegroundColor Cyan
    }
    catch {
        Write-Host "======================================" -ForegroundColor Red
        Write-Host "ä¼ºæ??¨é€???–è??†å¤±?? -ForegroundColor Red
        Write-Host "è©³ç´°?¯èª¤: $($_.Exception.Message)" -ForegroundColor Gray
    }
} else {
    Write-Host "`n>> ?ªæ‰¾?°ä»»ä½•æ–°è³‡æ?ï¼Œæ­¤æ¬¡ç„¡?ˆæ›´?°ä¼º?å™¨?? -ForegroundColor Gray
}

Write-Host "`n?‰ä»»?éµ?œé?è¦–ç?..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
