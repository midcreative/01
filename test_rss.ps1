$resp = Invoke-WebRequest -Uri 'https://news.google.com/rss/search?q=taiwan' -Method Get
Write-Host $resp.StatusCode
