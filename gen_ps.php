<?php
$script = <<<'EOT'
# =========================================================================
# 潘炩禕服務處 - 在地輿情更新系統 (本地爬蟲版)
# =========================================================================

\ = "http://panlingyi.tw/admin/api_receive_opinions.php"
\ = "Ss@0952826333" 
\ = @(
    @{ id=1; name="潘炩禕"; keywords=@("黑金", "賄選") },
    @{ id=2; name="許馨勻"; keywords=@("屏東") }
)

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "   潘炩禕服務處 - 輿情資料更新啟動" -ForegroundColor White
Write-Host "======================================" -ForegroundColor Cyan

\ = [System.Security.Cryptography.MD5]::Create()
\ = [BitConverter]::ToString(\.ComputeHash([System.Text.Encoding]::UTF8.GetBytes(\))).Replace("-","").ToLower()
\ = "Bearer \"

\ = @{
    "Authorization" = \
    "Content-Type" = "application/json; charset=utf-8"
}

\ = @()

foreach (\ in \) {
    Write-Host "
>> 正在搜尋關於 [\] 的近期新聞..." -ForegroundColor Yellow
    
    \ = @(\.name) + \.keywords
    \ = \ | Select-Object -Unique
    
    \ = @()
    foreach (\ in \) {
        \ += ""\""
    }
    
    \ = (\ -join ' OR ') + ' AND "屏東"'
    \ = [System.Web.HttpUtility]::UrlEncode(\)

    \ = "https://news.google.com/rss/search?q=\&hl=zh-TW&gl=TW&ceid=TW:zh-Hant"
    
    try {
        \ = Invoke-RestMethod -Uri \ -Method Get -Headers @{"User-Agent"="Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36"}
        
        \ = 0
        if (\.rss.channel.item -ne \) {
            foreach (\ in \.rss.channel.item) {
                if (\ -ge 10) { break }
                
                \ = [DateTime]::Parse(\.pubDate).ToString("yyyy-MM-dd HH:mm:ss")
                
                \ = @{
                    candidate_id = \.id
                    candidate_name = \.name
                    title = \.title
                    url = \.link
                    published_at = \
                    source_name = "Google News"
                    description = \.description
                }
                \ += \
                \++
            }
        }
        Write-Host "   完成！找到 \ 筆最新相關新聞。" -ForegroundColor Green
    }
    catch {
        Write-Host "   [錯誤] 無法取得 [\] 的新聞: \" -ForegroundColor Red
    }
    
    Start-Sleep -Seconds 2
}

if (\.Count -gt 0) {
    Write-Host "
>> 正在將資料送往伺服器進行 AI 情感分析與儲存... (請耐心等候幾秒鐘)" -ForegroundColor Yellow
    
    \ = @{
        opinions = \
    } | ConvertTo-Json -Depth 5 -Compress

    \ = [System.Text.Encoding]::UTF8.GetBytes(\)
    
    try {
        \ = [System.Net.WebRequest]::Create(\)
        \.Method = "POST"
        \.ContentType = "application/json; charset=utf-8"
        \.Headers.Add("Authorization", \)
        \.ContentLength = \.Length
        \ = \.GetRequestStream()
        \.Write(\, 0, \.Length)
        \.Close()
        
        \ = \.GetResponse()
        \ = New-Object System.IO.StreamReader(\.GetResponseStream())
        \ = \.ReadToEnd() | ConvertFrom-Json
        
        Write-Host "======================================" -ForegroundColor Cyan
        Write-Host "更新成功！" -ForegroundColor Green
        Write-Host "伺服器成功接收並儲存了 \ 筆新輿情。" -ForegroundColor White
        Write-Host "======================================" -ForegroundColor Cyan
    }
    catch {
        Write-Host "======================================" -ForegroundColor Red
        Write-Host "伺服器連線或處理失敗" -ForegroundColor Red
        Write-Host "詳細錯誤: \" -ForegroundColor Gray
    }
} else {
    Write-Host "
>> 未找到任何新資料，此次無須更新伺服器。" -ForegroundColor Gray
}

Write-Host "
按任意鍵關閉視窗..."
\ = \System.Management.Automation.Internal.Host.InternalHost.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
EOT;

file_put_contents('admin/爬蟲腳本_手動更新輿情.ps1', "\xef\xbb\xbf" . $script); // UTF-8 BOM
echo "Generated the file perfectly via PHP.";
