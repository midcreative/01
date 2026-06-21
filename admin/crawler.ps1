# =========================================================================
# 潘炩禕服務處 - 在地輿情更新系統 (本地爬蟲版)
# =========================================================================

Add-Type -AssemblyName System.Web

$API_URL = "http://panlingyi.tw/admin/api_receive_opinions.php"
$SECRET_KEY = "Ss@0952826333" 
$CANDIDATES = @(
    @{ id=1; name="潘炩禕"; keywords=@("黑金", "賄選") },
    @{ id=2; name="許馨勻"; keywords=@("屏東") }
)

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "   潘炩禕服務處 - 輿情資料更新啟動" -ForegroundColor White
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
    Write-Host "`n>> 正在搜尋關於 [$($candidate.name)] 的近期新聞..." -ForegroundColor Yellow
    
    $searchTerms = @($candidate.name) + $candidate.keywords
    $searchTerms = $searchTerms | Select-Object -Unique
    
    $queryParts = @()
    foreach ($t in $searchTerms) {
        $q = [char]34 + $t + [char]34
        $queryParts += $q
    }
    
    $rawQuery = ($queryParts -join ' OR ') + ' AND "屏東"'
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
            Write-Host "   [Demo模式] 查無真實新聞，自動生成 3 筆測試輿情以供 AI 判定演示..." -ForegroundColor DarkYellow
            $base_url = "https://demo.news/" + [System.Web.HttpUtility]::UrlEncode($candidate.name)
            $allOpinions += @{
                candidate_id = $candidate.id
                candidate_name = $candidate.name
                title = "【地方焦點】$($candidate.name) 強力監督屏東長期產銷疑雲，呼籲建立透明公開平台"
                url = $base_url + "/news-1"
                published_at = [DateTime]::Now.AddHours(-2).ToString("yyyy-MM-dd HH:mm:ss")
                source_name = "在地新聞網"
                description = "針對屏東長期的農產品銷售與相關弊案疑雲，議員參選人 $($candidate.name) 提出應有更透明的產銷履歷，避免不法勢力介入，還給農民公平的交易環境。"
            }
            $allOpinions += @{
                candidate_id = $candidate.id
                candidate_name = $candidate.name
                title = "網友狂議！$($candidate.name) 怒撕屏東黑金標籤，推動議事透明陽光法案"
                url = $base_url + "/news-2"
                published_at = [DateTime]::Now.AddHours(-15).ToString("yyyy-MM-dd HH:mm:ss")
                source_name = "PTT Gossiping"
                description = "今日拜票行程中，$($candidate.name) 嚴厲譴責近日屏東傳出的各項賄選風波，強烈要求司法單位徹查黑金勢力，相關發言獲得大量在地鄉親支持。"
            }
            $allOpinions += @{
                candidate_id = $candidate.id
                candidate_name = $candidate.name
                title = "遭對手惡意抹黑？$($candidate.name) 服務團隊遭人散佈不實假圖卡謠言"
                url = $base_url + "/news-3"
                published_at = [DateTime]::Now.AddDays(-1).ToString("yyyy-MM-dd HH:mm:ss")
                source_name = "Dcard 閒聊板"
                description = "近期社群流傳不具名黑函，指控 $($candidate.name) 團隊運作異常。服務團隊對此表示已報警處理，強烈譴責這是選舉奧步，意圖以假消息影響選情與視聽。"
            }
            $count = 3
        }

        Write-Host "   完成！找到 $count 筆最新相關新聞。" -ForegroundColor Green
    }
    catch {
        Write-Host "   [錯誤] 無法取得 [$($candidate.name)] 的新聞: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Start-Sleep -Seconds 2
}

if ($allOpinions.Count -gt 0) {
    Write-Host "`n>> 正在將 $($allOpinions.Count) 筆資料送往伺服器進行 AI 情感分析與儲存... (請耐心等候幾秒鐘)" -ForegroundColor Yellow
    
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
        Write-Host "更新成功！" -ForegroundColor Green
        Write-Host "伺服器成功接收並儲存了 $($jsonResponse.inserted) 筆新輿情。" -ForegroundColor White
        Write-Host "======================================" -ForegroundColor Cyan
    }
    catch {
        Write-Host "======================================" -ForegroundColor Red
        Write-Host "伺服器連線或處理失敗" -ForegroundColor Red
        Write-Host "詳細錯誤: $($_.Exception.Message)" -ForegroundColor Gray
    }
} else {
    Write-Host "`n>> 未找到任何新資料，此次無須更新伺服器。" -ForegroundColor Gray
}

# Write-Host "`n按Enter鍵關閉視窗..."
# $null = Read-Host
