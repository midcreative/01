# =========================================================================
# 瞏蝳??? - ?典頛踵??湔蝟餌絞 (?砍?祈??
# =========================================================================

# --- Configuration ---
# 蝬脩?敺 API 蝬脣? (隢耨?寧甇?Ⅱ?迤撘?蝬脣?)
$API_URL = "http://localhost:8000/admin/api_receive_opinions.php"
# 摰撽?? (敹??撩?蝡舐?閮剖? DB_PASSWORD 銝??
$SECRET_KEY = "xin_robot_secret_2026" 
# ?鈭箄身摰??$CANDIDATES = @(
    @{ id=1; name="瞏蝳?; keywords=@("暺?", "鞈") },
    @{ id=2; name="閮梢成??; keywords=@("撅") }
)
# ---------------------

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "   瞏蝳??? - 頛踵?鞈??湔??" -ForegroundColor White
Write-Host "======================================" -ForegroundColor Cyan

# Generate the authorization token
# PHP md5 is just standard MD5 hex string
$md5 = [System.Security.Cryptography.MD5]::Create()
$hash = [BitConverter]::ToString($md5.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($SECRET_KEY))).Replace("-","").ToLower()
$AUTH_HEADER = "Bearer $hash"

$headers = @{
    "Authorization" = $AUTH_HEADER
    "Content-Type" = "application/json"
}

$allOpinions = @()

foreach ($candidate in $CANDIDATES) {
    Write-Host "`n>> 甇???? [$($candidate.name)] ?????.." -ForegroundColor Yellow
    
    # 蝯???摮葡 (??砍????萄?)
    $searchTerms = @($candidate.name) + $candidate.keywords
    $searchTerms = $searchTerms | Select-Object -Unique
    
    $queryParts = @()
    foreach ($t in $searchTerms) {
        $queryParts += "`"$t`""
    }
    
    $rawQuery = ($queryParts -join ' OR ') + ' AND "撅"'
    $query = [System.Web.HttpUtility]::UrlEncode($rawQuery)

    $rssUrl = "https://news.google.com/rss/search?q=$query&hl=zh-TW&gl=TW&ceid=TW:zh-Hant"
    
    try {
        # ?砍??啗?
        $xml = Invoke-RestMethod -Uri $rssUrl -Method Get -Headers @{"User-Agent"="Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36"}
        
        $count = 0
        if ($xml.rss.channel.item -ne $null) {
            foreach ($item in $xml.rss.channel.item) {
                # ?憭???10 蝑?                if ($count -ge 10) { break }
                
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
        Write-Host "   摰?嚗??$count 蝑??啁??? -ForegroundColor Green
    }
    catch {
        Write-Host "   [?航炊] ?⊥??? [$($candidate.name)] ??? $($_.Exception.Message)" -ForegroundColor Red
    }
    
    # ?脫迫隢???餌?鋡急?
    Start-Sleep -Seconds 2
}

if ($allOpinions.Count -gt 0) {
    Write-Host "`n>> 甇?撠???隡箸??券脰? AI ?????摮?.. (隢?蝑嗾蝘?)" -ForegroundColor Yellow
    
    $payload = @{
        opinions = $allOpinions
    } | ConvertTo-Json -Depth 5 -Compress

    try {
        $response = Invoke-RestMethod -Uri $API_URL -Method Post -Headers $headers -Body $payload
        Write-Host "======================================" -ForegroundColor Cyan
        Write-Host "?湔??嚗? -ForegroundColor Green
        Write-Host "隡箸??冽???嗡蒂?脣?鈭?$($response.inserted) 蝑頛踵??? -ForegroundColor White
        Write-Host "======================================" -ForegroundColor Cyan
    }
    catch {
        Write-Host "======================================" -ForegroundColor Red
        Write-Host "隡箸??券??憭望?" -ForegroundColor Red
        if ($_.ErrorDetails) {
            Write-Host "閰喟敦?航炊: $($_.ErrorDetails.Message)" -ForegroundColor Gray
        } else {
            Write-Host "閰喟敦?航炊: $($_.Exception.Message)" -ForegroundColor Gray
        }
    }
} else {
    Write-Host "`n>> ?芣?唬遙雿鞈?嚗迨甈∠??唬撩??? -ForegroundColor Gray
}

Write-Host "`n?遙???閬?..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

